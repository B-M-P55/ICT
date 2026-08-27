<?php $activePage = $activePage ?? ''; ?>
<aside class="admin-side">
    <a class="brand" href="admin_dashboard.php"><img src="../img/logo.png" alt="H2O2U"><span>H2O2U</span></a>
    <nav class="side-links">
        <p class="side-label">Overview</p>
        <a class="side-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="admin_dashboard.php"><i class="fa-solid fa-grid-2"></i>Dashboard</a>
        <p class="side-label">Catalogue & stock</p>
        <a class="side-link <?= $activePage === 'products' ? 'active' : '' ?>" href="admin_products.php"><i class="fa-solid fa-bottle-water"></i>Products</a>
        <a class="side-link <?= $activePage === 'stock' ? 'active' : '' ?>" href="admin_stock.php"><i class="fa-solid fa-boxes-stacked"></i>Stock management</a>
        <p class="side-label">Operations</p>

        <a class="side-link" href="admin_order.html"><i class="fa-solid fa-clipboard-list"></i>Orders</a>
        
        <a class="side-link <?= $activePage === 'payments' ? 'active' : '' ?>" href="admin_payments.php"><i class="fa-solid fa-wallet"></i>Payments</a>


        <a class="side-link" href="admin_delivery.php"><i class="fa-solid fa-truck-fast"></i>Deliveries</a>

       


    </nav>
    <div class="side-bottom"><a class="side-link" href="admin_logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i>Log out</a></div>
</aside>
