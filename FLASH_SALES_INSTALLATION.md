# Flash Sales System - Installation Guide

## ✅ Files Created

### Database
- `database_flash_sales.sql` - Schema with 2 tables

### Backend
- `includes/flash_sale_functions.php` - 15+ helper functions
- `ajax/get_flash_sales.php` - AJAX data handler

### Frontend
- `js/flash_sales.js` - Countdown timer class
- `includes/flash_sale_banner.php` - Homepage banner component
- `flash_sales.php` - Dedicated flash sales page
- `seller/manage_flash_sales.php` - Seller admin interface
- CSS styles added to `style.css`

---

## 📋 Installation Steps

### Step 1: Import Database Schema

```bash
# Via phpMyAdmin or command line
mysql -u root ecommerce_marketplace < database_flash_sales.sql
```

Or via PowerShell:
```powershell
Get-Content "d:\xampp\htdocs\ecommerce\database_flash_sales.sql" | d:\xampp\mysql\bin\mysql.exe -u root ecommerce_marketplace
```

### Step 2: Add Flash Sale Banner to Homepage

Add this line to `index.php` after the hero section (around line 190):

```php
<?php include 'includes/flash_sale_banner.php'; ?>
```

### Step 3: Update Product Detail Page

Add flash sale detection at the top of `product_detail.php` (after including functions):

```php
require_once 'includes/flash_sale_functions.php';
updateFlashSaleStatuses($conn);
$flash_sale = getFlashSaleByProduct($conn, $product_id);
```

Then update the price display section to show flash sale pricing:

```php
<?php if ($flash_sale): ?>
    <div class="flash-sale-prices mb-3">
        <div class="flash-sale-price">₹<?php echo number_format($flash_sale['sale_price'], 2); ?></div>
        <div class="flash-original-price">₹<?php echo number_format($flash_sale['original_price'], 2); ?></div>
        <span class="flash-discount-badge"><?php echo $flash_sale['discount_percentage']; ?>% OFF</span>
    </div>
    <div class="alert alert-warning">
        <i class="fas fa-clock"></i> <strong>Flash Sale!</strong> Ends in:
        <div id="countdown-product-<?php echo $flash_sale['sale_id']; ?>" 
             data-countdown 
             data-sale-id="<?php echo $flash_sale['sale_id']; ?>"
             data-end-time="<?php echo $flash_sale['end_time']; ?>">
        </div>
    </div>
    <script src="js/flash_sales.js"></script>
<?php else: ?>
    <h3 class="text-primary">₹<?php echo number_format($product['price'], 2); ?></h3>
<?php endif; ?>
```

### Step 4: Add Link to Navbar

Add flash sales link to your navbar (in `includes/navbar.php` or `index.php`):

```php
<li class="nav-item">
    <a class="nav-link" href="flash_sales.php">
        <i class="fas fa-bolt"></i> Flash Sales
    </a>
</li>
```

### Step 5: Add Seller Menu Link

Add to seller dashboard navigation:

```php
<a href="manage_flash_sales.php" class="nav-link">
    <i class="fas fa-bolt"></i> Flash Sales
</a>
```

---

## 🎯 Features Included

### ⏰ Countdown Timer
- Real-time countdown (days, hours, minutes, seconds)
- Auto-refresh every second
- Auto-reload page when expired
- Smooth animations

### 💰 Pricing Display
- Original price with strikethrough
- Sale price in gradient style
- Discount percentage badge
- Clear visual hierarchy

### 📊 Stock Management
- Optional stock limits
- Progress bar showing remaining stock
- Auto-expire when stock runs out
- Purchase tracking

### 🎨 Visual Design
- Animated gradient banner
- Pulsing "Limited Time" badge
- Stock progress bars
- Responsive layout

### 🛠️ Admin Interface
- Create flash sales
- Set start/end times
- Define stock limits
- Auto-calculate discounts
- View sales history
- Cancel active sales

