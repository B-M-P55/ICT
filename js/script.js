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
       PRODUCT ROW
    ========================================== */

    const productRow =
        document.getElementById("productRow");


    if (productRow) {

        productRow.innerHTML = products.map(product => `

            <div class="product-column">


                <!-- PRODUCT CARD -->

                <div class="product-card">


                    <!-- =================================
                         NAVY TOP
                    ================================== -->

                    <div class="product-top-banner">
                    </div>


                    <!-- =================================
                         CIRCULAR PRODUCT IMAGE
                    ================================== -->

                    <div class="product-img-wrapper">

                        <img
                            src="${product.img}"
                            alt="${product.name}"
                        >

                    </div>


                    <!-- =================================
                         PRODUCT INFORMATION
                    ================================== -->

                    <div class="product-body">


                        <h4>
                            ${product.name}
                        </h4>


                        <p>

                            dui, vehicula, elit tincidunt ipsum eget in sit id
                            id non tempor tincidunt sit sed diam tortor.
                            faucibus Nam ipsum urna Ut

                        </p>


                        <!-- =================================
                             PRICE + ORDER
                        ================================== -->

                        <div class="product-footer">


                            <span class="product-price">

                                <span class="arrow-icon">
                                    ▶
                                </span>

                                ${product.price}

                            </span>


                            <button
                                type="button"
                                class="btn-buy"
                                onclick="orderProduct('${product.name}')">

                                Order now

                            </button>


                        </div>


                    </div>

                </div>

            </div>

        `).join("");

    }


    /* =========================================
       ORDER BUTTONS
    ========================================== */

    const orderButtons = [
        "orderButton",
        "heroOrderButton",
        "contactOrderButton"
    ];


    orderButtons.forEach(id => {

        const btn =
            document.getElementById(id);


        if (btn) {

            btn.addEventListener("click", function () {

                const target =
                    document.getElementById("products");


                if (target) {

                    target.scrollIntoView({
                        behavior: "smooth"
                    });

                }

            });

        }

    });


});


/* =========================================
   PRODUCT ORDER FUNCTION
========================================== */

function orderProduct(productName) {

    alert(
        productName + " has been selected."
    );

}