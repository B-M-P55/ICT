document.addEventListener('DOMContentLoaded', function () {

    /* =========================================
       ORDER QUANTITY
    ========================================== */

    const btnMinus = document.getElementById('btnMinus');
    const btnPlus = document.getElementById('btnPlus');
    const itemQty = document.getElementById('itemQty');
    const itemSubtotal = document.getElementById('itemSubtotal');
    const orderSubtotal = document.getElementById('orderSubtotal');
    const orderTotal = document.getElementById('orderTotal');

    const PRICE_PER_UNIT = 1000;

    let currentQuantity = 1;


    /* =========================================
       UPDATE PRICE DISPLAY
    ========================================== */

    function updatePrices() {

        const totalAmount =
            currentQuantity * PRICE_PER_UNIT;

        itemQty.textContent =
            currentQuantity;

        const formattedTotal =
            `${totalAmount} Ks.`;

        itemSubtotal.textContent =
            formattedTotal;

        orderSubtotal.textContent =
            formattedTotal;

        orderTotal.textContent =
            formattedTotal;
    }


    /* =========================================
       PLUS BUTTON
    ========================================== */

    btnPlus.addEventListener('click', function () {

        currentQuantity++;

        updatePrices();
    });


    /* =========================================
       MINUS BUTTON
    ========================================== */

    btnMinus.addEventListener('click', function () {

        if (currentQuantity > 1) {

            currentQuantity--;

            updatePrices();
        }
    });


    /* =========================================
       PAYMENT MODAL
    ========================================== */

    const placeOrderBtn =
        document.getElementById('placeOrderBtn');

    const paymentModalElement =
        document.getElementById('paymentModal');

    const paymentModal =
        new bootstrap.Modal(paymentModalElement);

    const paymentSelect =
        document.getElementById('paymentMethodSelect');

    const attachmentField =
        document.getElementById('attachmentField');

    const confirmPaymentBtn =
        document.getElementById('confirmPaymentBtn');

    const paymentProof =
        document.getElementById('paymentProof');


    /* =========================================
       TEMPORARY TEST ORDER ID
       
       IMPORTANT:
       This will later come from the
       Order Management PHP.
    ========================================== */

    const TEST_ORDER_ID = 1;


    /* =========================================
       OPEN PAYMENT MODAL
    ========================================== */

    placeOrderBtn.addEventListener('click', function () {

        const billingForm =
            document.getElementById('billingForm');

        if (billingForm.checkValidity()) {

            paymentModal.show();

        } else {

            billingForm.reportValidity();
        }
    });


    /* =========================================
       PAYMENT METHOD CHANGE
    ========================================== */

    paymentSelect.addEventListener(
        'change',
        function () {

            if (this.value === 'kbz') {

                attachmentField.classList.remove(
                    'd-none'
                );

            } else {

                attachmentField.classList.add(
                    'd-none'
                );

                /*
                   Clear previously selected
                   file when switching to COD.
                */

                paymentProof.value = '';
            }
        }
    );


    /* =========================================
       CONFIRM PAYMENT
    ========================================== */

    confirmPaymentBtn.addEventListener(
        'click',
        async function () {

            /* -------------------------------
               CHECK PAYMENT METHOD
            -------------------------------- */

            if (!paymentSelect.value) {

                alert(
                    'Please select a payment method.'
                );

                return;
            }


            /* -------------------------------
               CHECK KBZ PAYMENT PHOTO
            -------------------------------- */

            if (paymentSelect.value === 'kbz') {

                if (!paymentProof.files.length) {

                    alert(
                        'Please attach your payment screenshot.'
                    );

                    return;
                }
            }


            /* -------------------------------
               CALCULATE TOTAL
            -------------------------------- */

            const paymentAmount =
                currentQuantity * PRICE_PER_UNIT;


            /* -------------------------------
               CONVERT PAYMENT METHOD
            -------------------------------- */

            let paymentMethod;

            if (paymentSelect.value === 'kbz') {

                paymentMethod = 'Kpay';

            } else {

                paymentMethod = 'Cash on Delivery';
            }


            /* -------------------------------
               CREATE FORM DATA
            -------------------------------- */

            const formData =
                new FormData();

            formData.append(
                'order_ID',
                TEST_ORDER_ID
            );

            formData.append(
                'payment_amount',
                paymentAmount
            );

            formData.append(
                'payment_method',
                paymentMethod
            );


            /* -------------------------------
               ADD PAYMENT PHOTO
            -------------------------------- */

            if (
                paymentSelect.value === 'kbz' &&
                paymentProof.files.length > 0
            ) {

                formData.append(
                    'payment_photo',
                    paymentProof.files[0]
                );
            }


            /* -------------------------------
               DISABLE BUTTON
               PREVENT DOUBLE SUBMISSION
            -------------------------------- */

            confirmPaymentBtn.disabled = true;

            confirmPaymentBtn.textContent =
                'Processing...';


            try {

                /* ===========================
                   SEND TO PHP
                ============================ */

                const response =
                    await fetch(
                        'php/create_payment.php',
                        {
                            method: 'POST',
                            body: formData
                        }
                    );


                /* ===========================
                   GET PHP RESPONSE
                ============================ */

                const result =
                    await response.json();


                /* ===========================
                   SUCCESS
                ============================ */

                if (result.success) {

                    alert(
                        'Thank you! Your payment has been recorded successfully.'
                    );

                    paymentModal.hide();

                    console.log(
                        'Payment created:',
                        result
                    );


                } else {

                    alert(
                        result.message ||
                        'Payment failed.'
                    );
                }


            } catch (error) {

                console.error(
                    'Payment error:',
                    error
                );

                alert(
                    'Unable to connect to the payment server.'
                );


            } finally {

                /* =========================
                   RESTORE BUTTON
                ========================== */

                confirmPaymentBtn.disabled =
                    false;

                confirmPaymentBtn.textContent =
                    'Confirm';
            }

        }
    );


document.addEventListener("DOMContentLoaded", function () {
  const township = document.getElementById("township");
  const arrivalTime = document.getElementById("arrivalTime");
  const minusBtn = document.getElementById("minusBtn");
  const plusBtn = document.getElementById("plusBtn");
  const quantityElement = document.getElementById("quantity");
  const productPrice = document.getElementById("productPrice");
  const totalPrice = document.getElementById("totalPrice");
  const orderForm = document.getElementById("orderForm");
  const orderMessage = document.getElementById("orderMessage");
  const deliveryDateInput = document.getElementById("deliveryDate");
  const paymentRadios = document.querySelectorAll('input[name="payment"]');
  const kbzUploadContainer = document.getElementById("kbzUploadContainer");
  const paymentSlip = document.getElementById("paymentSlip");

  const pricePerItem = 1000;
  let quantity = 1;

  const arrivalTimes = {
    Insein: "30 minutes",
    Mayangone: "45 minutes",
    Hlaing: "50 minutes"
  };

  // Restrict calendar past dates
  if (deliveryDateInput) {
    deliveryDateInput.min = new Date().toISOString().split("T")[0];
  }

  // Update arrival time based on Township selection
  township.addEventListener("change", function () {
    arrivalTime.textContent = arrivalTimes[township.value] || "Please select your township";
  });

  // Calculate prices
  function updateTotal() {
    const productTotal = pricePerItem * quantity;
    productPrice.textContent = `${productTotal.toLocaleString()} Ks.`;
    totalPrice.textContent = `${productTotal.toLocaleString()} Ks.`;
    quantityElement.textContent = quantity;
  }

  plusBtn.addEventListener("click", () => {
    quantity++;
    updateTotal();
  });

  minusBtn.addEventListener("click", () => {
    if (quantity > 1) {
      quantity--;
      updateTotal();
    }
  });

  // Toggle KBZ Pay attachment input field
  paymentRadios.forEach(radio => {
    radio.addEventListener("change", function () {
      if (this.value === "KBZ Pay") {
        kbzUploadContainer.style.display = "block";
        paymentSlip.setAttribute("required", "required");
      } else {
        kbzUploadContainer.style.display = "none";
        paymentSlip.removeAttribute("required");
        paymentSlip.value = ""; 
      }
    });
  });

  // Form submission logic
  orderForm.addEventListener("submit", function (e) {
    e.preventDefault();

    if (!township.value) {
      arrivalTime.textContent = "Please select your township";
      township.focus();
      return;
    }

    const selectedPayment = document.querySelector('input[name="payment"]:checked').value;
    if (selectedPayment === "KBZ Pay" && !paymentSlip.files.length) {
      orderMessage.textContent = "Please attach your KBZ Pay payment screenshot.";
      orderMessage.style.color = "#d9534f";
      return;
    }

    orderMessage.textContent = `Order placed successfully! Estimated delivery time: ${arrivalTimes[township.value]}.`;
    orderMessage.style.color = "#168a42";
  });

  updateTotal();

});