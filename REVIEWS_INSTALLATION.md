# Product Reviews & Ratings - Installation Guide

## ✅ Files Created

### Database
- `database_reviews.sql` - Database schema for reviews system

### Backend Files
- `includes/review_functions.php` - Helper functions for reviews
- `ajax/submit_review.php` - Handle review submission
- `ajax/vote_review.php` - Handle review voting

### Frontend Files
- `includes/product_reviews_section.php` - Review UI component
- `js/reviews.js` - JavaScript for review interactions
- CSS styles added to `style.css`

---

## 📋 Installation Steps

### Step 1: Create Database Tables
Run the SQL file to create the required tables:

```bash
# Option 1: Via phpMyAdmin
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select your ecommerce database
3. Go to "Import" tab
4. Choose file: database_reviews.sql
5. Click "Go"

# Option 2: Via command line
mysql -u root -p ecommerce < database_reviews.sql
```

### Step 2: Update product_detail.php
Add this line before the closing `</body>` tag (around line 302):

```php
<?php include 'includes/product_reviews_section.php'; ?>
```

### Step 3: Create uploads directory
Create a directory for review images:

```bash
mkdir uploads/reviews
chmod 777 uploads/reviews
```

Or create manually:
- Create folder: `d:\xampp\htdocs\ecommerce\uploads\reviews`

---

## 🎯 Features Included

✅ **Star Rating System** - 1-5 star ratings with visual feedback
✅ **Review Submission** - Title, text, and image uploads
✅ **Verified Purchase Badge** - Shows if reviewer bought the product
✅ **Helpful Voting** - Users can vote reviews helpful/not helpful
✅ **Rating Distribution** - Visual bars showing rating breakdown
✅ **Average Rating** - Calculated and displayed prominently
✅ **Review Images** - Upload up to 5 images per review
✅ **Responsive Design** - Works on all devices
✅ **Modern UI** - Gradient styling matching your theme

---

## 🧪 Testing

1. **Login as a user** who has purchased a product
2. **Go to product detail page**
3. **Scroll to reviews section**
4. **Submit a review** with rating and text
5. **Upload images** (optional)
6. **Vote on reviews** (helpful/not helpful)
7. **Check rating summary** updates correctly

---

## 🎨 Customization

### Change Star Color
Edit in `style.css`:
```css
.star-rating i { color: #fbbf24; } /* Change this color */
```

### Adjust Review Card Style
Edit `.review-card` class in `style.css`

### Modify Rating Distribution Colors
Edit `.rating-bar-fill` background in `style.css`

---

## 🐛 Troubleshooting

**Reviews not submitting?**
- Check database connection
- Verify tables were created
- Check browser console for errors

**Images not uploading?**
- Ensure `uploads/reviews` folder exists
- Check folder permissions (777)
- Verify file size limits in php.ini

**Ratings not updating?**
- Check `updateProductRating()` function is called
- Verify `average_rating` column exists in products table

---

## 📊 Database Structure

**reviews table:**
- review_id, product_id, user_id, rating, title, review_text
- verified_purchase, helpful_count, created_at

**review_images table:**
- image_id, review_id, image_path, created_at

**review_votes table:**
- vote_id, review_id, user_id, vote_type, created_at

**products table (updated):**
- average_rating, review_count (new columns)

---

## ✨ Next Steps

After installation, the review system will be fully functional! Users can:
- View product ratings at a glance
- Read detailed customer reviews
- Submit their own reviews with photos
- Vote on helpful reviews
- See verified purchase badges

The system automatically calculates and updates average ratings whenever new reviews are submitted.
