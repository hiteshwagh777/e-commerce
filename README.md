# Smart Multi-Vendor E-Commerce Marketplace

A comprehensive e-commerce platform built with Core PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap. This system mimics Amazon/Flipkart functionality with multi-vendor support, delivery management, and intelligent product recommendations.

## 🌟 Features

### Multi-Role System
- **Customer**: Browse, search, shop, track orders
- **Seller (Vendor)**: Manage products, view sales, track revenue
- **Delivery Partner**: Accept deliveries, update status, track earnings
- **Admin**: Manage all users, approve accounts, assign deliveries

### Customer Features
✅ User registration and login with session-based authentication  
✅ Browse and search products with category filters  
✅ View detailed product information  
✅ AJAX-powered shopping cart  
✅ Update/remove cart items dynamically  
✅ Secure checkout with address management  
✅ Track order and delivery status  
✅ View complete order history  
✅ Get personalized product recommendations  
✅ View frequently bought together items  
✅ See top-selling products  

### Seller Features
✅ Separate registration with business details (GST, license)  
✅ Account approval workflow  
✅ Add/edit/delete products with image upload  
✅ Set pricing and manage inventory  
✅ View seller-specific orders  
✅ Track revenue and sales statistics  
✅ Dashboard with key metrics  

### Delivery Partner Features
✅ Registration with vehicle and license details  
✅ Account approval system  
✅ View assigned deliveries  
✅ Accept/reject delivery requests  
✅ Update delivery status (picked, out for delivery, delivered)  
✅ Track delivery history  
✅ View total earnings  

### Admin Features
✅ Comprehensive dashboard with all statistics  
✅ Manage customers, sellers, and delivery partners  
✅ Approve/reject/block user accounts  
✅ View and manage all products  
✅ Monitor all orders  
✅ Assign deliveries to partners  
✅ View revenue and analytics  

### Smart Features (ML/Recommendation)
✅ Personalized product recommendations based on purchase history  
✅ Frequently bought together suggestions  
✅ Related products by category  
✅ Top-selling products display  
✅ Purchase history tracking  
✅ Product view analytics  

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: Core PHP 7.4+
- **Database**: MySQL 8.0+
- **Server**: XAMPP (Apache, MySQL)
- **Database Management**: phpMyAdmin

## 📋 Requirements

- XAMPP Server (v3.3.0 or higher)
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Modern web browser (Chrome, Firefox, Edge)
- At least 2GB free disk space

## 🚀 Installation Instructions

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Install XAMPP on your system
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Database
1. Open phpMyAdmin in your browser: `http://localhost/phpmyadmin`
2. Create a new database named `ecommerce_marketplace`
3. Import the database schema:
   - Click on the database name
   - Go to "Import" tab
   - Choose file: `database.sql`
   - Click "Go" to execute

### Step 3: Deploy Application
1. Copy the entire `ecommerce` folder to your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\ecommerce  (Windows)
   /opt/lampp/htdocs/ecommerce  (Linux)
   /Applications/XAMPP/htdocs/ecommerce  (Mac)
   ```

2. Set proper permissions for uploads folder:
   ```bash
   chmod -R 777 uploads/  (Linux/Mac)
   ```
   On Windows, ensure the uploads folder has write permissions.

### Step 4: Configure Database Connection
1. Open `config/database.php`
2. Verify the database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Default XAMPP password is empty
   define('DB_NAME', 'ecommerce_marketplace');
   ```

### Step 5: Access the Application
Open your browser and navigate to:
```
http://localhost/ecommerce/
```

## 👤 Default Login Credentials

### Admin Account
- **URL**: `http://localhost/ecommerce/login.php?role=admin`
- **Username**: `admin`
- **Password**: `admin123`

### Test Customer (Create your own)
- Register at: `http://localhost/ecommerce/register.php`

### Test Seller (Create your own)
- Register at: `http://localhost/ecommerce/seller_register.php`
- **Note**: Seller accounts require admin approval before login

### Test Delivery Partner (Create your own)
- Register at: `http://localhost/ecommerce/delivery_register.php`
- **Note**: Delivery partner accounts require admin approval

## 📂 Project Structure

```
ecommerce/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   └── functions.php         # Helper functions and session management
├── admin/
│   ├── dashboard.php         # Admin dashboard
│   ├── manage_sellers.php    # Seller management
│   ├── manage_delivery.php   # Delivery partner management
│   └── manage_orders.php     # Order management and delivery assignment
├── seller/
│   ├── dashboard.php         # Seller dashboard with statistics
│   ├── products.php          # Product management (add/edit/delete)
│   └── orders.php            # Seller's order view
├── delivery/
│   ├── dashboard.php         # Delivery partner dashboard
│   └── update_delivery.php   # Delivery status updates
├── ajax/
│   ├── add_to_cart.php       # Add to cart handler
│   ├── update_cart.php       # Update cart quantity
│   └── remove_from_cart.php  # Remove from cart
├── uploads/
│   └── products/             # Product images storage
├── index.php                 # Homepage with product listing
├── product_detail.php        # Product detail page
├── cart.php                  # Shopping cart
├── checkout.php              # Checkout and order placement
├── orders.php                # Customer order history
├── login.php                 # Unified login for all roles
├── register.php              # Customer registration
├── seller_register.php       # Seller registration
├── delivery_register.php     # Delivery partner registration
├── logout.php                # Logout handler
├── style.css                 # Main stylesheet
├── database.sql              # Database schema
└── README.md                 # This file
```

