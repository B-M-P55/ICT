/* =========================================
   SIDEBAR NAVIGATION
========================================= */

const profileBtn =
    document.getElementById("profileBtn");

const passwordBtn =
    document.getElementById("passwordBtn");

const notificationBtn =
    document.getElementById("notificationBtn");

const backBtn =
    document.getElementById("backBtn");

const viewProfileBtn =
    document.getElementById("viewProfileBtn");


/* =========================================
   MY PROFILE
========================================= */

if (profileBtn) {

    profileBtn.addEventListener(
        "click",
        function () {

            window.location.href =
                "user_pf.html";

        }
    );

}


/* =========================================
   PASSWORD
========================================= */

if (passwordBtn) {

    passwordBtn.addEventListener(
        "click",
        function () {

            window.location.href =
                "user_pw.html";

        }
    );

}


/* =========================================
   NOTIFICATIONS
========================================= */

if (notificationBtn) {

    notificationBtn.addEventListener(
        "click",
        function () {

            window.location.href =
                "user_noti.html";

        }
    );

}


/* =========================================
   VIEW PROFILE
========================================= */

if (viewProfileBtn) {

    viewProfileBtn.addEventListener(
        "click",
        function () {

            window.location.href =
                "user_pf.html";

        }
    );

}


/* =========================================
   BACK
========================================= */

if (backBtn) {

    backBtn.addEventListener(
        "click",
        function () {

            window.history.back();

        }
    );

}