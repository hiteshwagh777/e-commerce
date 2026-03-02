<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get product details
$stmt = $conn->prepare("SELECT p.*, s.shop_name, s.seller_id FROM products p 
                        JOIN sellers s ON p.seller_id = s.seller_id 
                        WHERE p.product_id = ? AND p.status = 'active'");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit();
}

// Track product view
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $conn->query("INSERT INTO product_views (product_id, user_id) VALUES ($product_id, $user_id)");
}

// Get related products (same category)
// Get related and frequently bought together using helpers
$related_products = getRelatedProducts($conn, $product_id, 4);
$frequently_bought_products = getFrequentlyBoughtTogether($conn, $product_id, 4);

$cart_count = 0;
$in_wishlist = false;
if (isset($_SESSION['user_id'])) {
    $cart_count = getCartCount($conn, $_SESSION['user_id']);
    if ($conn->query("SHOW TABLES LIKE 'wishlist'")->num_rows > 0) {
        $chk = $conn->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?");
        $uid = $_SESSION['user_id'];
        $chk->bind_param("ii", $uid, $product_id);
        $chk->execute();
        $in_wishlist = $chk->get_result()->num_rows > 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - MarketPlace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shopping-bag"></i> MarketPlace
            </a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart 
                            <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-6">
                <?php
                // Get product images
                $product_images = [];
                
                // Check if product_images table exists
                $table_check = $conn->query("SHOW TABLES LIKE 'product_images'");
                if ($table_check && $table_check->num_rows > 0) {
                    $images_query = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY display_order ASC");
                    $images_query->bind_param("i", $product_id);
                    $images_query->execute();
                    $images_result = $images_query->get_result();
                    while ($img = $images_result->fetch_assoc()) {
                        $product_images[] = $img['image_path'];
                    }
                }
                
                // Fallback to old image column if no images found
                if (empty($product_images) && $product['image']) {
                    $product_images[] = $product['image'];
                }
                
                // Use default if no images
                if (empty($product_images)) {
                    $product_images[] = 'default.jpg';
                }
                ?>
                
                <!-- Main Image Display -->
                <div class="product-gallery-main mb-3">
                    <img src="uploads/products/<?php echo htmlspecialchars($product_images[0]); ?>" 
                         class="product-detail-img" id="mainImage" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                
                <!-- Thumbnail Gallery -->
                <?php if (count($product_images) > 1): ?>
                <div class="product-gallery-thumbnails">
                    <div class="row g-2">
                        <?php foreach ($product_images as $index => $image): ?>
                        <div class="col-3">
                            <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 onclick="changeImage('uploads/products/<?php echo htmlspecialchars($image); ?>', this)">
                                <img src="uploads/products/<?php echo htmlspecialchars($image); ?>" 
                                     alt="Image <?php echo $index + 1; ?>" class="img-fluid">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p class="text-muted">Sold by: <strong><?php echo htmlspecialchars($product['shop_name']); ?></strong></p>
                
                <?php if ($product['category']): ?>
                <p><span class="badge bg-info"><?php echo htmlspecialchars($product['category']); ?></span></p>
                <?php endif; ?>
                
                <h3 class="text-primary"><?php echo formatPrice($product['price']); ?></h3>
                
                <div class="my-3">
                    <?php if ($product['stock'] > 0): ?>
                        <p class="text-success"><i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)</p>
                    <?php else: ?>
                        <p class="text-danger"><i class="fas fa-times-circle"></i> Out of Stock</p>
                    <?php endif; ?>
                </div>
                
                <div class="my-4">
                    <h5>Description:</h5>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <?php if (isset($_SESSION['user_id']) && $product['stock'] > 0): ?>
                <div class="my-4">
                    <div class="input-group mb-3" style="max-width: 200px;">
                        <button class="btn btn-outline-secondary" type="button" id="decreaseQty">-</button>
                        <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button class="btn btn-outline-secondary" type="button" id="increaseQty">+</button>
                    </div>
                    <button class="btn btn-primary btn-lg" id="addToCartBtn" data-product-id="<?php echo $product_id; ?>">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                    <?php if ($conn->query("SHOW TABLES LIKE 'wishlist'")->num_rows > 0): ?>
                    <button type="button" class="btn btn-outline-danger btn-lg ms-2" id="wishlistBtn" data-product-id="<?php echo $product_id; ?>" title="<?php echo $in_wishlist ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                        <i class="fas fa-heart<?php echo $in_wishlist ? '' : '-o'; ?>"></i> <?php echo $in_wishlist ? 'In Wishlist' : 'Add to Wishlist'; ?>
                    </button>
                    <?php endif; ?>
                </div>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                <div class="alert alert-info">
                    Please <a href="login.php">login</a> to add items to cart.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Frequently Bought Together -->
        <?php if (!empty($frequently_bought_products)): ?>
        <div class="recommendation-section">
            <h3><i class="fas fa-link"></i> Frequently Bought Together</h3>
            <div class="row">
                <?php foreach($frequently_bought_products as $fbp): ?>
                <div class="col-md-3">
                    <div class="product-card">
                        <img src="uploads/products/<?php echo htmlspecialchars($fbp['image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($fbp['name']); ?>">
                        <div class="product-card-body">
                            <h5><?php echo htmlspecialchars($fbp['name']); ?></h5>
                            <div class="product-price"><?php echo formatPrice($fbp['price']); ?></div>
                            <a href="product_detail.php?id=<?php echo $fbp['product_id']; ?>" class="btn btn-primary btn-sm w-100">View</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div class="recommendation-section">
            <h3><i class="fas fa-th"></i> Related Products</h3>
            <div class="row">
                <?php foreach($related_products as $rp): ?>
                <div class="col-md-3">
                    <div class="product-card">
                        <img src="uploads/products/<?php echo htmlspecialchars($rp['image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($rp['name']); ?>">
                        <div class="product-card-body">
                            <h5><?php echo htmlspecialchars($rp['name']); ?></h5>
                            <div class="product-price"><?php echo formatPrice($rp['price']); ?></div>
                            <a href="product_detail.php?id=<?php echo $rp['product_id']; ?>" class="btn btn-primary btn-sm w-100">View</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Change main image on thumbnail click
        function changeImage(imageSrc, element) {
            document.getElementById('mainImage').src = imageSrc;
            // Remove active class from all thumbnails
            document.querySelectorAll('.thumbnail-item').forEach(item => {
                item.classList.remove('active');
            });
            // Add active class to clicked thumbnail
            element.classList.add('active');
        }

        // Quantity controls
        document.getElementById('increaseQty').addEventListener('click', function() {
            let qty = document.getElementById('quantity');
            if (parseInt(qty.value) < parseInt(qty.max)) {
                qty.value = parseInt(qty.value) + 1;
            }
        });

        document.getElementById('decreaseQty').addEventListener('click', function() {
            let qty = document.getElementById('quantity');
            if (parseInt(qty.value) > 1) {
                qty.value = parseInt(qty.value) - 1;
            }
        });

        // Wishlist toggle
        var wishlistBtn = document.getElementById('wishlistBtn');
        if (wishlistBtn) {
            wishlistBtn.addEventListener('click', function() {
                var productId = this.getAttribute('data-product-id');
                var isIn = this.querySelector('i').classList.contains('fa-heart') && !this.querySelector('i').classList.contains('fa-heart-o');
                var url = isIn ? 'ajax/remove_from_wishlist.php' : 'ajax/add_to_wishlist.php';
                var fd = new FormData();
                fd.append('product_id', productId);
                fetch(url, { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            location.reload();
                        }
                    });
            });
        }

        // Add to cart
        document.getElementById('addToCartBtn').addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const quantity = document.getElementById('quantity').value;
            
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart!');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to add to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
    </script>

    <?php include 'includes/product_reviews_section.php'; ?>
</body>
</html>

