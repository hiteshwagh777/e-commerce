<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireCustomerLogin();

$user_id = $_SESSION['user_id'];
$has_discount_col = $conn->query("SHOW COLUMNS FROM orders LIKE 'discount_amount'")->num_rows > 0;
$orders = $conn->query("SELECT order_id, total_amount, payment_method, order_date" . ($has_discount_col ? ", discount_amount, discount_code" : "") . " FROM orders WHERE user_id = $user_id ORDER BY order_date DESC");
$total_savings = 0;
if ($has_discount_col) {
    $r = $conn->query("SELECT COALESCE(SUM(discount_amount), 0) as total FROM orders WHERE user_id = $user_id AND discount_amount > 0");
    if ($r) $total_savings = $r->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - MarketPlace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-shopping-bag"></i> MarketPlace</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="wallet.php">Wallet</a></li>
                <li class="nav-item"><a class="nav-link active" href="payment_history.php">Payment History</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container my-5">
        <h2><i class="fas fa-history"></i> Payment History</h2>
        <div class="card mt-3 mb-4">
            <div class="card-body py-3">
                <strong>Total Savings from discounts:</strong> <span class="text-success"><?php echo formatPrice($total_savings); ?></span>
            </div>
        </div>
        <div class="card">
            <div class="card-header">All Payments (Orders)</div>
            <div class="card-body">
                <?php if ($orders->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Date</th><th>Order ID</th><th>Payment Method</th><th>Amount</th><th>Discount</th></tr>
                        </thead>
                        <tbody>
                            <?php while ($o = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d M Y H:i', strtotime($o['order_date'])); ?></td>
                                <td><a href="orders.php">#<?php echo $o['order_id']; ?></a></td>
                                <td><?php echo htmlspecialchars($o['payment_method'] ?? 'COD'); ?></td>
                                <td><?php echo formatPrice($o['total_amount']); ?></td>
                                <td class="text-success"><?php echo ($has_discount_col && isset($o['discount_amount']) && $o['discount_amount'] > 0) ? '-' . formatPrice($o['discount_amount']) . ' (' . htmlspecialchars($o['discount_code'] ?? '') . ')' : '-'; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted mb-0">No payment history yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