### 📄 Dedicated Page
- All active flash sales
- Filter by:
  - Ending soon (< 1 hour)
  - High discount (≥ 50%)
  - Low stock (< 30%)
- Grid layout

### 🔄 Auto-Status Updates
- Scheduled → Active (at start time)
- Active → Expired (at end time)
- Active → Expired (stock limit reached)
- Updates on every page load

---

## 🎨 Customization

### Change Flash Sale Colors

Edit in `style.css`:
```css
.flash-sale-banner {
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
    /* Change to your preferred gradient */
}
```

### Adjust Countdown Refresh Rate

Edit in `js/flash_sales.js` (line ~60):
```javascript
setInterval(refreshFlashSales, 60000); // Change 60000ms (60s) to your preference
```

### Modify Banner Product Limit

Edit in `includes/flash_sale_banner.php` (line ~7):
```php
$flash_sales = getActiveFlashSales($conn, 4); // Change 4 to desired number
```

---

## 🧪 Testing

1. **Create a Flash Sale**
   - Login as seller
   - Go to `seller/manage_flash_sales.php`
   - Select product, set sale price
   - Set start time (now or future)
   - Set end time
   - Optionally set stock limit
   - Click "Create Flash Sale"

2. **View on Homepage**
   - Flash sale banner should appear
   - Countdown timer should be running
   - Stock bar should show (if limit set)

3. **Test Countdown**
   - Wait for countdown to update
   - Verify it counts down correctly
   - Check auto-refresh works

4. **Test Expiration**
   - Create a sale ending in 2 minutes
   - Wait for expiration
   - Page should reload
   - Sale should disappear

5. **Test Filters**
   - Go to `flash_sales.php`
   - Click different filter tabs
   - Verify products filter correctly

---

## 📊 Database Structure

```
flash_sales
├── sale_id (PK)
├── product_id (FK → products)
├── original_price
├── sale_price
├── discount_percentage
├── start_time
├── end_time
├── stock_limit (nullable)
├── sold_count
├── status (scheduled/active/expired/cancelled)
├── created_at
└── created_by (FK → sellers)

flash_sale_purchases
├── purchase_id (PK)
├── sale_id (FK → flash_sales)
├── user_id (FK → users)
├── quantity
├── purchase_price
└── purchased_at
```

---

## 🔧 Helper Functions

### Main Functions:
- `getActiveFlashSales()` - Get all active sales
- `getFlashSaleByProduct()` - Check if product on sale
- `createFlashSale()` - Create new sale
- `updateFlashSaleStatuses()` - Auto-update statuses
- `recordFlashSalePurchase()` - Track purchases
- `getFlashSaleTimeRemaining()` - Calculate time left
- `cancelFlashSale()` - Cancel a sale

---

## 🐛 Troubleshooting

**Countdown not showing?**
- Verify `js/flash_sales.js` is loaded
- Check browser console for errors
- Ensure data attributes are set correctly

**Sales not appearing?**
- Check start/end times are correct
- Verify product is active
- Run `updateFlashSaleStatuses()` manually

**Stock not updating?**
- Ensure `recordFlashSalePurchase()` is called on purchase
- Check database for sold_count value

**Styles not applied?**
- Clear browser cache
- Verify CSS was added to `style.css`
- Check for CSS conflicts

---

## ✨ Usage Tips

1. **Create Urgency**: Set short durations (1-6 hours)
2. **Limit Stock**: Use stock limits to create scarcity
3. **High Discounts**: 30-70% off works best
4. **Schedule Ahead**: Schedule sales for peak times
5. **Monitor Performance**: Check sold_count regularly

---

## 🚀 Next Steps

After installation:
- Create your first flash sale
- Test the countdown timer
- Share flash sales page link
- Monitor sales performance
- Adjust pricing strategies

The flash sales system is fully functional and ready to drive urgency and boost conversions!