## 🔐 Security Features

- ✅ Password hashing using PHP `password_hash()`
- ✅ Prepared statements to prevent SQL injection
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ Input sanitization
- ✅ CSRF protection (session validation)

## 💡 Usage Guide

### For Customers
1. Register an account at `/register.php`
2. Browse products on homepage
3. Search or filter by category
4. View product details
5. Add items to cart
6. Proceed to checkout
7. Enter delivery address
8. Place order
9. Track order status in "My Orders"

### For Sellers
1. Register at `/seller_register.php` with business details
2. Wait for admin approval
3. Login after approval
4. Add products with images
5. Set prices and stock levels
6. View orders for your products
7. Monitor sales and revenue in dashboard

### For Delivery Partners
1. Register at `/delivery_register.php` with vehicle details
2. Wait for admin approval
3. Login after approval
4. View assigned deliveries
5. Accept delivery requests
6. Update status as you progress
7. Mark as delivered upon completion
8. Track your earnings

### For Admin
1. Login with admin credentials
2. Approve/reject seller and delivery partner registrations
3. Monitor all activities
4. Assign deliveries to available partners
5. Manage users (block if necessary)
6. View comprehensive analytics

## 📊 Database Schema

### Main Tables
- `users` - Customer accounts
- `sellers` - Seller/vendor accounts
- `delivery_partners` - Delivery partner accounts
- `admins` - Admin accounts
- `products` - Product catalog
- `cart` - Shopping cart items
- `orders` - Order records
- `order_items` - Individual items in orders
- `deliveries` - Delivery tracking
- `purchase_history` - For recommendations
- `product_views` - For analytics

## 🎨 Key Features Explained

### Smart Recommendations
The system uses SQL queries to analyze:
- User purchase history
- Product categories
- Co-purchase patterns (frequently bought together)
- Product popularity (views and sales)

### AJAX Shopping Cart
- Add to cart without page reload
- Real-time cart count updates
- Instant quantity updates
- Smooth user experience

### Multi-Vendor Architecture
- Each seller manages their own products
- Sellers only see their orders and revenue
- Admin has full oversight
- Automated commission calculation ready for implementation

### Delivery Management
- Orders automatically create delivery tasks
- Admin assigns to available partners
- Partners update status in real-time
- Customers can track delivery progress

## 🐛 Troubleshooting

### Database Connection Error
- Verify XAMPP MySQL is running
- Check database credentials in `config/database.php`
- Ensure database `ecommerce_marketplace` exists

### Image Upload Issues
- Check `uploads/products/` folder exists
- Verify folder has write permissions
- Check PHP `upload_max_filesize` in php.ini

### Login Issues
- Clear browser cookies and cache
- Verify account status (not blocked)
- For sellers/delivery: ensure admin has approved account

### Page Not Loading
- Verify Apache is running in XAMPP
- Check file paths are correct
- Look for PHP errors in XAMPP logs

## 🔄 Future Enhancements

- Payment gateway integration (PayPal, Stripe, Razorpay)
- Email notifications
- SMS alerts for delivery status
- Advanced product filtering
- Wishlist functionality
- Product reviews and ratings
- Seller performance metrics
- Real-time chat support
- Mobile app integration
- Advanced ML recommendations using Python

## 📝 License

This project is created for educational purposes. Feel free to use and modify as needed.

## 👨‍💻 Support

For issues or questions:
- Check the troubleshooting section
- Review the code comments
- Test with default admin credentials
- Verify database schema is properly imported

## ⚡ Performance Tips

- Enable output buffering in php.ini
- Use MySQL query optimization
- Implement caching for product listings
- Optimize images before upload
- Enable Gzip compression in .htaccess

## 🎯 Testing Checklist

- [ ] Admin can login and access dashboard
- [ ] Sellers can register and get approved
- [ ] Delivery partners can register and get approved  
- [ ] Customers can browse and search products
- [ ] Add to cart works via AJAX
- [ ] Checkout process completes successfully
- [ ] Orders appear in customer's order history
- [ ] Sellers see their product orders
- [ ] Admin can assign deliveries
- [ ] Delivery partners can update status
- [ ] Recommendations display correctly
- [ ] All role-based access controls work

---

**Note**: This is a demonstration project using XAMPP local server. For production deployment, additional security measures, hosting configuration, and optimizations are required.
