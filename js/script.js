document.addEventListener("DOMContentLoaded", function () {


    /* =========================================
       PRODUCT DATA
    ========================================== */

    const products = [

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

        productRow.innerHTML = products.map(function (prod) {

            return `

                <div class="col-12 col-md-6">

                    <div class="product-card">


                        <!-- NAVY TOP -->

                        <div class="product-top-banner">

                        </div>



                        <!-- PRODUCT IMAGE -->

                        <div class="product-img-wrapper">

                            <img
                                src="${prod.img}"
                                alt="${prod.name}"
                            >

                        </div>



                        <!-- PRODUCT BODY -->

                        <div class="product-body">


                            <h4>
                                ${prod.name}
                            </h4>


                            <p>

                                dui, vehicula, elit tincidunt ipsum
                                eget in sit id id non tempor tincidunt
                                sit sed diam tortor. faucibus Nam
                                ipsum urna Ut

                            </p>



                            <!-- PRODUCT FOOTER -->

                            <div class="product-footer">


                                <span class="product-price">

                                    <span class="arrow-icon">
                                        ▶
                                    </span>

                                    ${prod.price}

                                </span>



                                <button
                                    type="button"
                                    class="btn-buy"
                                >

                                    Order now

                                </button>


                            </div>

                        </div>

                    </div>

                </div>

            `;

        }).join("");



        /* =========================================
           PRODUCT ORDER BUTTONS
        ========================================== */

        const productButtons =
            document.querySelectorAll(".btn-buy");


        productButtons.forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    window.location.href =
                        "user_orders.html";

                }
            );

        });

    }



    /* =========================================
       NAVBAR ORDER BUTTON
    ========================================== */

    const navOrderButton =
        document.getElementById("navOrderButton");


    if (navOrderButton) {

        navOrderButton.addEventListener(
            "click",
            function () {

                window.location.href =
                    "user_orders.html";

            }
        );

    }



    /* =========================================
       HERO ORDER BUTTON
    ========================================== */

    const heroOrderButton =
        document.getElementById(
            "heroOrderButton"
        );


    if (heroOrderButton) {

        heroOrderButton.addEventListener(
            "click",
            function () {

                window.location.href =
                    "user_orders.html";

            }
        );

    }

});