<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireCustomerLogin();

$user_id = $_SESSION['user_id'];
$has_wishlist = $conn->query("SHOW TABLES LIKE 'wishlist'")->num_rows > 0;
$items = [];
if ($has_wishlist) {
    $items = $conn->query("SELECT w.*, p.name, p.price, p.image, p.stock, p.status as product_status, s.shop_name 
                          FROM wishlist w 
                          JOIN products p ON w.product_id = p.product_id 
                          JOIN sellers s ON p.seller_id = s.seller_id 
                          WHERE w.user_id = $user_id ORDER BY w.added_at DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - MarketPlace</title>
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
                <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container my-5">
        <h2><i class="fas fa-heart"></i> My Wishlist</h2>
        <?php if (!$has_wishlist): ?>
            <div class="alert alert-info">Wishlist is not set up. Run database_customer_addon.sql.</div>
        <?php elseif (!is_object($items) || $items->num_rows == 0): ?>
            <div class="alert alert-info text-center my-5">
                <i class="fas fa-heart fa-3x mb-3"></i>
                <h4>Your wishlist is empty</h4>
                <a href="index.php" class="btn btn-primary mt-3">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="row mt-4">
                <?php while ($row = $items->fetch_assoc()): ?>
                <div class="col-md-3 mb-4">
                    <div class="product-card position-relative">
                        <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2 remove-wishlist" data-product-id="<?php echo $row['product_id']; ?>" title="Remove from wishlist"><i class="fas fa-times"></i></button>
                        <img src="uploads/products/<?php echo htmlspecialchars($row['image'] ?: 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="card-img-top" style="height:200px;object-fit:cover">
                        <div class="product-card-body">
                            <h5><?php echo htmlspecialchars($row['name']); ?></h5>
                            <p class="text-muted small"><?php echo htmlspecialchars($row['shop_name']); ?></p>
                            <div class="product-price"><?php echo formatPrice($row['price']); ?></div>
                            <?php if ($row['product_status'] === 'active' && $row['stock'] > 0): ?>
                            <a href="product_detail.php?id=<?php echo $row['product_id']; ?>" class="btn btn-primary btn-sm w-100">View & Add to Cart</a>
                            <?php else: ?>
                            <span class="badge bg-secondary">Out of stock / Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelectorAll('.remove-wishlist').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const card = this.closest('.col-md-3');
            fetch('ajax/remove_from_wishlist.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'product_id=' + productId })
                .then(r => r.json())
                .then(data => { if (data.success) card.remove(); });
        });
    });
    </script>
</body>
</html>
