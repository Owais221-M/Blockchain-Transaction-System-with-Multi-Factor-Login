<?php
require __DIR__ . "/blockchain-transactions/vendor/autoload.php";

use Web3\Web3;
use Web3\Utils;

$web3 = new Web3('http://127.0.0.1:8545');
$eth_address = '0xb627B27b0457Fb50608CFB114663872D3404Adb2';

$eth_balance = null;

$web3->eth->getBalance($eth_address, 'latest', function ($err, $balance) use (&$eth_balance) {
    if ($err !== null) {
        $eth_balance = "Error: " . $err->getMessage();
    } else {
        // 👇 Add this line to inspect what Web3 is returning
        error_log("Raw balance: " . print_r($balance, true));

        $flatten = function ($input) use (&$flatten) {
            $flat = [];
            foreach ((array)$input as $item) {
                if (is_array($item)) {
                    $flat = array_merge($flat, $flatten($item));
                } else {
                    $flat[] = $item;
                }
            }
            return $flat;
        };

        $flat = $flatten($balance);

        foreach ($flat as $item) {
            if ($item instanceof \phpseclib\Math\BigInteger) {
                $eth_balance = Utils::fromWei($item, 'ether')->toString();
                break;
            } elseif (is_string($item) && strpos($item, '0x') === 0) {
                $decimal = gmp_strval(gmp_init($item, 16));
                $eth_balance = Utils::fromWei($decimal, 'ether');
                break;
            }
        }

        if (!$eth_balance) {
            $eth_balance = "Unavailable";
        }
    }
});

header('Content-Type: application/json');
echo json_encode([
    'eth_address' => $eth_address,
    'eth_balance' => $eth_balance
]);
