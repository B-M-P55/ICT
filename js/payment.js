/* =========================================
   PAYMENT PAGE
========================================= */


/* =========================================
   GET ELEMENTS
========================================= */

const paymentForm =
    document.getElementById("paymentForm");


const paymentMethod =
    document.getElementById("paymentMethod");


const paymentPhoto =
    document.getElementById("paymentPhoto");


const fileName =
    document.getElementById("fileName");


const paymentMessage =
    document.getElementById("paymentMessage");



/* =========================================
   PAYMENT PHOTO
========================================= */

if (paymentPhoto) {

    paymentPhoto.addEventListener(
        "change",
        function () {

            if (paymentPhoto.files.length > 0) {

                fileName.textContent =
                    paymentPhoto.files[0].name;

            } else {

                fileName.textContent =
                    "Attach the photo of payment";

            }

        }
    );

}



/* =========================================
   PAYMENT FORM
========================================= */

if (paymentForm) {

    paymentForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            /* Clear previous message */

            paymentMessage.textContent = "";



            /* =================================
               CHECK PAYMENT METHOD
            ================================= */

            if (paymentMethod.value === "") {

                paymentMessage.textContent =
                    "Please choose your payment method.";

                paymentMessage.style.color = "red";

                return;

            }



            /* =================================
               CHECK PAYMENT PHOTO
            ================================= */

            if (
                paymentMethod.value === "KBZ Pay" &&
                paymentPhoto.files.length === 0
            ) {

                paymentMessage.textContent =
                    "Please attach the photo of payment.";

                paymentMessage.style.color = "red";

                return;

            }



            /* =================================
               PAYMENT DATA
            ================================= */

            const paymentData = {

                paymentMethod:
                    paymentMethod.value,

                paymentPhoto:
                    paymentPhoto.files.length > 0
                        ? paymentPhoto.files[0].name
                        : null

            };


            console.log(
                "Payment Data:",
                paymentData
            );



            /* =================================
               SUCCESS
            ================================= */

            paymentMessage.textContent =
                "Payment confirmed successfully.";

            paymentMessage.style.color =
                "green";


            console.log(
                "Payment confirmed."
            );

        }
    );

}