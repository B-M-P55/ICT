document.addEventListener('DOMContentLoaded', function () {
    // Order Quantity Elements
    const btnMinus = document.getElementById('btnMinus');
    const btnPlus = document.getElementById('btnPlus');
    const itemQty = document.getElementById('itemQty');
    const itemSubtotal = document.getElementById('itemSubtotal');
    const orderSubtotal = document.getElementById('orderSubtotal');
    const orderTotal = document.getElementById('orderTotal');

    const PRICE_PER_UNIT = 1000;
    let currentQuantity = 1;

    // Function to recalculate prices
    function updatePrices() {
        const totalAmount = currentQuantity * PRICE_PER_UNIT;
        itemQty.textContent = currentQuantity;
        
        // Format display
        const formattedTotal = `${totalAmount} Ks.`;
        itemSubtotal.textContent = formattedTotal;
        orderSubtotal.textContent = formattedTotal;
        orderTotal.textContent = formattedTotal;
    }

    // Plus Button Event
    btnPlus.addEventListener('click', function () {
        currentQuantity++;
        updatePrices();
    });

    // Minus Button Event
    btnMinus.addEventListener('click', function () {
        if (currentQuantity > 1) { // Prevents quantity from going below 1
            currentQuantity--;
            updatePrices();
        }
    });

    // --- Modal & Form Logic ---
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const paymentModalElement = document.getElementById('paymentModal');
    const paymentModal = new bootstrap.Modal(paymentModalElement);
    
    const paymentSelect = document.getElementById('paymentMethodSelect');
    const attachmentField = document.getElementById('attachmentField');
    const confirmPaymentBtn = document.getElementById('confirmPaymentBtn');

    // Open Payment Modal
    placeOrderBtn.addEventListener('click', function () {
        const billingForm = document.getElementById('billingForm');
        if (billingForm.checkValidity()) {
            paymentModal.show();
        } else {
            billingForm.reportValidity();
        }
    });

    // Toggle attachment field based on KBZ Pay
    paymentSelect.addEventListener('change', function () {
        if (this.value === 'kbz') {
            attachmentField.classList.remove('d-none');
        } else {
            attachmentField.classList.add('d-none');
        }
    });

    // Confirm Payment
    confirmPaymentBtn.addEventListener('click', function () {
        if (!paymentSelect.value) {
            alert('Please select a payment method.');
            return;
        }

        if (paymentSelect.value === 'kbz') {
            const fileInput = document.getElementById('paymentProof');
            if (!fileInput.files.length) {
                alert('Please attach your payment screenshot.');
                return;
            }
        }

        alert(`Thank you! Your order of ${currentQuantity} bottle(s) totaling ${currentQuantity * PRICE_PER_UNIT} Ks. has been placed successfully.`);
        paymentModal.hide();
    });
});