# Advanced Search & Filters - Installation Guide

## ✅ Files Created

### Backend
- `ajax/filter_products.php` - AJAX handler for filtering

### Frontend
- `js/filters.js` - JavaScript filter class
- `includes/filter_sidebar.php` - Filter sidebar component
- CSS styles added to `style.css`

---

## 📋 Installation Steps

### Step 1: Update index.php

Add the filter sidebar and controls to your homepage. Insert this code after the hero section (around line 200):

```php
<!-- Products Section with Filters -->
<div class="container my-5">
    <div class="row">
        <!-- Filter Sidebar -->
        <div class="col-md-3">
            <?php include 'includes/filter_sidebar.php'; ?>
        </div>
        
        <!-- Products Grid -->
        <div class="col-md-9">
            <!-- Sort and Result Info -->
            <div class="result-info">
                <div class="result-count" id="result-count">Loading products...</div>
                <div class="sort-controls">
                    <label for="sort-select">Sort by:</label>
                    <select id="sort-select">
                        <option value="newest">Newest First</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="name_asc">Name: A-Z</option>
                        <option value="name_desc">Name: Z-A</option>
                        <option value="rating">Highest Rated</option>
                        <option value="popular">Most Popular</option>
                    </select>
                </div>
            </div>
            
            <!-- Active Filters -->
            <div id="active-filters" class="active-filters"></div>
            
            <!-- Loading Spinner -->
            <div id="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            
            <!-- Products Grid -->
            <div class="row" id="products-grid">
                <!-- Products will be loaded here via AJAX -->
            </div>
            
            <!-- Pagination -->
            <div id="pagination"></div>
        </div>
    </div>
</div>

<!-- Include Filter JavaScript -->
<script src="js/filters.js"></script>
```

### Step 2: Add Search Input to Navbar

Update the search form in your navbar (around line 115) to use the new filter system:

```php
<input class="form-control me-2" type="search" id="search-input" placeholder="Search products..." aria-label="Search">
```

Remove the form submit button and action, as search will be handled by JavaScript.

---

## 🎯 Features Included

✅ **Price Range Slider** - Dual sliders for min/max price
✅ **Multi-Select Categories** - Check multiple categories
✅ **Stock Filter** - Show only in-stock products
✅ **7 Sort Options** - Newest, price, name, rating, popularity
✅ **Real-time Search** - Debounced search (500ms)
✅ **Active Filter Tags** - Visual display of active filters
✅ **Pagination** - Navigate through results
✅ **AJAX Loading** - No page reloads
✅ **URL Management** - Shareable filter URLs
✅ **Result Count** - Shows number of products found

---

## 🎨 Filter Options

### Sort Options:
1. **Newest First** - Recently added products
2. **Price: Low to High** - Cheapest first
3. **Price: High to Low** - Most expensive first
4. **Name: A-Z** - Alphabetical ascending
5. **Name: Z-A** - Alphabetical descending
6. **Highest Rated** - Best reviews first
7. **Most Popular** - Most reviewed products

### Filters:
- **Price Range**: Slider from min to max price
- **Categories**: Multi-select checkboxes
- **Stock Status**: In stock only toggle
- **Search**: Real-time text search

---

## 🧪 Testing

1. **Open homepage** - You'll see the filter sidebar
2. **Adjust price slider** - Products update automatically
3. **Select categories** - Check multiple categories
4. **Try sorting** - Change sort order
5. **Use search** - Type in search box (debounced)
6. **Clear filters** - Click "Clear All Filters"
7. **Check pagination** - Navigate through pages
8. **Test URL sharing** - Copy URL with filters applied

---

## 🎨 Customization

### Change Filter Colors
Edit in `style.css`:
```css
.price-slider::-webkit-slider-thumb {
    background: var(--primary-gradient); /* Change this */
}
```

### Adjust Debounce Time
Edit in `js/filters.js` (line ~50):
```javascript
setTimeout(() => {
    this.filters.search = e.target.value;
    this.applyFilters();
}, 500); // Change 500ms to your preference
```

### Modify Products Per Page
Edit in `ajax/filter_products.php` (line ~13):
```php
$per_page = 12; // Change this number
```

---

## 🐛 Troubleshooting

**Filters not working?**
- Check browser console for errors
- Verify `ajax/filter_products.php` exists
- Ensure `js/filters.js` is loaded

**Products not loading?**
- Check database connection
- Verify products table has data
- Check AJAX response in Network tab

**Sliders not moving?**
- Ensure jQuery/Bootstrap is loaded
- Check CSS is applied correctly

---

## 📊 How It Works

1. **User interacts** with filters (slider, checkbox, etc.)
2. **JavaScript captures** the change event
3. **Debounced for search** (500ms delay)
4. **AJAX request** sent to `filter_products.php`
5. **Server filters** products based on criteria
6. **JSON response** returned with products
7. **JavaScript renders** products dynamically
8. **URL updated** for shareability
9. **Active filters** displayed as tags

---

## ✨ Next Steps

After installation, users can:
- Filter products by multiple criteria simultaneously
- See real-time results without page reloads
- Share filtered product URLs
- Navigate through paginated results
- Clear all filters with one click

The system is fully responsive and works on mobile, tablet, and desktop!
