document.addEventListener("DOMContentLoaded", function () {

    fetch("php/check_session.php")
        .then(function (response) { return response.json(); })
        .then(function (data) {

            var navButtons = document.querySelector(".nav-buttons");
            if (!navButtons) return;

            var homeLink = document.querySelector('.nav-links a[href="homepage.html"], .nav-links a[href="index.html"]');

            if (data.logged_in) {
                // Logged in: show profile icon, Home goes to homepage.html
                navButtons.innerHTML =
                    '<button class="order-btn" onclick="window.location.href=\'user_orders.html\'">ORDER NOW</button>' +
                    '<a href="user_pf.html" class="profile"><i class="fa-solid fa-user"></i></a>';

                if (homeLink) homeLink.setAttribute("href", "homepage.html");
            } else {
                // Not logged in: show login button, Home goes to index.html
                navButtons.innerHTML =
                    '<button class="order-btn" onclick="window.location.href=\'user_orders.html\'">ORDER NOW</button>' +
                    '<button class="order-btn" onclick="window.location.href=\'php/admin_login.php\'">LOG IN</button>';

                if (homeLink) homeLink.setAttribute("href", "index.html");
            }

        })
        .catch(function () {

        });

});
