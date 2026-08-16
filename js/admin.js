const searchInput = document.querySelector(".filter-input");

if (searchInput) {

    searchInput.addEventListener("keyup", function () {

        const searchValue =
            this.value.toLowerCase();

        const rows =
            document.querySelectorAll(".admin-table tbody tr");

        rows.forEach(function(row) {

            const text =
                row.textContent.toLowerCase();

            if (text.includes(searchValue)) {

                row.style.display = "";

            } else {

                row.style.display = "none";

            }

        });

    });

}