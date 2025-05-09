document.addEventListener('DOMContentLoaded', () => {
    const coinSelect = document.getElementById('coin');
    const amountInput = document.getElementById('amount');
    const displayPrice = document.getElementById('displayPrice');
    const hiddenPrice = document.getElementById('price');
    const totalDisplay = document.getElementById('totalDisplay');
    const typeInputs = document.getElementsByName('type');

    updatePrice();

    coinSelect.addEventListener('change', updatePrice);

    amountInput.addEventListener('input', updateTotal);
    typeInputs.forEach(input => {
        input.addEventListener('change', updateTotal);
    });

    setInterval(updatePrice, 10000);

    async function updatePrice() {
        const coin = coinSelect.value; 
        const symbol = coin + "USDT";  

        try {
            const response = await fetch(`https://api.binance.com/api/v3/ticker/price?symbol=${symbol}`);
            const data = await response.json();

            if (data.price) {
                displayPrice.value = parseFloat(data.price).toFixed(2);
                hiddenPrice.value = parseFloat(data.price).toFixed(2);
            } else {
                displayPrice.value = "Error";
                hiddenPrice.value = 0;
            }
        } catch (error) {
            console.error("Error fetching price:", error);
            displayPrice.value = "Error";
            hiddenPrice.value = 0;
        }

        updateTotal();
    }

    function updateTotal() {
        const priceVal = parseFloat(hiddenPrice.value) || 0;
        const amountVal = parseFloat(amountInput.value) || 0;
        const total = priceVal * amountVal;
        
        totalDisplay.value = total.toFixed(2);
    }
});
