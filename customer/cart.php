<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth-check.php';

checkCustomerAuth();

$customer_id = $_SESSION['customer_id'];
$cart_items = getCartItems($customer_id);
$total = getCartTotal($customer_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'remove') {
        $cart_id = (int)$_POST['cart_id'];
        $delete_query = "DELETE FROM carts WHERE id = ? AND customer_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("ii", $cart_id, $customer_id);
        $stmt->execute();
        redirect(CUSTOMER_URL . 'cart.php');
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
        $cart_id = (int)$_POST['cart_id'];
        $quantity = (int)$_POST['quantity'];
        
        if ($quantity > 0) {
            $update_query = "UPDATE carts SET jumlah = ? WHERE id = ? AND customer_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("iii", $quantity, $cart_id, $customer_id);
            $stmt->execute();
        }
        redirect(CUSTOMER_URL . 'cart.php');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Brew Bakery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .cart-layout {
            min-height: calc(100vh - 70px);
            background-color: var(--light);
            padding: 2rem 0;
        }
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .cart-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }
        .cart-items {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }
        .cart-items h1 {
            margin-bottom: 1.5rem;
        }
        .cart-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .item-image {
            width: 100px;
            height: 100px;
            background-color: var(--accent);
            border-radius: 0.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .item-info {
            flex: 1;
            min-width: 200px;
        }
        .item-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .item-price {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .qty-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        .qty-input {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 0.3rem;
            background: white;
        }
        .qty-input button {
            padding: 0.4rem 0.6rem;
            border: none;
            background: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1rem;
            transition: background-color 0.2s;
        }
        .qty-input button:hover {
            background-color: var(--light);
        }
        .qty-input input {
            width: 50px;
            border: none;
            text-align: center;
            padding: 0.4rem;
            font-weight: 600;
        }
        .qty-input input:focus {
            outline: none;
        }
        .item-total {
            font-weight: 600;
            min-width: 120px;
            text-align: right;
            color: var(--primary);
            font-size: 1.1rem;
        }
        .btn-remove {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 0.4rem 0.75rem;
            border-radius: 0.3rem;
            cursor: pointer;
            font-size: 0.85rem;
            transition: background-color 0.3s;
        }
        .btn-remove:hover {
            background-color: #c82333;
        }
        .cart-summary {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .summary-row.total {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--primary);
            border-top: 2px solid var(--primary);
            padding-top: 1rem;
            margin-top: 1rem;
        }
        .btn-checkout {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 0.3rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: background-color 0.3s;
        }
        .btn-checkout:hover {
            background-color: var(--dark);
        }
        .empty-cart {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .empty-cart p {
            color: #666;
            margin-bottom: 1rem;
        }
        .btn-continue {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 0.3rem;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .cart-grid {
                grid-template-columns: 1fr;
            }
            .cart-summary {
                position: static;
            }
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .item-total {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="cart-layout">
        <div class="cart-container">
            <a href="<?php echo CUSTOMER_URL; ?>shop.php" style="color: var(--primary); text-decoration: none; margin-bottom: 1rem; display: inline-block;">← Lanjut Belanja</a>
            
            <?php if (count($cart_items) > 0): ?>
            <div class="cart-grid">
                <div class="cart-items">
                    <h1>🛒 Keranjang Belanja (<?php echo count($cart_items); ?> item)</h1>
                    
                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="item-image">
                            <?php if (!empty($item['foto_utama'])): ?>
                                <img src="<?php echo PRODUCT_IMG_URL . htmlspecialchars($item['foto_utama']); ?>" alt="<?php echo htmlspecialchars($item['nama']); ?>">
                                <?php else: ?>
                                <span style="font-size: 2rem;">🍞</span>
                            <?php endif; ?>
                        </div>
                        <div class="item-info">
                            <div class="item-name"><?php echo htmlspecialchars($item['nama']); ?></div>
                            <!-- PERBAIKAN: Tampilkan harga dengan diskon -->
                            <div class="item-price">
                                <?php 
                                $priceInfo = formatPriceWithDiscount($item['harga'], $item['diskon_tipe'], $item['diskon_nilai'], $item['diskon_aktif']);
                                if ($priceInfo['savings'] > 0): 
                                ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;">
                                        <span style="text-decoration: line-through; color: #999;">
                                            <?php echo formatCurrency($priceInfo['original']); ?>
                                        </span>
                                        <span style="color: #28a745; font-weight: 900;">
                                            <?php echo formatCurrency($priceInfo['discounted']); ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <?php echo formatCurrency($priceInfo['original']); ?> per item
                                <?php endif; ?>
                            </div>
                            <div class="qty-controls">
                                <form method="POST" id="form-<?php echo $item['id']; ?>" style="display: contents;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <div class="qty-input">
                                        <button type="button" onclick="decrementQty(<?php echo $item['id']; ?>)">−</button>
                                        <input type="number" id="qty-<?php echo $item['id']; ?>" name="quantity" value="<?php echo $item['jumlah']; ?>" min="1" max="100">
                                        <button type="button" onclick="incrementQty(<?php echo $item['id']; ?>)">+</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- PERBAIKAN: Total item dengan diskon -->
                        <div class="item-total">
                            <?php 
                            $priceInfo = formatPriceWithDiscount($item['harga'], $item['diskon_tipe'], $item['diskon_nilai'], $item['diskon_aktif']);
                            echo formatCurrency($priceInfo['discounted'] * $item['jumlah']);
                            ?>
                        </div>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn-remove">🗑️ Hapus</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3>Ringkasan Pesanan</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span><?php echo formatCurrency($total); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Pajak</span>
                        <span><?php echo formatCurrency(0); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span><?php echo formatCurrency($total); ?></span>
                    </div>
                    
                    <a href="<?php echo CUSTOMER_URL; ?>checkout.php" class="btn-checkout">💳 Lanjut ke Checkout</a>
                    
                </div>
            </div>
            <?php else: ?>
            <div class="empty-cart">
                <p style="font-size: 3rem; margin-bottom: 1rem;">🛒</p>
                <h2>Keranjang Anda kosong</h2>
                <p>Belum ada produk yang ditambahkan ke keranjang</p>
                <a href="<?php echo CUSTOMER_URL; ?>shop.php" class="btn-continue">🛍️ Mulai Belanja Sekarang</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function incrementQty(cartId) {
            const input = document.getElementById('qty-' + cartId);
            input.value = parseInt(input.value) + 1;
            updateCart(cartId);
        }
        
        function decrementQty(cartId) {
            const input = document.getElementById('qty-' + cartId);
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateCart(cartId);
            }
        }
        
        function updateCart(cartId) {
            const form = document.getElementById('form-' + cartId);
            form.submit();
        }
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
