document.addEventListener("DOMContentLoaded", function () {


    var productRow = document.getElementById("productRow");

    /* =========================================
       PRODUCT DATA
    ========================================== */

    const products = [

        {
            name: "Bottled Water",
            price: "1000 Ks.",
            img: "img/bottle.jpg"
        },

        {
            name: "Bottled Water",
            price: "1000 Ks.",
            img: "images/two-btl.jpg"
        },

        {
            name: "Bottled Water",
            price: "1000 Ks.",
            img: "images/two-btl.jpg"
        },

        {
            name: "Bottled Water",
            price: "1000 Ks.",
            img: "images/two-btl.jpg"
        }

    ];



    /* =========================================
       PRODUCT CONTAINER
    ========================================== */

    const productRow =
        document.getElementById("productRow");



    /* =========================================
       CREATE PRODUCTS
    ========================================== */


    if (productRow) {

        fetch("php/get_products.php")
            .then(function (response) {
                return response.json();
            })
            .then(function (products) {

                productRow.innerHTML = products.map(function (prod) {

                    return '' +
                        '<div class="col-12 col-md-6">' +
                            '<div class="product-card">' +
                                '<div class="product-top-banner"></div>' +
                                '<div class="product-img-wrapper">' +
                                    '<img src="' + prod.image_path + '" alt="' + prod.product_name + '">' +
                                '</div>' +
                                '<div class="product-body">' +
                                    '<h4>' + prod.product_name + ' (' + prod.size + ')</h4>' +
                                    '<p>Fresh and clean drinking water delivered directly to your door.</p>' +
                                    '<div class="product-footer">' +
                                        '<span class="product-price">' +
                                            '<span class="arrow-icon">▶</span> ' +
                                            prod.price + ' Ks.' +
                                        '</span>' +
                                        '<button type="button" class="btn-buy" data-id="' + prod.productID + '">Order now</button>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>';

                }).join("");

                var productButtons = document.querySelectorAll(".btn-buy");
                productButtons.forEach(function (button) {
                    button.addEventListener("click", function () {
                        window.location.href = "user_orders.html";
                    });
                });

            })
            .catch(function (error) {
                console.error("Failed to load products:", error);
            });

    }

    var navOrderButton = document.getElementById("navOrderButton");
    if (navOrderButton) {
        navOrderButton.addEventListener("click", function () {
            window.location.href = "user_orders.html";
        });
    }

    var heroOrderButton = document.getElementById("heroOrderButton");
    if (heroOrderButton) {
        heroOrderButton.addEventListener("click", function () {
            window.location.href = "user_orders.html";
        });
    }

});
