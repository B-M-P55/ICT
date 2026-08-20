document.addEventListener("DOMContentLoaded", function () {

    /* ELEMENTS */
    const profileBtn = document.getElementById("profileBtn");
    const passwordBtn = document.getElementById("passwordBtn");
    const notiBtn = document.getElementById("notiBtn");
    const orderBtn = document.getElementById("orderBtn");
    const historyBtn = document.getElementById("historyBtn");
    const viewProfileBtn = document.getElementById("viewProfileBtn");
    const backBtn = document.getElementById("backBtn");
    
    const form = document.getElementById("profileForm");
    const message = document.getElementById("message");
    const displayHeaderName = document.getElementById("displayHeaderName");

    /* NAVIGATION LOGIC */

    // My Profile (Reload / Reset to current page)
    profileBtn.addEventListener("click", function () {
        window.location.href = "user_pf.html";
    });

    viewProfileBtn.addEventListener("click", function () {
        window.location.href = "user_pf.html";
    });

    // Password Page
    passwordBtn.addEventListener("click", function () {
        window.location.href = "user_pw.html";
    });

    // Notifications Page
    if (notiBtn) {
        notiBtn.addEventListener("click", function () {
            window.location.href = "user_noti.html";
        });
    }

    // My Order Page
    orderBtn.addEventListener("click", function () {
        window.location.href = "user_orders.html";
    });

    // Delivery History Page
    historyBtn.addEventListener("click", function () {
        window.location.href = "user_delivery.html";
    });

    // Back to Homepage
    backBtn.addEventListener("click", function () {
        window.location.href = "homepage.html";
    });

    /* SAVE FORM LOGIC */

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        const username = document.getElementById("username").value.trim();
        const displayName = document.getElementById("displayName").value.trim();
        const township = document.getElementById("township").value;
        const phone = document.getElementById("phone").value.trim();
        const email = document.getElementById("email").value.trim();

        /* Username validation */
        const usernamePattern = /^[A-Za-z0-9_]+$/;

        if (username === "") {
            message.textContent = "Please enter your username.";
            message.style.color = "#dc3545";
            return;
        }

        if (!usernamePattern.test(username)) {
            message.textContent = "Username must not include spaces or special characters.";
            message.style.color = "#dc3545";
            return;
        }

        /* Email validation */
        if (email !== "" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            message.textContent = "Please enter a valid email address.";
            message.style.color = "#dc3545";
            return;
        }

        /* Save Data */
        const userData = {
            username: username,
            displayName: displayName,
            township: township,
            phone: phone,
            email: email
        };

        localStorage.setItem("waterUserProfile", JSON.stringify(userData));

        /* Update Header Name dynamically */
        if (displayName !== "") {
            displayHeaderName.textContent = displayName;
        } else {
            displayHeaderName.textContent = username;
        }

        /* Success Message */
        message.textContent = "Profile saved successfully!";
        message.style.color = "#198754";
    });

    /* LOAD SAVED PROFILE DATA */

    const savedProfile = localStorage.getItem("waterUserProfile");

    if (savedProfile) {
        try {
            const data = JSON.parse(savedProfile);

            document.getElementById("username").value = data.username || "";
            document.getElementById("displayName").value = data.displayName || "";
            document.getElementById("township").value = data.township || "";
            document.getElementById("phone").value = data.phone || "";
            document.getElementById("email").value = data.email || "";

            if (data.displayName) {
                displayHeaderName.textContent = data.displayName;
            } else if (data.username) {
                displayHeaderName.textContent = data.username;
            }
        } catch (error) {
            console.log("Unable to load saved profile.");
        }
    }
});