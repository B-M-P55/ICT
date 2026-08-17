/* =========================================
   SIDEBAR NAVIGATION
========================================= */

const profileBtn = document.getElementById("profileBtn");
const passwordBtn = document.getElementById("passwordBtn");
const notificationBtn = document.getElementById("notificationBtn");
const backBtn = document.getElementById("backBtn");
const viewProfileBtn = document.getElementById("viewProfileBtn");


/* My Profile */

if (profileBtn) {

    profileBtn.addEventListener("click", function () {

        window.location.href = "user_pf.html";

    });

}


/* Password */

if (passwordBtn) {

    passwordBtn.addEventListener("click", function () {

        window.location.href = "user_pw.html";

    });

}


/* Notifications */

if (notificationBtn) {

    notificationBtn.addEventListener("click", function () {

        window.location.href = "user_noti.html";

    });

}


/* View Profile */

if (viewProfileBtn) {

    viewProfileBtn.addEventListener("click", function () {

        window.location.href = "user_pf.html";

    });

}


/* Back */

if (backBtn) {

    backBtn.addEventListener("click", function () {

        window.history.back();

    });

}


/* =========================================
   PASSWORD FORM
========================================= */

const passwordForm =
    document.getElementById("passwordForm");


if (passwordForm) {

    passwordForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            const currentPassword =
                document.getElementById(
                    "currentPassword"
                ).value;


            const newPassword =
                document.getElementById(
                    "newPassword"
                ).value;


            const confirmPassword =
                document.getElementById(
                    "confirmPassword"
                ).value;


            const message =
                document.getElementById(
                    "passwordMessage"
                );


            /* Empty check */

            if (
                currentPassword === "" ||
                newPassword === "" ||
                confirmPassword === ""
            ) {

                message.textContent =
                    "Please fill in all fields.";

                message.style.color = "red";

                return;

            }


            /* Password length */

            if (newPassword.length < 6) {

                message.textContent =
                    "New password must be at least 6 characters.";

                message.style.color = "red";

                return;

            }


            /* Confirm password */

            if (newPassword !== confirmPassword) {

                message.textContent =
                    "New password and confirm password do not match.";

                message.style.color = "red";

                return;

            }


            /* Success */

            message.textContent =
                "Password changed successfully.";

            message.style.color = "green";


            passwordForm.reset();

        }
    );

}