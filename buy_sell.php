<?php
session_start();
if (!isset($_SESSION['id'])) {
    die("You must be logged in.");
}

require_once __DIR__ . "/vendor/autoload.php";

use Web3\Web3;
use Web3\Utils;
use Web3p\EthereumTx\Transaction;

$conn = new mysqli("localhost", "root", "Ansari_221", "crypto_transaction");
if ($conn->connect_error) die("DB connection failed: " . $conn->connect_error);

$user_id = $_SESSION['id'];
$type = $_POST['type'];
$coin = $_POST['coin'];
$amount = floatval($_POST['amount']);
$price = floatval($_POST['price']);
$total = $amount * $price;

if ($amount <= 0 || $price <= 0) {
    die("Invalid input.");
}

$stmt = $conn->prepare("SELECT balance, btc_balance, eth_balance, eth_address, private_key FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if (!$result->num_rows) die("User not found.");
$user = $result->fetch_assoc();

$currentUSDT = (float)$user['balance'];
$currentBTC  = (float)$user['btc_balance'];
$currentETH  = (float)$user['eth_balance'];
$eth_address = $user['eth_address'];
$private_key = $user['private_key'];
$platformAddress = "0x90F8bf6A479f320ead074411a4B0e7944Ea8c9C1";

// === BUY ===
if ($type === "buy") {
    if ($currentUSDT < $total) die("Not enough USDT.");
    $currentUSDT -= $total;
    if ($coin === "BTC") $currentBTC += $amount;
    elseif ($coin === "ETH") $currentETH += $amount;
    else die("Invalid coin.");

// === SELL ===
} elseif ($type === "sell") {
    if ($coin === "BTC") {
        if ($currentBTC < $amount) die("Not enough BTC.");
        $currentBTC -= $amount;
        $currentUSDT += $total;

    } elseif ($coin === "ETH") {
        if ($currentETH < $amount) die("Not enough ETH.");

        $web3 = new Web3('http://127.0.0.1:8545');
        $eth = $web3->eth;

        // === Get nonce (wait if needed) ===
        $nonce = null;
        $eth->getTransactionCount($eth_address, 'pending', function ($err, $count) use (&$nonce) {
            if ($err !== null) die("Failed to get nonce: " . $err->getMessage());
            $nonce = $count;
        });
        $wait = 0;
        while ($nonce === null && $wait < 10) {
            usleep(200000);
            $wait++;
        }
        if ($nonce === null) die("Nonce fetch failed.");

        // === Convert to hex ===
        $weiBigInt = Utils::toWei((string)$amount, 'ether');
        $weiHex = Utils::toHex($weiBigInt, true);
        $nonceHex = Utils::toHex($nonce, true);

        $txData = [
            'nonce' => $nonceHex,
            'to' => $platformAddress,
            'value' => $weiHex,
            'gas' => '0x5208',
            'gasPrice' => '0x3B9ACA00',
            'chainId' => 1337
        ];

        $transaction = new Transaction($txData);
        $signedTx = '0x' . $transaction->sign($private_key);

        $txHash = null;
        $eth->sendRawTransaction($signedTx, function ($err, $txHashResult) use (&$txHash) {
            if ($err !== null) die("Failed to send signed tx: " . $err->getMessage());
            $txHash = $txHashResult;
        });

        if (!$txHash) die("Transaction not sent.");
        usleep(3000000); // wait for mining

        // === Update ETH balance from blockchain ===
        $rpc = json_encode([
            "jsonrpc" => "2.0",
            "method" => "eth_getBalance",
            "params" => [$eth_address, "latest"],
            "id" => 1
        ]);
        $ctx = stream_context_create([
            "http" => [
                "method" => "POST",
                "header" => "Content-Type: application/json",
                "content" => $rpc
            ]
        ]);
        $res = file_get_contents("http://127.0.0.1:8545", false, $ctx);
        $json = json_decode($res, true);
        $weiHex = $json['result'] ?? '0x0';
        $weiDec = base_convert(substr($weiHex, 2), 16, 10);
        $currentETH = bcdiv($weiDec, bcpow('10', 18), 6);

        $currentUSDT += $total;
    } else {
        die("Invalid coin type.");
    }
} else {
    die("Invalid transaction type.");
}

// === Update balances ===
$stmt = $conn->prepare("UPDATE users SET balance = ?, btc_balance = ?, eth_balance = ? WHERE id = ?");
$stmt->bind_param("dddi", $currentUSDT, $currentBTC, $currentETH, $user_id);
$stmt->execute();
$stmt->close();

// === Log transaction ===
$stmt = $conn->prepare("INSERT INTO transactions (user_id, type, coin, amount, price, total) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issddd", $user_id, $type, $coin, $amount, $price, $total);
$stmt->execute();
$stmt->close();

$conn->close();
echo "✅ $type successful!<br><a href='dashboard.php'>Back to Dashboard</a>";
?>
