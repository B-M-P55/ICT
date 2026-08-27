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