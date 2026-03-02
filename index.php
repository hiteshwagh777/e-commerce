<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$cart_count = 0;
$wishlist_count = 0;
$has_wishlist_table = $conn->query("SHOW TABLES LIKE 'wishlist'")->num_rows > 0;
if (isset($_SESSION['user_id'])) {
    $cart_count = getCartCount($conn, $_SESSION['user_id']);
    $wishlist_count = getWishlistCount($conn, $_SESSION['user_id']);
}

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

// Build query
$where_conditions = ["p.status = 'active'", "s.status = 'approved'"];
$params = [];
$types = "";

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= "ss";
}

if (!empty($category)) {
    $where_conditions[] = "p.category = ?";
    $params[] = &$category;
    $types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

$query = "SELECT p.*, s.shop_name FROM products p 
          JOIN sellers s ON p.seller_id = s.seller_id 
          WHERE $where_clause 
          ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Main categories for browsing (Electronics, Beauty, Kids Products, Gifting Items, Decoration Items)
$main_categories = ['Electronics', 'Beauty', 'Kids Products', 'Gifting Items', 'Decoration Items'];
// Get categories from products (merge with main so all show)
$categories = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
$category_list = $main_categories;
while ($row = $categories->fetch_assoc()) {
    if (!in_array($row['category'], $category_list)) $category_list[] = $row['category'];
}

// Get top selling products
$top_products = $conn->query("SELECT p.*, s.shop_name, COUNT(oi.item_id) as sales_count 
                              FROM products p 
                              JOIN sellers s ON p.seller_id = s.seller_id 
                              LEFT JOIN order_items oi ON p.product_id = oi.product_id 
                              WHERE p.status = 'active' AND s.status = 'approved'
                              GROUP BY p.product_id 
                              ORDER BY sales_count DESC 
                              LIMIT 4");

// Get recommended products for logged-in users
$recommended_products = null;
if (isset($_SESSION['user_id'])) {
    // Get products based on user's purchase history
    $recommended_products = $conn->prepare("SELECT DISTINCT p.*, s.shop_name 
                                           FROM products p 
                                           JOIN sellers s ON p.seller_id = s.seller_id
                                           WHERE p.category IN (
                                               SELECT DISTINCT p2.category 
                                               FROM purchase_history ph 
                                               JOIN products p2 ON ph.product_id = p2.product_id 
                                               WHERE ph.user_id = ?
                                           ) 
                                           AND p.product_id NOT IN (
                                               SELECT product_id FROM purchase_history WHERE user_id = ?
                                           )
                                           AND p.status = 'active' AND s.status = 'approved'
                                           LIMIT 4");
    $user_id = $_SESSION['user_id'];
    $recommended_products->bind_param("ii", $user_id, $user_id);
    $recommended_products->execute();
    $recommended_products = $recommended_products->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Marketplace - Shop Online</title>
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <form class="search-bar d-flex mx-auto" method="GET" style="width: 50%;">
                    <input class="form-control" type="search" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="fas fa-shopping-cart"></i> Cart 
                                <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="wishlist.php"><i class="fas fa-heart"></i> Wishlist <span class="badge bg-danger"><?php echo $wishlist_count; ?></span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php"><i class="fas fa-box"></i> Orders</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a></li>
                                <li><a class="dropdown-item" href="wallet.php"><i class="fas fa-wallet"></i> Wallet</a></li>
                                <li><a class="dropdown-item" href="payment_history.php"><i class="fas fa-history"></i> Payment History</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <?php if (empty($search) && empty($category)): ?>
    <section class="hero-section">
        <div class="container">
            <h1>Welcome to Our Marketplace</h1>
            <p>Discover amazing products from trusted sellers</p>
            <a href="#products" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag"></i> Start Shopping
            </a>
        </div>
    </section>
    <?php endif; ?>

    <div class="container my-5">
        <!-- Category Filter: Only show when not searching -->
        <?php if (empty($search)): ?>
        <div class="category-filter">
            <h5><i class="fas fa-filter"></i> Browse Product Categories</h5>
            <a href="index.php" class="btn btn-sm <?php echo empty($category) ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
            <?php foreach ($category_list as $cat_name): ?>
                <a href="?category=<?php echo urlencode($cat_name); ?>" 
                   class="btn btn-sm <?php echo $category === $cat_name ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    <?php echo htmlspecialchars($cat_name); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Recommended Products -->
        <?php if ($recommended_products && $recommended_products->num_rows > 0): ?>
        <div class="recommendation-section">
            <h3><i class="fas fa-star"></i> Recommended For You</h3>
            <div class="row">
                <?php while($product = $recommended_products->fetch_assoc()): ?>
                <div class="col-md-3 mb-4">
                    <div class="product-card">
                        <img src="uploads/products/<?php echo htmlspecialchars($product['image'] ?: 'default.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-card-body">
                            <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="text-muted small">by <?php echo htmlspecialchars($product['shop_name']); ?></p>
                            <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                            <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary btn-sm w-100">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Top Selling Products -->
        <?php if ($top_products->num_rows > 0 && empty($search) && empty($category)): ?>
        <div class="recommendation-section">
            <h3><i class="fas fa-fire"></i> Top Selling Products</h3>
            <div class="row">
                <?php while($product = $top_products->fetch_assoc()): ?>
                <div class="col-md-3 mb-4">
                    <div class="product-card">
                        <img src="uploads/products/<?php echo htmlspecialchars($product['image'] ?: 'default.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-card-body">
                            <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="text-muted small">by <?php echo htmlspecialchars($product['shop_name']); ?></p>
                            <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                            <span class="badge bg-success mb-2"><?php echo $product['sales_count']; ?> sold</span>
                            <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary btn-sm w-100">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- All Products -->
        <div id="products">
            <h3 class="mb-4">
                <i class="fas fa-shopping-bag"></i> 
                <?php echo !empty($search) ? "Search Results for '$search'" : (!empty($category) ? $category : "All Products"); ?>
            </h3>
            <!-- Products Grid -->
            <div class="row" id="products-grid">
                <?php if ($products->num_rows > 0): ?>
                    <?php while($product = $products->fetch_assoc()): ?>
                    <div class="col-md-3 mb-4">
                        <div class="product-card">
                            <img src="uploads/products/<?php echo htmlspecialchars($product['image'] ?: 'default.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="product-card-body">
                                <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="text-muted small">by <?php echo htmlspecialchars($product['shop_name']); ?></p>
                                <?php if ($product['category']): ?>
                                <span class="badge bg-info mb-2"><?php echo htmlspecialchars($product['category']); ?></span>
                                <?php endif; ?>
                                <?php if (isset($product['average_rating']) && $product['average_rating'] > 0): ?>
                                <div class="mb-2">
                                    <span class="text-warning">★</span> <?php echo number_format($product['average_rating'], 1); ?>
                                    <span class="text-muted small">(<?php echo $product['review_count'] ?? 0; ?>)</span>
                                </div>
                                <?php endif; ?>
                                <div class="product-price"><?php echo formatPrice($product['price']); ?></div>
                                <div class="product-stock">
                                    <?php if ($product['stock'] > 0): ?>
                                        <i class="fas fa-check-circle text-success"></i> In Stock (<?php echo $product['stock']; ?>)
                                    <?php else: ?>
                                        <i class="fas fa-times-circle text-danger"></i> Out of Stock
                                    <?php endif; ?>
                                </div>
                                <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary btn-sm w-100 mt-2">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <?php if (isset($_SESSION['user_id']) && $has_wishlist_table): ?>
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-1 add-wishlist-index" data-product-id="<?php echo $product['product_id']; ?>"><i class="fas fa-heart"></i> Wishlist</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No products found.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h5>About Us</h5>
                    <p>Your trusted online marketplace connecting buyers with quality sellers.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="seller_register.php">Become a Seller</a></li>
                        <li><a href="delivery_register.php">Join as Delivery Partner</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Customer Service</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Returns</a></li>
                        <li><a href="#">Shipping Info</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Follow Us</h5>
                    <div>
                        <a href="#" class="me-3"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter fa-2x"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-instagram fa-2x"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; 2024 E-Commerce Marketplace. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($_SESSION['user_id']) && $has_wishlist_table): ?>
    <script>
    document.querySelectorAll('.add-wishlist-index').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var productId = this.dataset.productId;
            var fd = new FormData();
            fd.append('product_id', productId);
            fetch('ajax/add_to_wishlist.php', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        btn.innerHTML = '<i class="fas fa-heart text-danger"></i> Added';
                        setTimeout(function() { location.reload(); }, 800);
                    }
                });
        });
    });
    </script>
    <?php endif; ?>
</body>
</html>
