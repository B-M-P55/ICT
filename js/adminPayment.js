document.addEventListener("DOMContentLoaded", function () {

    const tableBody =
        document.getElementById("paymentTableBody");

    const searchBox =
        document.querySelector(".search-box");

    const paginationButtons =
        document.querySelectorAll(".pagination button");


    /* =========================================
       LOAD PAYMENTS
    ========================================== */

    async function loadPayments() {

        try {

            const response =
                await fetch(
                    "php/get_payment_history.php"
                );

            const result =
                await response.json();


            if (!result.success) {

                alert(
                    result.message ||
                    "Failed to load payments."
                );

                return;
            }


            renderPayments(
                result.payments
            );


        } catch (error) {

            console.error(
                "Payment loading error:",
                error
            );

            alert(
                "Unable to connect to payment server."
            );
        }
    }


    /* =========================================
       RENDER PAYMENTS
    ========================================== */

    function renderPayments(payments) {

        tableBody.innerHTML = "";


        if (payments.length === 0) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="8"
                        style="text-align:center;">
                        No payment records found.
                    </td>
                </tr>
            `;

            updateSummary([]);

            return;
        }


        payments.forEach(function (payment) {

            const row =
                document.createElement("tr");


            /* -------------------------------
               ORDER NUMBER
            -------------------------------- */

            const orderCell =
                document.createElement("td");

            orderCell.textContent =
                "#" + payment.order_ID;


            /* -------------------------------
               CUSTOMER NAME
            -------------------------------- */

            const customerCell =
                document.createElement("td");

            customerCell.textContent =
                payment.customer_name;


            /* -------------------------------
               PAYMENT AMOUNT
            -------------------------------- */

            const amountCell =
                document.createElement("td");

            amountCell.textContent =
                Number(
                    payment.payment_amount
                ).toLocaleString() + " Ks";


            /* -------------------------------
               PAYMENT DATE
            -------------------------------- */

            const dateCell =
                document.createElement("td");

            const paymentDate =
                new Date(
                    payment.payment_date
                );

            dateCell.innerHTML =
                formatDate(paymentDate);


            /* -------------------------------
               PAYMENT METHOD
            -------------------------------- */

            const methodCell =
                document.createElement("td");

            if (
                payment.payment_method ===
                "Kpay"
            ) {

                methodCell.innerHTML = `
                    <i class="fa-solid
                       fa-mobile-screen-button">
                    </i>
                    KBZ Pay
                `;

            } else {

                methodCell.innerHTML = `
                    <i class="fa-solid
                       fa-money-bill-wave">
                    </i>
                    Cash On Delivery
                `;
            }


            /* -------------------------------
               PAYMENT STATUS
            -------------------------------- */

            const statusCell =
                document.createElement("td");

            statusCell.appendChild(
                createPaymentStatus(
                    payment
                )
            );


            /* -------------------------------
               DELIVERY STATUS
            -------------------------------- */

            const deliveryCell =
                document.createElement("td");

            deliveryCell.appendChild(
                createDeliveryStatus(
                    payment.delivery_status
                )
            );


            /* -------------------------------
               PAYMENT PROOF
            -------------------------------- */

            const proofCell =
                document.createElement("td");

            if (
                payment.payment_photo
            ) {

                const proofButton =
                    document.createElement("button");

                proofButton.className =
                    "edit-btn";

                proofButton.innerHTML = `
                    <i class="fa-solid fa-file">
                    </i>
                    View File
                `;

                proofButton.addEventListener(
                    "click",
                    function () {

                        viewPaymentProof(
                            payment.payment_photo
                        );

                    }
                );

                proofCell.appendChild(
                    proofButton
                );

            } else {

                proofCell.textContent =
                    "N/A";
            }


            /* -------------------------------
               ADD CELLS
            -------------------------------- */

            row.appendChild(orderCell);
            row.appendChild(customerCell);
            row.appendChild(amountCell);
            row.appendChild(dateCell);
            row.appendChild(methodCell);
            row.appendChild(statusCell);
            row.appendChild(deliveryCell);
            row.appendChild(proofCell);


            /* -------------------------------
               STORE PAYMENT ID
            -------------------------------- */

            row.dataset.paymentId =
                payment.paymentID;


            tableBody.appendChild(row);

        });


        updateSummary(payments);
    }


    /* =========================================
       PAYMENT STATUS
    ========================================== */

    function createPaymentStatus(payment) {

        const wrapper =
            document.createElement("span");

        wrapper.classList.add("status");


        if (
            payment.payment_status ===
            "completed"
        ) {

            wrapper.classList.add("paid");

            wrapper.innerHTML = `
                <i class="fa-solid
                   fa-circle-check">
                </i>
                Paid
            `;

        } else {

            wrapper.classList.add("pending");

            wrapper.innerHTML = `
                <i class="fa-solid
                   fa-clock">
                </i>
                Pending
            `;
        }


        wrapper.style.cursor =
            "pointer";


        wrapper.addEventListener(
            "click",
            function () {

                editPaymentStatus(
                    payment,
                    wrapper
                );

            }
        );


        return wrapper;
    }


    /* =========================================
       EDIT PAYMENT STATUS
    ========================================== */

    async function editPaymentStatus(
        payment,
        statusElement
    ) {

        const currentStatus =
            payment.payment_status ===
            "completed"
                ? "Paid"
                : "Pending";


        const newStatus =
            prompt(
                "Enter payment status:\n\nPaid\nPending",
                currentStatus
            );


        if (!newStatus) {
            return;
        }


        const cleanedStatus =
            newStatus
                .trim()
                .toLowerCase();


        let databaseStatus;


        if (
            cleanedStatus === "paid" ||
            cleanedStatus === "completed"
        ) {

            databaseStatus =
                "completed";

        } else if (
            cleanedStatus === "pending"
        ) {

            databaseStatus =
                "pending";

        } else {

            alert(
                "Please enter Paid or Pending."
            );

            return;
        }


        /* -------------------------------
           SEND UPDATE TO PHP
        -------------------------------- */

        const formData =
            new FormData();

        formData.append(
            "paymentID",
            payment.paymentID
        );

        formData.append(
            "status",
            databaseStatus
        );


        try {

            const response =
                await fetch(
                    "php/update_payment_status.php",
                    {
                        method: "POST",
                        body: formData
                    }
                );


            const result =
                await response.json();


            if (!result.success) {

                alert(
                    result.message ||
                    "Failed to update payment."
                );

                return;
            }


            /* -------------------------------
               UPDATE LOCAL DATA
            -------------------------------- */

            payment.payment_status =
                databaseStatus;


            /* -------------------------------
               UPDATE DISPLAY
            -------------------------------- */

            if (
                databaseStatus ===
                "completed"
            ) {

                statusElement.classList.remove(
                    "pending"
                );

                statusElement.classList.add(
                    "paid"
                );

                statusElement.innerHTML = `
                    <i class="fa-solid
                       fa-circle-check">
                    </i>
                    Paid
                `;

            } else {

                statusElement.classList.remove(
                    "paid"
                );

                statusElement.classList.add(
                    "pending"
                );

                statusElement.innerHTML = `
                    <i class="fa-solid
                       fa-clock">
                    </i>
                    Pending
                `;
            }


            updateSummary(
                getCurrentPayments()
            );


        } catch (error) {

            console.error(
                "Status update error:",
                error
            );

            alert(
                "Unable to connect to payment server."
            );
        }
    }


    /* =========================================
       DELIVERY STATUS
    ========================================== */

    function createDeliveryStatus(
        deliveryStatus
    ) {

        const wrapper =
            document.createElement("span");

        wrapper.classList.add("status");


        if (
            deliveryStatus ===
            "delivered"
        ) {

            wrapper.classList.add(
                "delivered"
            );

            wrapper.innerHTML = `
                <i class="fa-solid fa-truck">
                </i>
                Delivered
            `;

        } else if (
            deliveryStatus ===
            "shipping"
        ) {

            wrapper.classList.add(
                "pending"
            );

            wrapper.innerHTML = `
                <i class="fa-solid fa-truck">
                </i>
                Shipping
            `;

        } else {

            wrapper.classList.add(
                "pending"
            );

            wrapper.innerHTML = `
                <i class="fa-solid fa-clock">
                </i>
                Pending
            `;
        }


        return wrapper;
    }


    /* =========================================
       VIEW PAYMENT PROOF
    ========================================== */

    function viewPaymentProof(
        filename
    ) {

        const imagePath =
            "images/payment/" +
            filename;


        window.open(
            imagePath,
            "_blank"
        );
    }


    /* =========================================
       DATE FORMAT
    ========================================== */

    function formatDate(date) {

        if (
            Number.isNaN(
                date.getTime()
            )
        ) {

            return "Invalid date";
        }


        const datePart =
            date.toLocaleDateString(
                "en-US",
                {
                    year: "numeric",
                    month: "long",
                    day: "numeric"
                }
            );


        const timePart =
            date.toLocaleTimeString(
                "en-US",
                {
                    hour: "numeric",
                    minute: "2-digit"
                }
            );


        return `
            ${datePart}
            <br>
            ${timePart}
        `;
    }


    /* =========================================
       SUMMARY
    ========================================== */

    function updateSummary(
        payments
    ) {

        const summaryCards =
            document.querySelectorAll(
                ".summary-card h2"
            );


        if (
            summaryCards.length < 4
        ) {

            return;
        }


        let totalOrders =
            payments.length;

        let totalSales = 0;

        let paidOrders = 0;

        let pendingOrders = 0;


        payments.forEach(
            function (payment) {

                totalSales +=
                    Number(
                        payment.payment_amount
                    );


                if (
                    payment.payment_status ===
                    "completed"
                ) {

                    paidOrders++;

                } else {

                    pendingOrders++;
                }
            }
        );


        summaryCards[0].textContent =
            totalOrders.toLocaleString();


        summaryCards[1].textContent =
            "Ks " +
            totalSales.toLocaleString();


        summaryCards[2].textContent =
            paidOrders.toLocaleString();


        summaryCards[3].textContent =
            pendingOrders.toLocaleString();
    }


    /* =========================================
       GET CURRENT PAYMENTS FROM TABLE
    ========================================== */

    function getCurrentPayments() {

        const rows =
            tableBody.querySelectorAll("tr");

        const payments = [];


        rows.forEach(
            function (row) {

                const status =
                    row.querySelector(
                        ".status"
                    );


                if (!status) {
                    return;
                }


                payments.push({

                    payment_status:
                        status.classList.contains(
                            "paid"
                        )
                            ? "completed"
                            : "pending",

                    payment_amount:
                        row.cells[2]
                            ? parseInt(
                                row.cells[2]
                                    .textContent
                                    .replace(
                                        /[^0-9]/g,
                                        ""
                                    )
                              )
                            : 0
                });
            }
        );


        return payments;
    }


    /* =========================================
       SEARCH
    ========================================== */

    if (searchBox) {

        const searchInput =
            document.createElement("input");

        searchInput.type =
            "text";

        searchInput.placeholder =
            "Search anything..";

        searchInput.style.border =
            "none";

        searchInput.style.outline =
            "none";

        searchInput.style.background =
            "transparent";

        searchInput.style.width =
            "150px";


        searchBox.innerHTML = "";


        const searchIcon =
            document.createElement("i");

        searchIcon.className =
            "fa-solid fa-magnifying-glass";


        searchBox.appendChild(
            searchIcon
        );

        searchBox.appendChild(
            searchInput
        );


        searchInput.addEventListener(
            "input",
            function () {

                const searchValue =
                    searchInput.value
                        .toLowerCase()
                        .trim();


                const rows =
                    tableBody.querySelectorAll(
                        "tr"
                    );


                rows.forEach(
                    function (row) {

                        const rowText =
                            row.textContent
                                .toLowerCase();


                        row.style.display =
                            rowText.includes(
                                searchValue
                            )
                                ? ""
                                : "none";
                    }
                );
            }
        );
    }


    /* =========================================
       PAGINATION
    ========================================== */

    paginationButtons.forEach(
        function (button) {

            button.addEventListener(
                "click",
                function () {

                    paginationButtons.forEach(
                        function (btn) {

                            btn.classList.remove(
                                "active"
                            );
                        }
                    );


                    button.classList.add(
                        "active"
                    );

                }
            );
        }
    );


    /* =========================================
       START
    ========================================== */

    loadPayments();

});