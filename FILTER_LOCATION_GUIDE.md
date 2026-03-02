# Quick Guide: Finding Advanced Search & Filters

## 📍 Where to Find the Filters

The Advanced Search & Filters are now **integrated into your homepage** at `http://localhost/ecommerce/`

### Visual Layout:

```
┌─────────────────────────────────────────────────────────────┐
│                        NAVBAR                                │
│  [Search Box] - Type here for real-time search              │
└─────────────────────────────────────────────────────────────┘

┌──────────────────┬──────────────────────────────────────────┐
│  FILTER SIDEBAR  │         PRODUCTS GRID                    │
│                  │  ┌────────────────────────────────────┐  │
│  💰 Price Range  │  │  [Sort Dropdown] [Result Count]    │  │
│  ├─ Min: ₹0     │  └────────────────────────────────────┘  │
│  └─ Max: ₹5000  │                                           │
│                  │  [Active Filter Tags]                    │
│  📁 Categories   │                                           │
│  ☐ Electronics  │  ┌─────┐  ┌─────┐  ┌─────┐              │
│  ☐ Fashion      │  │ P1  │  │ P2  │  │ P3  │              │
│  ☐ Books        │  └─────┘  └─────┘  └─────┘              │
│                  │                                           │
│  ✅ In Stock    │  ┌─────┐  ┌─────┐  ┌─────┐              │
│                  │  │ P4  │  │ P5  │  │ P6  │              │
│  [Clear All]     │  └─────┘  └─────┘  └─────┘              │
│                  │                                           │
│                  │  [Pagination: 1 2 3 Next]                │
└──────────────────┴──────────────────────────────────────────┘
```

## ✅ Files Verified

All filter files are in place:

1. **`includes/filter_sidebar.php`** ✅ - Filter UI component
2. **`js/filters.js`** ✅ - JavaScript filter logic
3. **`ajax/filter_products.php`** ✅ - AJAX handler
4. **`style.css`** ✅ - Filter styles added
5. **`index.php`** ✅ - Integration complete (line 238)

## 🎯 What You Should See

When you visit `http://localhost/ecommerce/`, you should see:

### Left Sidebar (3 columns):
- **Price Range Sliders** - Two sliders for min/max price
- **Category Checkboxes** - List of all categories
- **In Stock Only** - Toggle checkbox
- **Clear All Filters** - Button to reset

### Right Side (9 columns):
- **Sort Dropdown** - 7 sorting options
- **Result Count** - "X products found"
- **Active Filter Tags** - Shows applied filters
- **Product Grid** - 3 products per row
- **Pagination** - Page navigation

## 🔍 How to Use

1. **Search**: Type in navbar search box
2. **Price**: Drag the min/max sliders
3. **Categories**: Check one or more categories
4. **Sort**: Select from dropdown
5. **Stock**: Toggle "In Stock Only"
6. **Clear**: Click "Clear All Filters"

## 🐛 If You Don't See Filters

### Check 1: Browser Console
- Press `F12` in browser
- Look for JavaScript errors
- Check if `filters.js` is loaded

### Check 2: File Paths
Run these commands to verify files exist:

```powershell
Test-Path "d:\xampp\htdocs\ecommerce\includes\filter_sidebar.php"
Test-Path "d:\xampp\htdocs\ecommerce\js\filters.js"
Test-Path "d:\xampp\htdocs\ecommerce\ajax\filter_products.php"
```

### Check 3: PHP Errors
- Check XAMPP error logs
- Look for include errors
- Verify database connection

### Check 4: Clear Cache
- Clear browser cache (Ctrl+Shift+Delete)
- Hard refresh (Ctrl+F5)
- Try incognito mode

## 📱 Responsive Design

- **Desktop**: Full sidebar visible
- **Tablet**: Sidebar may collapse
- **Mobile**: Filters in dropdown/modal

## 🎨 Styling

The filters use your existing design system:
- Purple gradient sliders
- Matching color scheme
- Smooth animations
- Modern glassmorphism effects

## 🚀 Next Steps

1. Visit homepage
2. Look for sidebar on the left
3. Try adjusting price sliders
4. Select some categories
5. Watch products update in real-time!

The filters are **already integrated** - just refresh your homepage!
