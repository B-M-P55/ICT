// ================= CHECKOUT FORM =================

const checkoutForm = document.getElementById("checkoutForm");

checkoutForm.addEventListener("submit", function (event) {

    event.preventDefault();

    const firstName = document.getElementById("firstName").value.trim();
    const lastName = document.getElementById("lastName").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const email = document.getElementById("email").value.trim();

    // Check required fields

    if (
        firstName === "" ||
        lastName === "" ||
        phone === "" ||
        email === ""
    ) {

        alert("Please fill in all required fields.");

        return;
    }


    // Simple phone validation

    if (phone.length < 7) {

        alert("Please enter a valid phone number.");

        return;
    }


    // If everything is correct

    alert(
        "Thank you, " +
        firstName +
        "! Your order has been placed successfully."
    );


    // Clear form

    checkoutForm.reset();

});