document.addEventListener("DOMContentLoaded", function () {

    /* ELEMENTS */
    const profileBtn = document.getElementById("profileBtn");
    const passwordBtn = document.getElementById("passwordBtn");
    const notiBtn = document.getElementById("notiBtn");
    const orderBtn = document.getElementById("orderBtn");
    const historyBtn = document.getElementById("historyBtn");
    const viewProfileBtn = document.getElementById("viewProfileBtn");
    const backBtn = document.getElementById("backBtn");

    const form = document.getElementById("passwordForm");
    const message = document.getElementById("passwordMessage");
    const displayHeaderName = document.getElementById("displayHeaderName");

    /* NAVIGATION LOGIC */

    // My Profile & View Profile Pages
    profileBtn.addEventListener("click", function () {
        window.location.href = "user_pf.html";
    });

    viewProfileBtn.addEventListener("click", function () {
        window.location.href = "user_pf.html";
    });

    // Password Page (Reload / Reset)
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
    if (orderBtn) {
        orderBtn.addEventListener("click", function () {
            window.location.href = "user_orders.html";
        });
    }

    // Delivery History Page
    if (historyBtn) {
        historyBtn.addEventListener("click", function () {
            window.location.href = "user_delivery.html";
        });
    }

    // Back to Homepage
    backBtn.addEventListener("click", function () {
        window.location.href = "homepage.html";
    });

    /* LOAD DISPLAY NAME FROM STORAGE */

    const savedProfile = localStorage.getItem("waterUserProfile");
    if (savedProfile) {
        try {
            const data = JSON.parse(savedProfile);
            if (data.displayName) {
                displayHeaderName.textContent = data.displayName;
            } else if (data.username) {
                displayHeaderName.textContent = data.username;
            }
        } catch (error) {
            console.log("Unable to load profile header name.");
        }
    }

    /* PASSWORD CHANGE LOGIC */

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        const currentPassword = document.getElementById("currentPassword").value.trim();
        const newPassword = document.getElementById("newPassword").value.trim();
        const confirmPassword = document.getElementById("confirmPassword").value.trim();

        if (!currentPassword || !newPassword || !confirmPassword) {
            message.textContent = "Please fill in all fields.";
            message.style.color = "#dc3545";
            return;
        }

        if (newPassword.length < 6) {
            message.textContent = "New password must be at least 6 characters long.";
            message.style.color = "#dc3545";
            return;
        }

        if (newPassword !== confirmPassword) {
            message.textContent = "New password and Confirm password do not match.";
            message.style.color = "#dc3545";
            return;
        }

        /* Success Case */
        message.textContent = "Password changed successfully! Redirecting...";
        message.style.color = "#198754";

        /* Reset Form Fields */
        form.reset();

        /* Optional: Redirect after success */
        /*
        setTimeout(() => {
            window.location.href = "login.html";
        }, 2000);
        */
    });
});