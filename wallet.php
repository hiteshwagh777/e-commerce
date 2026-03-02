<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireCustomerLogin();

$user_id = $_SESSION['user_id'];
$has_wallet = $conn->query("SHOW TABLES LIKE 'user_wallet'")->num_rows > 0;
$balance = 0;
$transactions = [];
$message = '';

if ($has_wallet) {
    $r = $conn->query("SELECT balance FROM user_wallet WHERE user_id = $user_id");
    if ($r && $row = $r->fetch_assoc()) $balance = (float)$row['balance'];
    else {
        $conn->query("INSERT IGNORE INTO user_wallet (user_id, balance) VALUES ($user_id, 0)");
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_money'])) {
        $amount = floatval($_POST['amount'] ?? 0);
        if ($amount >= 10 && $amount <= 50000) {
            $conn->query("INSERT INTO user_wallet (user_id, balance) VALUES ($user_id, 0) ON DUPLICATE KEY UPDATE user_id=user_id");
            $conn->query("UPDATE user_wallet SET balance = balance + $amount WHERE user_id = $user_id");
            $conn->query("INSERT INTO wallet_transactions (user_id, type, amount) VALUES ($user_id, 'add_money', $amount)");
            $balance += $amount;
            $message = '<div class="alert alert-success">₹' . number_format($amount, 2) . ' added to wallet (simulated).</div>';
        } else {
            $message = '<div class="alert alert-danger">Amount must be between ₹10 and ₹50,000.</div>';
        }
    }
    $transactions = $conn->query("SELECT * FROM wallet_transactions WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 50");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet - MarketPlace</title>
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
                <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="payment_history.php">Payment History</a></li>
                <li class="nav-item"><a class="nav-link active" href="wallet.php">Wallet</a></li>
                <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container my-5">
        <h2><i class="fas fa-wallet"></i> Wallet Management</h2>
        <?php echo $message; ?>
        <?php if (!$has_wallet): ?>
            <div class="alert alert-warning">Wallet is not set up. Run database_customer_addon.sql.</div>
        <?php else: ?>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5>Current Balance</h5>
                            <h2><?php echo formatPrice($balance); ?></h2>
                            <button class="btn btn-light btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addMoneyModal"><i class="fas fa-plus"></i> Add Money</button>
                        </div>
                    </div>
                    <div class="modal fade" id="addMoneyModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header"><h5 class="modal-title">Add Money to Wallet</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="add_money" value="1">
                                        <div class="mb-3"><label>Amount (₹)</label><input type="number" name="amount" class="form-control" min="10" max="50000" step="1" value="500" required></div>
                                        <p class="small text-muted">Demo: This simulates adding money. Min ₹10, Max ₹50,000.</p>
                                    </div>
                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Recent Transactions</div>
                        <div class="card-body">
                            <?php if ($transactions && $transactions->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Order</th></tr></thead>
                                    <tbody>
                                        <?php while ($t = $transactions->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('d M Y H:i', strtotime($t['created_at'])); ?></td>
                                            <td><?php echo ucfirst(str_replace('_', ' ', $t['type'])); ?></td>
                                            <td class="<?php echo $t['amount'] >= 0 ? 'text-success' : 'text-danger'; ?>"><?php echo ($t['amount'] >= 0 ? '+' : '') . formatPrice($t['amount']); ?></td>
                                            <td><?php echo $t['order_id'] ? '#' . $t['order_id'] : '-'; ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-muted mb-0">No transactions yet. Add money to get started.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
