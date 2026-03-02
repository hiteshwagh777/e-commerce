# Multiple Images Feature Setup Guide

## 📋 Overview
This guide will help you add multiple product images support to your ecommerce website. Your products can now have multiple images with a beautiful gallery interface.

## 📸 Features Added
✅ **Product Gallery** - Display multiple product images  
✅ **Image Thumbnails** - Click thumbnails to change main image  
✅ **Responsive Design** - Works on all devices  
✅ **Backward Compatible** - Works with existing single image products  
✅ **Easy Upload** - Sellers can upload multiple images at once  

---

## 🚀 Installation Steps

### Step 1: Run Database Migration
Execute the SQL migration script to create the `product_images` table:

1. Open **phpMyAdmin** (usually at `http://localhost/phpmyadmin`)
2. Select your `ecommerce_marketplace` database
3. Go to **SQL** tab
4. Copy and paste the contents of `database_multi_image.sql`
5. Click **Execute**

**Alternatively**, run it via command line:
```bash
mysql -u root -p ecommerce_marketplace < database_multi_image.sql
```

### Step 2: Verify Files Were Updated
The following files have been automatically updated:

- ✅ `seller/products.php` - Multi-image upload form
- ✅ `product_detail.php` - Image gallery display
- ✅ `style.css` - Gallery styling
- ✅ `database_multi_image.sql` - Database schema

---

## 💡 How It Works

### For Sellers (Adding Products)
1. Go to **Seller Dashboard → My Products**
2. Click **Add New Product**
3. Fill in product details
4. Upload **Primary Image** in "Product Image" field
5. *(Optional)* Upload **Multiple Images** in "Add More Images" field
   - You can select multiple images at once (Ctrl+Click or Shift+Click)
6. Click **Add Product**

### For Customers (Viewing Products)
1. Browse to any product detail page
2. **Main image** displays at the top
3. **Thumbnails** appear below (if multiple images exist)
4. Click any thumbnail to switch the main image
5. Gallery responds smoothly with hover effects

---

## 🗄️ Database Schema

### New Table: `product_images`
```sql
CREATE TABLE product_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);
```

**Fields:**
- `image_id` - Unique identifier
- `product_id` - Links to products table
- `image_path` - Filename of uploaded image
- `is_primary` - Marks primary image (for future features)
- `display_order` - Controls image order in gallery
- `uploaded_at` - Timestamp of upload

---

## 🎨 Styling

The gallery comes with modern styling:
- **Product Image**: Smooth hover zoom effect
- **Thumbnails**: Border highlights on hover
- **Active Thumbnail**: Golden border indicating selected image
- **Responsive**: Adapts to all screen sizes

All styles are in `style.css` under the "Product Image Gallery" section.

---

## 📝 API Reference

### Frontend JavaScript Functions

#### Change Product Image
```javascript
changeImage(imageSrc, element)
```
- **imageSrc**: Full path to image
- **element**: Thumbnail DOM element
- **Usage**: Called automatically when clicking thumbnails

---

## ✅ Testing Checklist

After installation, test these features:

- [ ] Add a product with single image
- [ ] Add a product with multiple images (5+ images)
- [ ] View product detail page with gallery
- [ ] Click thumbnails to switch main image
- [ ] Check hover effects work smoothly
- [ ] Test on mobile device
- [ ] Edit product and add more images
- [ ] Verify old products still display images
- [ ] Check image optimization (no broken images)

---

## 🔧 Troubleshooting

### Issue: "product_images" table doesn't exist
**Solution**: Run the SQL migration from Step 1

### Issue: Images not uploading
**Solution**: 
- Check `uploads/products/` folder permissions (should be 755)
- Verify PHP max upload size in `php.ini`
- Check server error logs

### Issue: Old products show no images
**Solution**: The migration script copies existing images to `product_images` table. If not working:
```sql
INSERT INTO product_images (product_id, image_path, is_primary, display_order)
SELECT product_id, image, 1, 0 FROM products WHERE image IS NOT NULL;
```

### Issue: Gallery not displaying
**Solution**:
- Clear browser cache (Ctrl+Shift+Delete)
- Verify `style.css` is loaded (check browser DevTools)
- Check image file permissions in uploads folder

---

## 📱 Browser Support
- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

---

## 🎯 Future Enhancement Ideas

Potential features to add later:
- [ ] Drag-and-drop image reordering
- [ ] Image compression on upload
- [ ] Lazy loading for thumbnails
- [ ] Lightbox/modal view for full-size images
- [ ] Image zoom with pan functionality
- [ ] Delete individual images from gallery
- [ ] Automatic thumbnail generation

---

## 📞 Support
If you encounter issues:
1. Check the troubleshooting section above
2. Verify all files are in correct locations
3. Check browser console (F12) for JavaScript errors
4. Review server error logs
5. Ensure database migration completed successfully

---

## 🔐 Security Notes
- All file uploads are validated with `accept="image/*"` attribute
- Filenames are timestamped and sanitized
- Consider adding:
  - MIME type validation on server
  - File size limits
  - Image type verification
  - Virus scanning for large deployments

---

Happy selling with beautiful product images! 🎉
