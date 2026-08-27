document.addEventListener("DOMContentLoaded", function () {

    const tableBody = document.getElementById("paymentTableBody");
    const searchBox = document.querySelector(".search-box");

    // =========================================
    // LOAD PAYMENT RECORDS
    // =========================================

    loadPayments();


    function loadPayments() {

        fetch("../php/get_payment_history.php")
            .then(response => response.json())
            .then(data => {

                if (!data.success) {
                    alert("Unable to load payment records.");
                    return;
                }

                displayPayments(data.payments);

            })
            .catch(error => {

                console.error("Error:", error);

                alert("Unable to load payment records.");

            });
    }


    // =========================================
    // DISPLAY PAYMENT RECORDS
    // =========================================

    function displayPayments(payments) {

        tableBody.innerHTML = "";

        if (payments.length === 0) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align:center;">
                        No payment records found.
                    </td>
                </tr>
            `;

            return;
        }


        payments.forEach(payment => {

            const row = document.createElement("tr");

            // Database status → Display status
            let statusText = "";
            let statusClass = "";
            let statusIcon = "";

            if (payment.payment_status === "completed") {

                statusText = "Paid";
                statusClass = "paid";
                statusIcon = "fa-circle-check";

            } else if (payment.payment_status === "unpaid") {

                statusText = "Unpaid";
                statusClass = "unpaid";
                statusIcon = "fa-circle-xmark";

            } else {

                statusText = "Pending";
                statusClass = "pending";
                statusIcon = "fa-clock";

            }


            // Payment method icon
            let methodIcon = "";

            if (
                payment.payment_method.toLowerCase() === "kpay"
            ) {

                methodIcon = "fa-mobile-screen-button";

            } else {

                methodIcon = "fa-money-bill-wave";

            }


            // Format date
            const paymentDate =
                new Date(payment.payment_date);

            const formattedDate =
                paymentDate.toLocaleDateString(
                    "en-US",
                    {
                        month: "long",
                        day: "numeric",
                        year: "numeric"
                    }
                );


            const formattedTime =
                paymentDate.toLocaleTimeString(
                    "en-US",
                    {
                        hour: "numeric",
                        minute: "2-digit"
                    }
                );


            row.innerHTML = `

                <td>#${payment.order_ID}</td>

                <td>
                    ${payment.customer_name || "Unknown"}
                </td>

                <td>
                    ${payment.payment_amount} Ks
                </td>

                <td>
                    ${formattedDate}
                    <br>
                    ${formattedTime}
                </td>

                <td>
                    <i class="fa-solid ${methodIcon}"></i>
                    ${payment.payment_method}
                </td>

                <td>

                    <span
                        class="status ${statusClass}"
                        data-payment-id="${payment.paymentID}"
                    >

                        <i class="fa-solid ${statusIcon}"></i>
                        ${statusText}

                    </span>

                    <button
                        class="edit-btn"
                        data-payment-id="${payment.paymentID}"
                    >

                        <i class="fa-solid fa-pen"></i>
                        Edit

                    </button>

                </td>

            `;


            tableBody.appendChild(row);

        });


        attachEditButtons();

        updateSummary(payments);

    }


    // =========================================
    // EDIT PAYMENT STATUS
    // =========================================

    function attachEditButtons() {

        const editButtons =
            document.querySelectorAll(".edit-btn");


        editButtons.forEach(button => {

            button.addEventListener("click", function () {

                const paymentID =
                    this.dataset.paymentId;


                const newStatus =
                    prompt(
                        "Enter payment status:\n\nPaid\nUnpaid\nPending",
                        "Pending"
                    );


                if (!newStatus) {
                    return;
                }


                const statusInput =
                    newStatus.toLowerCase().trim();


                let databaseStatus;


                // Convert display status
                // into database status

                if (statusInput === "paid") {

                    databaseStatus = "completed";

                } else if (statusInput === "unpaid") {

                    databaseStatus = "unpaid";

                } else if (statusInput === "pending") {

                    databaseStatus = "pending";

                } else {

                    alert(
                        "Please enter Paid, Unpaid, or Pending."
                    );

                    return;
                }


                updatePaymentStatus(
                    paymentID,
                    databaseStatus
                );

            });

        });

    }


    // =========================================
    // UPDATE PAYMENT STATUS
    // =========================================

    function updatePaymentStatus(
        paymentID,
        status
    ) {

        const formData = new FormData();

        formData.append(
            "paymentID",
            paymentID
        );

        formData.append(
            "status",
            status
        );


        fetch("../php/update_payment_status.php", {

            method: "POST",

            body: formData

        })

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                alert(
                    "Payment status updated successfully."
                );

                // Reload records from database
                loadPayments();

            } else {

                alert(
                    data.message ||
                    "Failed to update payment status."
                );

            }

        })

        .catch(error => {

            console.error("Error:", error);

            alert(
                "Unable to connect to the payment server."
            );

        });

    }


    // =========================================
    // UPDATE SUMMARY
    // =========================================

    function updateSummary(payments) {

        let totalOrders = payments.length;

        let totalSales = 0;

        let paidOrders = 0;

        let pendingOrders = 0;


        payments.forEach(payment => {

            totalSales +=
                Number(payment.payment_amount) || 0;


            if (
                payment.payment_status === "completed"
            ) {

                paidOrders++;

            }


            if (
                payment.payment_status === "pending"
            ) {

                pendingOrders++;

            }

        });


        const summaryCards =
            document.querySelectorAll(
                ".summary-card h2"
            );


        if (summaryCards.length >= 4) {

            summaryCards[0].textContent =
                totalOrders;

            summaryCards[1].textContent =
                "Ks " + totalSales;

            summaryCards[2].textContent =
                paidOrders;

            summaryCards[3].textContent =
                pendingOrders;

        }

    }


    // =========================================
    // SEARCH
    // =========================================

    if (searchBox) {

        const searchInput =
            document.createElement("input");

        searchInput.type = "text";

        searchInput.placeholder =
            "Search anything..";

        searchInput.style.border = "none";
        searchInput.style.outline = "none";
        searchInput.style.background = "transparent";
        searchInput.style.width = "150px";


        searchBox.innerHTML = "";


        const searchIcon =
            document.createElement("i");

        searchIcon.className =
            "fa-solid fa-magnifying-glass";


        searchBox.appendChild(searchIcon);

        searchBox.appendChild(searchInput);


        searchInput.addEventListener(
            "keyup",
            function () {

                const searchValue =
                    searchInput.value
                        .toLowerCase()
                        .trim();


                const rows =
                    tableBody.querySelectorAll("tr");


                rows.forEach(row => {

                    const rowText =
                        row.textContent.toLowerCase();


                    if (
                        rowText.includes(searchValue)
                    ) {

                        row.style.display = "";

                    } else {

                        row.style.display = "none";

                    }

                });

            }
        );

    }

});