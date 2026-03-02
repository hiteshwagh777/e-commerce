<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireCustomerLogin();

$user_id = $_SESSION['user_id'];

// Get cart items
$cart_items = $conn->prepare("SELECT c.*, p.name, p.price, p.stock, p.image, s.shop_name 
                              FROM cart c 
                              JOIN products p ON c.product_id = p.product_id 
                              JOIN sellers s ON p.seller_id = s.seller_id
                              WHERE c.user_id = ?");
$cart_items->bind_param("i", $user_id);
$cart_items->execute();
$cart_items = $cart_items->get_result();

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - MarketPlace</title>
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
                <li class="nav-item"><a class="nav-link" href="orders.php"><i class="fas fa-box"></i> Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container my-5">
        <h2><i class="fas fa-shopping-cart"></i> Shopping Cart</h2>
        
        <?php if ($cart_items->num_rows > 0): ?>
        <div class="row mt-4">
            <div class="col-md-8">
                <?php while($item = $cart_items->fetch_assoc()): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                <div class="cart-item">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="uploads/products/<?php echo htmlspecialchars($item['image'] ?: 'default.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid">
                        </div>
                        <div class="col-md-4">
                            <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                            <p class="text-muted small">by <?php echo htmlspecialchars($item['shop_name']); ?></p>
                        </div>
                        <div class="col-md-2">
                            <strong><?php echo formatPrice($item['price']); ?></strong>
                        </div>
                        <div class="col-md-2">
                            <div class="input-group">
                                <button class="btn btn-sm btn-outline-secondary update-qty" data-cart-id="<?php echo $item['cart_id']; ?>" data-action="decrease">-</button>
                                <input type="number" class="form-control form-control-sm text-center" value="<?php echo $item['quantity']; ?>" readonly>
                                <button class="btn btn-sm btn-outline-secondary update-qty" data-cart-id="<?php echo $item['cart_id']; ?>" data-action="increase" data-max="<?php echo $item['stock']; ?>">+</button>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <strong><?php echo formatPrice($subtotal); ?></strong><br>
                            <button class="btn btn-sm btn-danger mt-2 remove-item" data-cart-id="<?php echo $item['cart_id']; ?>">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="col-md-4">
                <div class="cart-summary">
                    <h4>Order Summary</h4>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong><?php echo formatPrice($total); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery:</span>
                        <strong>₹50.00</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <h5>Total:</h5>
                        <h5 class="text-primary"><?php echo formatPrice($total + 50); ?></h5>
                    </div>
                    <a href="checkout.php" class="btn btn-primary w-100 btn-lg">
                        <i class="fas fa-lock"></i> Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center my-5">
            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
            <h4>Your cart is empty</h4>
            <a href="index.php" class="btn btn-primary mt-3">Continue Shopping</a>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update quantity
        document.querySelectorAll('.update-qty').forEach(btn => {
            btn.addEventListener('click', function() {
                const cartId = this.dataset.cartId;
                const action = this.dataset.action;
                const maxQty = this.dataset.max;
                
                fetch('ajax/update_cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `cart_id=${cartId}&action=${action}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            });
        });

        // Remove item
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Remove this item from cart?')) {
                    const cartId = this.dataset.cartId;
                    
                    fetch('ajax/remove_from_cart.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: `cart_id=${cartId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
