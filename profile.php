<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireCustomerLogin();

$user_id = $_SESSION['user_id'];
$message = '';
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $name, $phone, $address, $user_id);
    if ($stmt->execute()) {
        $_SESSION['user_name'] = $name;
        $message = '<div class="alert alert-success">Profile updated successfully.</div>';
        $user['name'] = $name;
        $user['phone'] = $phone;
        $user['address'] = $address;
    } else {
        $message = '<div class="alert alert-danger">Update failed.</div>';
    }
}

$total_savings = 0;
if ($conn->query("SHOW COLUMNS FROM orders LIKE 'discount_amount'")->num_rows > 0) {
    $r = $conn->query("SELECT COALESCE(SUM(discount_amount), 0) as total FROM orders WHERE user_id = $user_id AND discount_amount > 0");
    if ($r) $total_savings = $r->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MarketPlace</title>
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
                <li class="nav-item"><a class="nav-link" href="wallet.php">Wallet</a></li>
                <li class="nav-item"><a class="nav-link" href="payment_history.php">Payment History</a></li>
                <li class="nav-item"><a class="nav-link active" href="profile.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container my-5">
        <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
        <?php echo $message; ?>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Profile Details</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Full Name *</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small class="text-muted">Email cannot be changed.</small>
                            </div>
                            <div class="mb-3">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label>Address</label>
                                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Total Savings</div>
                    <div class="card-body text-center">
                        <h4 class="text-success"><?php echo formatPrice($total_savings); ?></h4>
                        <p class="text-muted small">Total saved from discounts & coupon codes on your orders.</p>
                        <a href="orders.php" class="btn btn-outline-primary btn-sm">View Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
