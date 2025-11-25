<style>
    .admin-sidebar {
        width: 250px;
        background-color: var(--dark);
        color: white;
        padding: 2rem 0;
        min-height: calc(100vh - 70px);
    }
    .sidebar-menu {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .sidebar-menu li {
        margin: 0;
    }
    .sidebar-menu a {
        display: block;
        padding: 1rem 1.5rem;
        color: white;
        text-decoration: none;
        transition: all 0.3s;
        border-left: 4px solid transparent;
    }
    .sidebar-menu a:hover,
    .sidebar-menu a.active {
        background-color: var(--primary);
        border-left-color: var(--secondary);
    }
    .sidebar-divider {
        height: 1px;
        background-color: rgba(255, 255, 255, 0.1);
        margin: 1rem 0;
    }
</style>

<div class="admin-sidebar">
    <ul class="sidebar-menu">
        <li><a href="<?php echo ADMIN_URL; ?>dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
        
        <div class="sidebar-divider"></div>
        
        <li><a href="<?php echo ADMIN_URL; ?>products/" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/products/') !== false ? 'active' : ''; ?>">🍞 Produk</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>categories/" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/categories/') !== false ? 'active' : ''; ?>">📂 Kategori</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>articles/" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/articles/') !== false ? 'active' : ''; ?>">📰 Artikel</a></li>
        
        <div class="sidebar-divider"></div>
        
        <li><a href="<?php echo ADMIN_URL; ?>orders/" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/orders/') !== false ? 'active' : ''; ?>">📦 Pesanan</a></li>
        <li><a href="<?php echo ADMIN_URL; ?>shipping/" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/shipping/') !== false ? 'active' : ''; ?>">🚚 Ongkir</a></li>
        
        <div class="sidebar-divider"></div>
        
        <li><a href="<?php echo ADMIN_URL; ?>reports/" class="<?php echo strpos($_SERVER['REQUEST_URI'], '/reports/') !== false ? 'active' : ''; ?>">📊 Laporan</a></li>
        
        <div class="sidebar-divider"></div>
        
        <li><a href="<?php echo AUTH_URL; ?>logout.php">🚪 Logout</a></li>
    </ul>
</div>
