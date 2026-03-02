<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireCustomerLogin();

$user_id = $_SESSION['user_id'];
$success_msg = isset($_GET['success']) ? "Order placed successfully!" : '';

// Get all orders
$orders = $conn->query("SELECT o.*, d.status as delivery_status 
                       FROM orders o 
                       LEFT JOIN deliveries d ON o.order_id = d.order_id 
                       WHERE o.user_id = $user_id 
                       ORDER BY o.order_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - MarketPlace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-shopping-bag"></i> MarketPlace</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                <li class="nav-item"><a class="nav-link active" href="orders.php"><i class="fas fa-box"></i> Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container my-5">
        <h2><i class="fas fa-box"></i> My Orders</h2>
        
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if ($orders->num_rows > 0): ?>
            <?php while ($order = $orders->fetch_assoc()): 
                // Get order items
                $items = $conn->query("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = {$order['order_id']}");
            ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Order #<?php echo $order['order_id']; ?></strong>
                        <span class="ms-3">Placed on <?php echo date('d M Y', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <div>
                            <span class="badge status-<?php echo $order['status']; ?>"><?php echo orderStatusLabel($order['status']); ?></span>
                            <?php if ($order['delivery_status']): ?>
                            <span class="badge status-<?php echo $order['delivery_status']; ?>">Delivery: <?php echo $order['delivery_status'] ? ucfirst(str_replace('_', ' ', $order['delivery_status'])) : 'N/A'; ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                        <button type="button" class="btn btn-sm btn-danger cancel-order-btn" data-order-id="<?php echo $order['order_id']; ?>">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php while ($item = $items->fetch_assoc()): ?>
                    <div class="row mb-2 align-items-center">
                        <div class="col-md-2">
                            <img src="uploads/products/<?php echo $item['image'] ?: 'default.jpg'; ?>" class="img-fluid" style="max-height: 80px;">
                        </div>
                        <div class="col-md-6">
                            <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                            <p class="text-muted small">Quantity: <?php echo $item['quantity']; ?></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>Delivery Address:</strong><br>
                            <?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?>
                        </div>
                        <div class="text-end">
                            <h5>Total: <?php echo formatPrice($order['total_amount']); ?></h5>
                            <?php if (!empty($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                            <small class="text-success">You saved <?php echo formatPrice($order['discount_amount']); ?> (<?php echo htmlspecialchars($order['discount_code'] ?? ''); ?>)</small><br>
                            <?php endif; ?>
                            <small class="text-muted">Payment: <?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info text-center my-5">
                <i class="fas fa-box fa-3x mb-3"></i>
                <h4>No orders yet</h4>
                <a href="index.php" class="btn btn-primary mt-3">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cancel order
        document.querySelectorAll('.cancel-order-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                if (confirm('Are you sure you want to cancel this order?\\n\\nRefunds will be processed immediately.')) {
                    const fd = new FormData();
                    fd.append('order_id', orderId);
                    
                    fetch('ajax/cancel_order.php', {
                        method: 'POST',
                        body: fd
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while cancelling the order');
                    });
                }
            });
        });
    </script>
</body>
</html>
