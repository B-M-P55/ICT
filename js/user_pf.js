document.addEventListener("DOMContentLoaded", function () {


    /* =========================================
       ELEMENTS
    ========================================= */

    const profileBtn =
        document.getElementById("profileBtn");

    const passwordBtn =
        document.getElementById("passwordBtn");

    const orderBtn =
        document.getElementById("orderBtn");

    const historyBtn =
        document.getElementById("historyBtn");

    const viewProfileBtn =
        document.getElementById("viewProfileBtn");

    const backBtn =
        document.getElementById("backBtn");

    const profileContent =
        document.getElementById("profileContent");

    const otherContent =
        document.getElementById("otherContent");

    const otherTitle =
        document.getElementById("otherTitle");

    const otherText =
        document.getElementById("otherText");

    const form =
        document.getElementById("profileForm");

    const message =
        document.getElementById("message");



    /* =========================================
       FUNCTION: ACTIVE MENU
    ========================================= */

    function activateMenu(button) {

        document
            .querySelectorAll(".sidebar-item")
            .forEach(function (item) {

                item.classList.remove("selected");

            });


        button.classList.add("selected");

    }



    /* =========================================
       SHOW PROFILE
    ========================================= */

    function showProfile() {

        profileContent.style.display = "block";

        otherContent.style.display = "none";

        activateMenu(profileBtn);

    }



    /* =========================================
       SHOW OTHER PAGE
    ========================================= */

    function showOtherPage(title, text, button) {

        profileContent.style.display = "none";

        otherContent.style.display = "block";

        otherTitle.textContent = title;

        otherText.textContent = text;

        activateMenu(button);

    }



    /* =========================================
       MY PROFILE
    ========================================= */

    profileBtn.addEventListener(
        "click",
        function () {

            showProfile();

        }
    );



    /* =========================================
       VIEW PROFILE
    ========================================= */

    viewProfileBtn.addEventListener(
        "click",
        function () {

            showProfile();

        }
    );



    /* =========================================
       PASSWORD
    ========================================= */

    passwordBtn.addEventListener(
        "click",
        function () {

            showOtherPage(
                "Password",
                "Password settings will appear here.",
                passwordBtn
            );

        }
    );



    /* =========================================
       MY ORDER
    ========================================= */

    orderBtn.addEventListener(
        "click",
        function () {

            showOtherPage(
                "My Order",
                "Your orders will appear here.",
                orderBtn
            );

        }
    );



    /* =========================================
       DELIVERY HISTORY
    ========================================= */

    historyBtn.addEventListener(
        "click",
        function () {

            showOtherPage(
                "Delivery History",
                "Your delivery history will appear here.",
                historyBtn
            );

        }
    );



    /* =========================================
       BACK
    ========================================= */

    backBtn.addEventListener(
        "click",
        function () {

            window.history.back();

        }
    );



    /* =========================================
       SAVE FORM
    ========================================= */

    form.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();


            const username =
                document
                    .getElementById("username")
                    .value
                    .trim();


            const displayName =
                document
                    .getElementById("displayName")
                    .value
                    .trim();


            const township =
                document
                    .getElementById("township")
                    .value;


            const phone =
                document
                    .getElementById("phone")
                    .value
                    .trim();


            const email =
                document
                    .getElementById("email")
                    .value
                    .trim();



            /* Username validation */

            const usernamePattern =
                /^[A-Za-z0-9_]+$/;


            if (username === "") {

                message.textContent =
                    "Please enter your username.";

                message.style.color =
                    "#dc3545";

                return;

            }


            if (!usernamePattern.test(username)) {

                message.textContent =
                    "Username must not include spaces or special characters.";

                message.style.color =
                    "#dc3545";

                return;

            }



            /* Email validation */

            if (
                email !== "" &&
                !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
            ) {

                message.textContent =
                    "Please enter a valid email address.";

                message.style.color =
                    "#dc3545";

                return;

            }



            /* Data */

            const userData = {

                username: username,

                displayName: displayName,

                township: township,

                phone: phone,

                email: email

            };



            /* Save */

            localStorage.setItem(
                "waterUserProfile",
                JSON.stringify(userData)
            );



            /* Success */

            message.textContent =
                "Profile saved successfully!";

            message.style.color =
                "#198754";

        }
    );



    /* =========================================
       LOAD SAVED PROFILE
    ========================================= */

    const savedProfile =
        localStorage.getItem("waterUserProfile");


    if (savedProfile) {

        try {

            const data =
                JSON.parse(savedProfile);


            document
                .getElementById("username")
                .value =
                data.username || "";


            document
                .getElementById("displayName")
                .value =
                data.displayName || "";


            document
                .getElementById("township")
                .value =
                data.township || "";


            document
                .getElementById("phone")
                .value =
                data.phone || "";


            document
                .getElementById("email")
                .value =
                data.email || "";

        }

        catch (error) {

            console.log(
                "Unable to load saved profile."
            );

        }

    }

});