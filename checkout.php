<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireCustomerLogin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$cart_items = $conn->prepare("SELECT c.*, p.name, p.price, p.stock, p.seller_id 
                              FROM cart c JOIN products p ON c.product_id = p.product_id 
                              WHERE c.user_id = ?");
$cart_items->bind_param("i", $user_id);
$cart_items->execute();
$cart_result = $cart_items->get_result();

if ($cart_result->num_rows == 0) {
    header("Location: cart.php");
    exit();
}

$total = 0;
$items = [];
while ($item = $cart_result->fetch_assoc()) {
    $items[] = $item;
    $total += $item['price'] * $item['quantity'];
}

$has_discount_cols = $conn->query("SHOW COLUMNS FROM orders LIKE 'discount_amount'")->num_rows > 0;
$has_wallet = $conn->query("SHOW TABLES LIKE 'user_wallet'")->num_rows > 0;
$wallet_balance = $has_wallet ? getWalletBalance($conn, $user_id) : 0;
$delivery_charge = 50;
$subtotal_with_delivery = $total + $delivery_charge;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = sanitize($_POST['address']);
    $payment_method = in_array($_POST['payment_method'] ?? '', ['COD', 'Card', 'Wallet']) ? $_POST['payment_method'] : 'COD';
    $discount_amount = 0;
    $discount_code = '';
    if ($has_discount_cols && !empty($_POST['discount_code_applied'])) {
        $discount_code = sanitize($_POST['discount_code_applied']);
        $discount_amount = floatval($_POST['discount_amount_applied'] ?? 0);
    }
    $order_total = max(0, $subtotal_with_delivery - $discount_amount);

    if (empty($address)) {
        $error = "Delivery address is required!";
    } elseif ($payment_method === 'Wallet' && (!$has_wallet || $wallet_balance < $order_total)) {
        $error = "Insufficient wallet balance. You have " . formatPrice($wallet_balance) . ". Add money in Wallet or choose another payment method.";
    } else {
        $conn->begin_transaction();
        try {
            if ($has_discount_cols) {
                $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, payment_method, discount_amount, discount_code) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("idssds", $user_id, $order_total, $address, $payment_method, $discount_amount, $discount_code);
            } else {
                $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, payment_method) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("idss", $user_id, $order_total, $address, $payment_method);
            }
            $stmt->execute();
            $order_id = $conn->insert_id;

            foreach ($items as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiid", $order_id, $item['product_id'], $item['seller_id'], $item['quantity'], $item['price']);
                $stmt->execute();
                $conn->query("UPDATE products SET stock = stock - {$item['quantity']} WHERE product_id = {$item['product_id']}");
                $conn->query("INSERT INTO purchase_history (user_id, product_id) VALUES ($user_id, {$item['product_id']})");
            }

            $stmt = $conn->prepare("INSERT INTO deliveries (order_id, status) VALUES (?, 'pending')");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();

            if ($payment_method === 'Wallet' && $has_wallet) {
                $conn->query("UPDATE user_wallet SET balance = balance - $order_total WHERE user_id = $user_id");
                $conn->query("INSERT INTO wallet_transactions (user_id, type, amount, order_id) VALUES ($user_id, 'order_payment', -$order_total, $order_id)");
            }

            if ($has_discount_cols && $discount_code !== '') {
                $conn->query("UPDATE discounts SET used_count = used_count + 1 WHERE code = '" . $conn->real_escape_string($discount_code) . "'");
            }

            $conn->query("DELETE FROM cart WHERE user_id = $user_id");
            $conn->commit();
            header("Location: orders.php?success=1");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Order placement failed! " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MarketPlace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-shopping-bag"></i> MarketPlace</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
            </ul>
        </div>
    </nav>

    <div class="container my-5">
        <h2><i class="fas fa-credit-card"></i> Checkout</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header"><h5>Delivery Address</h5></div>
                    <div class="card-body">
                        <form method="POST" id="checkoutForm">
                            <div class="mb-3">
                                <label>Full Address *</label>
                                <textarea name="address" class="form-control" rows="4" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label><strong>Payment Method</strong></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="COD" id="pm_cod" checked>
                                    <label class="form-check-label" for="pm_cod"><i class="fas fa-money-bill-wave"></i> Cash on Delivery</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="Card" id="pm_card">
                                    <label class="form-check-label" for="pm_card"><i class="fas fa-credit-card"></i> Card Payment</label>
                                </div>
                                <?php if ($has_wallet): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="Wallet" id="pm_wallet">
                                    <label class="form-check-label" for="pm_wallet"><i class="fas fa-wallet"></i> Wallet (Balance: <?php echo formatPrice($wallet_balance); ?>)</label>
                                </div>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="discount_code_applied" id="discount_code_applied" value="">
                            <input type="hidden" name="discount_amount_applied" id="discount_amount_applied" value="0">
                            <button type="submit" class="btn btn-primary btn-lg mt-3"><i class="fas fa-check-circle"></i> Place Order</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="cart-summary card">
                    <div class="card-body">
                        <h5>Order Summary</h5>
                        <hr>
                        <?php foreach ($items as $item): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <small><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</small>
                            <small><?php echo formatPrice($item['price'] * $item['quantity']); ?></small>
                        </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal:</span><strong><?php echo formatPrice($total); ?></strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Delivery:</span><strong>₹50.00</strong></div>
                        <?php if ($has_discount_cols): ?>
                        <div class="mb-2">
                            <label class="small">Discount / Redeem Code</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="couponCode" placeholder="Enter code">
                                <button type="button" class="btn btn-outline-secondary" id="applyCoupon">Apply</button>
                            </div>
                            <small id="couponMsg" class="text-muted"></small>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-success" id="discountRow" style="display:none!important;">
                            <span>Discount:</span><strong id="discountDisplay">-₹0</strong>
                        </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h5>Total:</h5>
                            <h5 class="text-primary" id="totalDisplay"><?php echo formatPrice($total + 50); ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function() {
        const subTotal = <?php echo $total; ?>;
        const delivery = 50;
        let discount = 0;
        const form = document.getElementById('checkoutForm');
        const totalDisplay = document.getElementById('totalDisplay');
        const discountRow = document.getElementById('discountRow');
        const discountDisplay = document.getElementById('discountDisplay');
        const couponMsg = document.getElementById('couponMsg');
        const applyBtn = document.getElementById('applyCoupon');
        if (applyBtn) {
            applyBtn.addEventListener('click', function() {
                const code = document.getElementById('couponCode').value.trim();
                if (!code) { couponMsg.textContent = 'Enter a code'; couponMsg.className = 'text-danger'; return; }
                const fd = new FormData();
                fd.append('code', code);
                fd.append('subtotal', subTotal + delivery);
                fetch('ajax/apply_coupon.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            discount = data.discount;
                            document.getElementById('discount_code_applied').value = data.code;
                            document.getElementById('discount_amount_applied').value = discount;
                            discountRow.style.display = 'flex';
                            discountDisplay.textContent = '-₹' + discount.toFixed(2);
                            totalDisplay.textContent = '₹' + (subTotal + delivery - discount).toFixed(2);
                            couponMsg.textContent = data.message || 'Coupon applied';
                            couponMsg.className = 'text-success';
                        } else {
                            discount = 0;
                            document.getElementById('discount_code_applied').value = '';
                            document.getElementById('discount_amount_applied').value = '0';
                            discountRow.style.display = 'none';
                            totalDisplay.textContent = '₹' + (subTotal + delivery).toFixed(2);
                            couponMsg.textContent = data.message || 'Invalid coupon';
                            couponMsg.className = 'text-danger';
                        }
                    });
            });
        }
    })();
    </script>
</body>
</html>
