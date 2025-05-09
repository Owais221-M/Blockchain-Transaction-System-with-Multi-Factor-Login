document.addEventListener('DOMContentLoaded', () => {
    let btcChart, ethChart;

    async function fetchBalance() {
        try {
            const response = await fetch('get_balance.php');
            const data = await response.json();

            console.log("🧪 Response from PHP:", data); // Optional for debugging

            if ('balance' in data) {
                document.getElementById('usdt-amount').textContent =
                    parseFloat(data.balance).toFixed(2) + " USDT";
                document.getElementById('btc-amount').textContent =
                    parseFloat(data.btc_balance).toFixed(6) + " BTC";

                // ✅ Final ETH display logic (string/number safe)
                const eth = data.eth_balance;
                const parsed = parseFloat(eth);
                if (!isNaN(parsed)) {
                    document.getElementById('eth-amount').textContent =
                        parsed.toFixed(6) + " ETH";
                } else {
                    document.getElementById('eth-amount').textContent =
                        eth || "Unavailable ETH";
                }
            } else if (data.error) {
                console.error(data.error);
            }
        } catch (error) {
            console.error('Error fetching balances:', error);
        }
    }

    async function updatePrices() {
        try {
            const response = await fetch(
                'https://api.binance.com/api/v3/ticker/24hr?symbols=["BTCUSDT","ETHUSDT"]'
            );
            const data = await response.json();

            if (data) {
                const btcData = data.find(item => item.symbol === "BTCUSDT");
                const ethData = data.find(item => item.symbol === "ETHUSDT");

                if (btcData && ethData) {
                    document.getElementById('btc-price').textContent =
                        `$${parseFloat(btcData.lastPrice).toFixed(2)}`;
                    document.getElementById('eth-price').textContent =
                        `$${parseFloat(ethData.lastPrice).toFixed(2)}`;

                    document.getElementById('market-data').innerHTML = `
                        <tr>
                            <td>BTC</td>
                            <td>$${parseFloat(btcData.lastPrice).toFixed(2)}</td>
                            <td>$${parseFloat(btcData.highPrice).toFixed(2)}</td>
                            <td>$${parseFloat(btcData.lowPrice).toFixed(2)}</td>
                            <td>${parseFloat(btcData.volume).toFixed(2)} BTC</td>
                        </tr>
                        <tr>
                            <td>ETH</td>
                            <td>$${parseFloat(ethData.lastPrice).toFixed(2)}</td>
                            <td>$${parseFloat(ethData.highPrice).toFixed(2)}</td>
                            <td>$${parseFloat(ethData.lowPrice).toFixed(2)}</td>
                            <td>${parseFloat(ethData.volume).toFixed(2)} ETH</td>
                        </tr>
                    `;
                }
            }
        } catch (error) {
            console.error('Error fetching prices:', error);
        }
    }

    document.getElementById('btc-link').addEventListener('click', (e) => {
        e.preventDefault();
        showChart('btc');
    });

    document.getElementById('eth-link').addEventListener('click', (e) => {
        e.preventDefault();
        showChart('eth');
    });

    function showChart(crypto) {
        document.getElementById('btc-chart').style.display = 'none';
        document.getElementById('eth-chart').style.display = 'none';

        if (crypto === 'btc') {
            document.getElementById('btc-chart').style.display = 'block';
            if (!btcChart) {
                createChart('btc');
            }
        } else {
            document.getElementById('eth-chart').style.display = 'block';
            if (!ethChart) {
                createChart('eth');
            }
        }
    }

    async function createChart(crypto) {
        const ctx = document.getElementById(
            crypto === 'btc' ? 'btcChart' : 'ethChart'
        ).getContext('2d');

        const historicalData = await fetchHistoricalData(crypto);

        if (!historicalData.timestamps.length) {
            console.error("No historical data available.");
            return;
        }

        const chartData = {
            labels: historicalData.timestamps,
            datasets: [
                {
                    label: `${crypto.toUpperCase()}/USDT Price (Last 1 Hour)`,
                    data: historicalData.prices,
                    borderColor: 'rgba(240, 185, 11, 1)',
                    backgroundColor: 'rgba(240, 185, 11, 0.2)',
                    fill: true,
                    tension: 0.1
                }
            ]
        };

        const chartOptions = {
            responsive: true,
            scales: {
                x: {
                    title: { display: true, text: 'Time' },
                    ticks: { color: '#eaeaea' }
                },
                y: {
                    title: { display: true, text: 'Price (USDT)' },
                    ticks: { color: '#eaeaea' }
                }
            },
            plugins: {
                legend: { labels: { color: '#eaeaea' } }
            }
        };

        if (crypto === 'btc') {
            btcChart = new Chart(ctx, { type: 'line', data: chartData, options: chartOptions });
        } else {
            ethChart = new Chart(ctx, { type: 'line', data: chartData, options: chartOptions });
        }
    }

    async function fetchHistoricalData(crypto) {
        try {
            const symbol = crypto.toUpperCase() + "USDT";

            const response = await fetch(
                `https://api.binance.com/api/v3/klines?symbol=${symbol}&interval=1m&limit=60`
            );
            const data = await response.json();

            if (!Array.isArray(data) || data.length === 0) {
                console.error(`No valid historical data received for ${crypto}.`);
                return { timestamps: [], prices: [] };
            }

            const timestamps = data.map(entry =>
                new Date(entry[0]).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            );
            const prices = data.map(entry => parseFloat(entry[4]));

            return { timestamps, prices };
        } catch (error) {
            console.error(`Error fetching ${crypto} historical data:`, error);
            return { timestamps: [], prices: [] };
        }
    }

    updatePrices();
    fetchBalance();
});
