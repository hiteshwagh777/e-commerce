<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/flash_sale_functions.php';

// Update flash sale statuses
updateFlashSaleStatuses($conn);

// Get cart and wishlist counts
$cart_count = 0;
$wishlist_count = 0;
if (isset($_SESSION['user_id'])) {
    $cart_count = getCartCount($conn, $_SESSION['user_id']);
    $wishlist_count = getWishlistCount($conn, $_SESSION['user_id']);
}

// Get all active flash sales
$flash_sales = getActiveFlashSales($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flash Sales - E-Commerce Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <!-- Flash Sales Hero -->
    <div class="flash-sale-banner">
        <div class="container">
            <div class="flash-sale-header">
                <span class="flash-sale-badge">
                    <i class="fas fa-bolt"></i> LIMITED TIME OFFERS
                </span>
                <h1><i class="fas fa-fire"></i> Flash Sales</h1>
                <p>Grab these amazing deals before they're gone!</p>
            </div>
        </div>
    </div>
    
    <!-- Flash Sales Content -->
    <div class="container my-5">
        <!-- Filters -->
        <div class="flash-sale-filters">
            <div class="flash-sale-tabs">
                <button class="flash-tab active" data-filter="all">
                    <i class="fas fa-th"></i> All Sales
                </button>
                <button class="flash-tab" data-filter="ending-soon">
                    <i class="fas fa-clock"></i> Ending Soon
                </button>
                <button class="flash-tab" data-filter="high-discount">
                    <i class="fas fa-percentage"></i> Highest Discount
                </button>
                <button class="flash-tab" data-filter="low-stock">
                    <i class="fas fa-box"></i> Low Stock
                </button>
            </div>
        </div>
        
        <!-- Sales Grid -->
        <?php if ($flash_sales->num_rows > 0): ?>
        <div class="flash-sales-grid" id="flash-sales-grid">
            <?php while($sale = $flash_sales->fetch_assoc()): 
                $remaining_stock = getFlashSaleRemainingStock($conn, $sale['sale_id']);
                $stock_percentage = $sale['stock_limit'] ? 
                    (($sale['stock_limit'] - $sale['sold_count']) / $sale['stock_limit']) * 100 : 100;
                $time_remaining = getFlashSaleTimeRemaining($sale['end_time']);
            ?>
            <div class="flash-sale-card" 
                 data-discount="<?php echo $sale['discount_percentage']; ?>"
                 data-time-remaining="<?php echo $time_remaining; ?>"
                 data-stock-percentage="<?php echo $stock_percentage; ?>">
                <span class="limited-badge">
                    <i class="fas fa-clock"></i> Limited Time
                </span>
                <img src="uploads/products/<?php echo htmlspecialchars($sale['image'] ?: 'default.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($sale['name']); ?>">
                <div class="flash-sale-card-body">
                    <h5><?php echo htmlspecialchars($sale['name']); ?></h5>
                    <p class="text-muted small">by <?php echo htmlspecialchars($sale['shop_name']); ?></p>
                    
                    <?php if ($sale['category']): ?>
                    <span class="badge bg-info mb-2"><?php echo htmlspecialchars($sale['category']); ?></span>
                    <?php endif; ?>
                    
                    <div class="flash-sale-prices">
                        <div class="flash-sale-price">₹<?php echo number_format($sale['sale_price'], 2); ?></div>
                        <div class="flash-original-price">₹<?php echo number_format($sale['original_price'], 2); ?></div>
                        <span class="flash-discount-badge"><?php echo $sale['discount_percentage']; ?>% OFF</span>
                    </div>
                    
                    <?php if ($sale['stock_limit']): ?>
                    <div class="flash-stock-bar">
                        <div class="flash-stock-label">
                            <span><i class="fas fa-box"></i> Stock</span>
                            <span><?php echo $remaining_stock; ?> / <?php echo $sale['stock_limit']; ?> left</span>
                        </div>
                        <div class="flash-stock-progress">
                            <div class="flash-stock-fill" style="width: <?php echo $stock_percentage; ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div id="countdown-<?php echo $sale['sale_id']; ?>" 
                         data-countdown 
                         data-sale-id="<?php echo $sale['sale_id']; ?>"
                         data-end-time="<?php echo $sale['end_time']; ?>">
                    </div>
                    
                    <a href="product_detail.php?id=<?php echo $sale['product_id']; ?>" 
                       class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-shopping-cart"></i> Buy Now
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> No active flash sales at the moment. Check back soon!
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/flash_sales.js"></script>
    <script>
    // Filter functionality
    document.querySelectorAll('.flash-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            // Update active tab
            document.querySelectorAll('.flash-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            const cards = document.querySelectorAll('.flash-sale-card');
            
            cards.forEach(card => {
                let show = true;
                
                if (filter === 'ending-soon') {
                    const timeRemaining = parseInt(card.dataset.timeRemaining);
                    show = timeRemaining < 3600; // Less than 1 hour
                } else if (filter === 'high-discount') {
                    const discount = parseInt(card.dataset.discount);
                    show = discount >= 50; // 50% or more
                } else if (filter === 'low-stock') {
                    const stockPercentage = parseFloat(card.dataset.stockPercentage);
                    show = stockPercentage < 30; // Less than 30% stock
                }
                
                card.style.display = show ? 'block' : 'none';
            });
        });
    });
    </script>
</body>
</html>
