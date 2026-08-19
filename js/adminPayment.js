document.addEventListener("DOMContentLoaded", function () {

    const tableRows = document.querySelectorAll(".admin-table tbody tr");
    const searchBox = document.querySelector(".search-box");
    const paginationButtons = document.querySelectorAll(".pagination button");


     //  SEARCH PAYMENT RECORDS

    if (searchBox) {

        const searchInput = document.createElement("input");

        searchInput.type = "text";
        searchInput.placeholder = "Search anything..";

        searchInput.style.border = "none";
        searchInput.style.outline = "none";
        searchInput.style.background = "transparent";
        searchInput.style.width = "150px";

        searchBox.innerHTML = "";

        const searchIcon = document.createElement("i");

        searchIcon.className = "fa-solid fa-magnifying-glass";

        searchBox.appendChild(searchIcon);
        searchBox.appendChild(searchInput);


        searchInput.addEventListener("keyup", function () {

            const searchValue =
                searchInput.value.toLowerCase().trim();

            tableRows.forEach(function (row) {

                const rowText =
                    row.textContent.toLowerCase();

                if (rowText.includes(searchValue)) {

                    row.style.display = "";

                } else {

                    row.style.display = "none";

                }

            });

        });

    }


     //  EDIT PAYMENT STATUS

    const editButtons =
        document.querySelectorAll(".edit-btn");


    editButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const row = button.closest("tr");

            if (!row) {
                return;
            }


            const statusElement =
                row.querySelector(".status");


            if (!statusElement) {
                return;
            }


            const currentStatus =
                statusElement.textContent.trim();


            //  Payment Status

            if (
                currentStatus === "Paid" ||
                currentStatus === "Unpaid" ||
                currentStatus === "Pending"
            ) {

                const newStatus =
                    prompt(
                        "Enter payment status:\n\nPaid\nUnpaid\nPending",
                        currentStatus
                    );


                if (!newStatus) {
                    return;
                }


                const status =
                    newStatus.toLowerCase();


                if (
                    status !== "paid" &&
                    status !== "unpaid" &&
                    status !== "pending"
                ) {

                    alert(
                        "Please enter Paid, Unpaid, or Pending."
                    );

                    return;
                }


                updatePaymentStatus(
                    statusElement,
                    status
                );

            }

        });

    });



    //  UPDATE PAYMENT STATUS
   

    function updatePaymentStatus(
        statusElement,
        status
    ) {

        statusElement.classList.remove(
            "paid",
            "unpaid",
            "pending"
        );


        let icon = "";


        if (status === "paid") {

            statusElement.classList.add("paid");

            icon =
                '<i class="fa-solid fa-circle-check"></i>';

            statusElement.innerHTML =
                icon + " Paid";

        }


        else if (status === "unpaid") {

            statusElement.classList.add("unpaid");

            icon =
                '<i class="fa-solid fa-circle-xmark"></i>';

            statusElement.innerHTML =
                icon + " Unpaid";

        }


        else {

            statusElement.classList.add("pending");

            icon =
                '<i class="fa-solid fa-clock"></i>';

            statusElement.innerHTML =
                icon + " Pending";

        }


        updateSummary();

    }


      // UPDATE SUMMARY CARDS

    function updateSummary() {

        const rows =
            document.querySelectorAll(
                ".admin-table tbody tr"
            );


        let paidCount = 0;
        let pendingCount = 0;


        rows.forEach(function (row) {

            const status =
                row.querySelector(".status");


            if (!status) {
                return;
            }


            if (
                status.classList.contains("paid")
            ) {

                paidCount++;

            }


            if (
                status.classList.contains("pending")
            ) {

                pendingCount++;

            }

        });


        const summaryCards =
            document.querySelectorAll(
                ".summary-card h2"
            );


        if (summaryCards.length >= 4) {

            summaryCards[2].textContent =
                paidCount;

            summaryCards[3].textContent =
                pendingCount;

        }

    }


      // PAGINATION BUTTONS

    paginationButtons.forEach(function (button) {

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


                button.classList.add("active");

            }
        );

    });



//   View Payment Proof

    editButtons.forEach(function (button) {

        if (
            button.textContent.includes("View File")
        ) {

            button.addEventListener(
                "click",
                function () {

                    alert(
                        "Payment proof will be displayed here."
                    );

                }
            );

        }

    });

    updateSummary();

});