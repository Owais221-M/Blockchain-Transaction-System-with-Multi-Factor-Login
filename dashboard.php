<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require_once "config.php"; // DB config
require __DIR__ . "/blockchain-transactions/vendor/autoload.php"; // Web3

use Web3\Web3;
use Web3\Utils;

$user_id = $_SESSION['id'];
$sql = "SELECT username, eth_address, balance, btc_balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$username     = $row['username'] ?? 'User';
$eth_address  = $row['eth_address'] ?? null;
$usdt_balance = number_format((float)($row['balance'] ?? 0), 2);
$btc_balance  = number_format((float)($row['btc_balance'] ?? 0), 6);

$eth_balance = "N/A";

if ($eth_address && strlen($eth_address) === 42 && strpos($eth_address, '0x') === 0) {
    try {
        $web3 = new Web3('http://127.0.0.1:8545');
        $balanceReady = false;

        $web3->eth->getBalance($eth_address, 'latest', function ($err, $balance) use (&$eth_balance, &$balanceReady) {
            try {
                $value = null;

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
                        $value = Utils::fromWei($item, 'ether')->toString();
                        break;
                    } elseif (is_string($item) && strpos($item, '0x') === 0) {
                        $decimal = hexdec($item); // ✅ replaces gmp_strval
                        $value = Utils::fromWei((string)$decimal, 'ether');
                        break;
                    }
                }

                $eth_balance = $value ?: 'Unavailable';
            } catch (Exception $e) {
                $eth_balance = 'Error';
            }

            $balanceReady = true;
        });

        $timeout = 0;
        while (!$balanceReady && $timeout < 2000000) {
            usleep(100000); // wait for callback
            $timeout += 100000;
        }

        if (!$balanceReady) {
            $eth_balance = 'Timeout';
        }

    } catch (Exception $e) {
        $eth_balance = "Exception";
    }
} else {
    $eth_balance = "Invalid ETH address";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Crypto Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header>
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    <nav><a href="logout.php" class="logout-btn">Logout</a></nav>
</header>

<main>
    <section class="crypto-container">
        <div id="balance-display">
            <h2>Your Balances</h2>
            <p>USDT: <span id="usdt-amount"><?php echo $usdt_balance; ?> USDT</span></p>
            <p>BTC : <span id="btc-amount"><?php echo $btc_balance; ?> BTC</span></p>
            <p>ETH : <span id="eth-amount"><?php echo htmlspecialchars($eth_balance); ?> ETH</span></p>
            <p>ETH Address: <code><?php echo htmlspecialchars($eth_address); ?></code></p>
        </div>

        <div style="margin-bottom: 20px;">
            <a href="buy_sell_form.html" class="trade-btn">Buy/Sell Crypto</a>
        </div>

        <div id="crypto-prices">
            <h2>Current Prices</h2>
            <div class="crypto">
                <h3><a href="#" id="btc-link">BTC/USDT</a></h3>
                <p id="btc-price">Loading...</p>
            </div>
            <div class="crypto">
                <h3><a href="#" id="eth-link">ETH/USDT</a></h3>
                <p id="eth-price">Loading...</p>
            </div>
        </div>

        <div id="crypto-table">
            <h2>Market Data (24h)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Coin</th>
                        <th>Price</th>
                        <th>24h High</th>
                        <th>24h Low</th>
                        <th>Volume</th>
                    </tr>
                </thead>
                <tbody id="market-data">
                    <tr><td colspan="5">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <div id="charts">
            <div id="btc-chart" class="chart-container" style="display:none;">
                <h3>BTC/USDT Chart</h3>
                <canvas id="btcChart"></canvas>
            </div>
            <div id="eth-chart" class="chart-container" style="display:none;">
                <h3>ETH/USDT Chart</h3>
                <canvas id="ethChart"></canvas>
            </div>
        </div>
    </section>
</main>

<footer>
    <p>© <?php echo date("Y"); ?> My Crypto Dashboard</p>
</footer>
<script src="dashboard.js"></script>
</body>
</html>
