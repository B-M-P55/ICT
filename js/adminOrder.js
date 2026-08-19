document.addEventListener("DOMContentLoaded", function () {

    const table = document.querySelector(".admin-table");
    const tableBody = table.querySelector("tbody");
    const rows = Array.from(tableBody.querySelectorAll("tr"));

    const searchInput = document.querySelector(".filter-input");
    const statusSelect = document.querySelector(".status-select");

    const editButtons = document.querySelectorAll(".edit-btn");

    const paginationButtons =
        document.querySelectorAll(".pagination button");


     //  SEARCH AND FILTER


    function filterOrders() {

        const searchValue =
            searchInput.value.toLowerCase().trim();

        const selectedStatus =
            statusSelect.value.toLowerCase();

        rows.forEach(function (row) {

            const orderNumber =
                row.cells[0].textContent.toLowerCase();

            const customer =
                row.cells[1].textContent.toLowerCase();

            const rowText =
                row.textContent.toLowerCase();

            let matchesSearch =
                orderNumber.includes(searchValue) ||
                customer.includes(searchValue) ||
                rowText.includes(searchValue);

            let matchesStatus = true;


            //Filtering will be ready when status is added.

            if (selectedStatus !== "all status") {

                const status =
                    row.dataset.status || "";

                matchesStatus =
                    status.toLowerCase() === selectedStatus;

            }


            if (matchesSearch && matchesStatus) {

                row.style.display = "";

            } else {

                row.style.display = "none";

            }

        });

        updateShowingCount();

    }


    if (searchInput) {

        searchInput.addEventListener(
            "input",
            filterOrders
        );

    }


    if (statusSelect) {

        statusSelect.addEventListener(
            "change",
            filterOrders
        );

    }



      // EDIT ORDER
   

    editButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const row =
                button.closest("tr");

            if (!row) {
                return;
            }


            const orderNo =
                row.cells[0].textContent.trim();

            const customer =
                row.cells[1].textContent.trim();

            const item =
                row.cells[3].textContent
                .replace(/\s+/g, " ")
                .trim();

            const price =
                row.cells[4].textContent.trim();

            const quantity =
                row.cells[5].textContent.trim();


            const newQuantity =
                prompt(
                    "Edit quantity for " +
                    orderNo +
                    " - " +
                    customer +
                    "\n\nCurrent quantity: " +
                    quantity,
                    quantity
                );


            if (newQuantity === null) {
                return;
            }


            if (
                newQuantity.trim() === "" ||
                isNaN(newQuantity) ||
                Number(newQuantity) <= 0
            ) {

                alert(
                    "Please enter a valid quantity."
                );

                return;

            }


            const quantityNumber =
                Number(newQuantity);

             // Get price number

            const priceNumber =
                parseInt(
                    price.replace(/[^0-9]/g, "")
                );


            const total =
                priceNumber * quantityNumber;


            // Update quantity

            row.cells[5].textContent =
                quantityNumber;


            // Update total
    

            row.cells[6].textContent =
                total.toLocaleString() + " Ks";


            // Update summary

            updateSummary();


            alert(
                "Order " +
                orderNo +
                " has been updated."
            );

        });

    });


     //  UPDATE SUMMARY
    

    function updateSummary() {

        const visibleRows =
            rows.filter(function (row) {

                return row.style.display !== "none";

            });


        let totalOrders =
            visibleRows.length;

        let totalSales = 0;


        visibleRows.forEach(function (row) {

            const totalText =
                row.cells[6].textContent;

            const total =
                parseInt(
                    totalText.replace(/[^0-9]/g, "")
                );


            if (!isNaN(total)) {

                totalSales += total;

            }

        });


        const summaryCards =
            document.querySelectorAll(
                ".order-summary .summary-card"
            );


        /*
          Only update the visible/filter result
          count for the first two cards.
         */

        if (summaryCards.length >= 2) {

            /*
             * Keep the original total if
             * nothing is being searched.
             */

            if (
                searchInput.value.trim() === "" &&
                statusSelect.value === "All Status"
            ) {

                summaryCards[0]
                    .querySelector("h2")
                    .textContent = "1,254";

                summaryCards[1]
                    .querySelector("h2")
                    .textContent =
                    "Ks 247,223";

            } else {

                summaryCards[0]
                    .querySelector("h2")
                    .textContent =
                    totalOrders;

                summaryCards[1]
                    .querySelector("h2")
                    .textContent =
                    "Ks " +
                    totalSales.toLocaleString();

            }

        }

    }


     //  PAGINATION
    

    paginationButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                /*
                 * Ignore the next button for now.
                 */

                if (
                    button.querySelector(
                        ".fa-chevron-right"
                    )
                ) {

                    alert(
                        "Next page"
                    );

                    return;

                }


                paginationButtons.forEach(
                    function (btn) {

                        btn.classList.remove(
                            "active"
                        );

                    }
                );


                button.classList.add("active");


                const page =
                    button.textContent.trim();


                if (page) {

                    console.log(
                        "Selected page:",
                        page
                    );

                }

            }
        );

    });

     
    
    //  SHOWING ENTRIES

    fu
    nction updateShowingCount() {

        const tableFooter =
            document.querySelector(
                ".table-footer span"
            );


        if (!tableFooter) {
            return;
        }


        const visibleRows =
            rows.filter(function (row) {

                return row.style.display !== "none";

            });


        if (
            searchInput.value.trim() !== "" ||
            statusSelect.value !== "All Status"
        ) {

            tableFooter.textContent =
                "Showing " +
                visibleRows.length +
                " of " +
                rows.length +
                " entries";

        } else {

            tableFooter.textContent =
                "Showing 4 of 12 entries";

        }

    }


    updateSummary();
    updateShowingCount();

});