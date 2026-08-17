document.addEventListener("DOMContentLoaded", function () {
    const products = [
        { name: "Bottled Water", price: "5000 Ks.", img: "images/two-btl.jpg" },
        { name: "Bottled Water", price: "5000 Ks.", img: "images/two-btl.jpg" },
        { name: "Bottled Water", price: "5000 Ks.", img: "images/two-btl.jpg" },
        { name: "Bottled Water", price: "5000 Ks.", img: "images/two-btl.jpg" }
    ];

    const productRow = document.getElementById("productRow");

    if (productRow) {
        productRow.innerHTML = products.map(prod => `
            <div class="col-12 col-md-6 mb-5">
                <div class="product-card">
                    <!-- Pointed Navy Top Banner -->
                    <div class="product-top-banner"></div>
                    
                    <!-- Circular Image Wrapper -->
                    <div class="product-img-wrapper">
                        <img src="${prod.img}" alt="${prod.name}">
                    </div>

                    <!-- Card Body -->
                    <div class="product-body">
                        <h4>${prod.name}</h4>
                        <p>
                            dui, vehicula, elit tincidunt ipsum eget in sit id
                            id non tempor tincidunt sit sed diam tortor.
                            faucibus Nam ipsum urna Ut
                        </p>
                        
                        <!-- Card Footer -->
                        <div class="product-footer">
                            <span class="product-price">
                                <span class="arrow-icon">▶</span> ${prod.price}
                            </span>
                            <button class="btn-buy">Order now</button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    // Scroll action handlers for Order Buttons
    const orderButtons = ["orderButton", "heroOrderButton", "contactOrderButton"];
    orderButtons.forEach(id => {
        const btn = document.getElementById(id);
        if (btn) {
            btn.addEventListener("click", () => {
                const target = document.getElementById("products");
                if (target) {
                    target.scrollIntoView({ behavior: "smooth" });
                }
            });
        }
    });
});