document.addEventListener("DOMContentLoaded", function () {
    const profileBtn = document.getElementById("profileBtn");
    const passwordBtn = document.getElementById("passwordBtn");
    const notificationBtn = document.getElementById("notificationBtn");
    const orderBtn = document.getElementById("orderBtn");
    const deliveryBtn = document.getElementById("deliveryBtn");
    const viewProfileBtn = document.getElementById("viewProfileBtn");
    const backBtn = document.getElementById("backBtn");

    if (profileBtn) {
        profileBtn.addEventListener("click", () => window.location.href = "user_pf.html");
    }

    if (passwordBtn) {
        passwordBtn.addEventListener("click", () => window.location.href = "user_pw.html");
    }

    if (notificationBtn) {
        notificationBtn.addEventListener("click", () => window.location.href = "user_noti.html");
    }

    if (orderBtn) {
        orderBtn.addEventListener("click", () => window.location.href = "user_orders.html");
    }

    if (deliveryBtn) {
        deliveryBtn.addEventListener("click", () => window.location.href = "user_delivery.html");
    }

    if (viewProfileBtn) {
        viewProfileBtn.addEventListener("click", () => window.location.href = "user_pf.html");
    }

    if (backBtn) {
        backBtn.addEventListener("click", () => window.history.back());
    }
});