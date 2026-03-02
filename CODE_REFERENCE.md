# 📖 Multiple Images Feature - Code Reference

## 🎯 Complete Code Examples

### 1. Display Image Gallery in Product Detail Page

#### HTML Structure
```html
<!-- Main Image Display -->
<div class="product-gallery-main mb-3">
    <img src="uploads/products/<?php echo htmlspecialchars($product_images[0]); ?>" 
         class="product-detail-img" id="mainImage" alt="Product">
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
```

#### PHP Backend
```php
<?php
// Get product images from database
$images_query = $conn->prepare("SELECT image_path FROM product_images 
                               WHERE product_id = ? 
                               ORDER BY display_order ASC");
$images_query->bind_param("i", $product_id);
$images_query->execute();
$images_result = $images_query->get_result();

// Fetch all images
$product_images = [];
while ($img = $images_result->fetch_assoc()) {
    $product_images[] = $img['image_path'];
}

// Fallback to old image column if needed
if (empty($product_images) && $product['image']) {
    $product_images[] = $product['image'];
}

// Use default if no images found
if (empty($product_images)) {
    $product_images[] = 'default.jpg';
}
?>
```

#### JavaScript
```javascript
// Change main image when thumbnail clicked
function changeImage(imageSrc, element) {
    // Update main image source
    document.getElementById('mainImage').src = imageSrc;
    
    // Remove active class from all thumbnails
    document.querySelectorAll('.thumbnail-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to clicked thumbnail
    element.classList.add('active');
}
```

---

### 2. Multiple Image Upload in Seller Panel

#### Form HTML
```html
<form method="POST" enctype="multipart/form-data">
    <div class="modal-body">
        <input type="hidden" name="action" value="add">
        
        <!-- ... other form fields ... -->
        
        <!-- Primary Image -->
        <div class="col-md-4">
            <div class="mb-3">
                <label>Product Image (Primary) *</label>
                <input type="file" name="image" class="form-control" 
                       accept="image/*" required>
                <small class="text-muted">This will be the main product image</small>
            </div>
        </div>
        
        <!-- Multiple Images -->
        <div class="mb-3">
            <label>Add More Images (Optional)</label>
            <input type="file" name="images[]" class="form-control" 
                   accept="image/*" multiple>
            <small class="text-muted">
                Select multiple images at once using Ctrl/Cmd + Click
            </small>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Close
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Add Product
        </button>
    </div>
</form>
```

#### PHP Backend - Add Product
```php
<?php
if ($_POST['action'] == 'add') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category = sanitize($_POST['category']);
    
    // Handle primary image
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/products/";
        $image = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image);
    }
    
    // Insert product
    $stmt = $conn->prepare("INSERT INTO products 
                           (seller_id, name, description, price, stock, category, image) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdiis", $seller_id, $name, $description, $price, 
                      $stock, $category, $image);
    
    if ($stmt->execute()) {
        $product_id = $conn->insert_id;
        
        // Handle multiple images
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $target_dir = "../uploads/products/";
            $is_primary = 1;
            $display_order = 0;
            
            // Process each uploaded image
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] == 0) {
                    $filename = time() . '_' . $key . '_' . 
                               basename($_FILES['images']['name'][$key]);
                    
                    // Move file to uploads directory
                    if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
                        // Insert image record in database
                        $img_stmt = $conn->prepare("INSERT INTO product_images 
                                                   (product_id, image_path, is_primary, display_order) 
                                                   VALUES (?, ?, ?, ?)");
                        $img_stmt->bind_param("isii", $product_id, $filename, 
                                            $is_primary, $display_order);
                        $img_stmt->execute();
                        
                        // After first image, set is_primary to 0
                        $is_primary = 0;
                        $display_order++;
                    }
                }
            }
        }
        
        echo "<div class='alert alert-success'>Product added successfully!</div>";
    }
}
?>
```

#### PHP Backend - Edit Product
```php
<?php
if ($_POST['action'] == 'edit') {
    $product_id = intval($_POST['product_id']);
    $name = sanitize($_POST['name']);
    // ... get other fields ...
    
    // Update product basic info
    $stmt = $conn->prepare("UPDATE products 
                           SET name=?, description=?, price=?, stock=?, category=?, status=? 
                           WHERE product_id=? AND seller_id=?");
    $stmt->bind_param("ssdisii", $name, $description, $price, $stock, 
                      $category, $status, $product_id, $seller_id);
    $stmt->execute();
    
    // Handle additional images (append, not replace)
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $target_dir = "../uploads/products/";
        
        // Get current max order to append new images
        $max_order_result = $conn->query("SELECT MAX(display_order) as max_order 
                                         FROM product_images 
                                         WHERE product_id = $product_id");
        $max_order_row = $max_order_result->fetch_assoc();
        $display_order = ($max_order_row['max_order'] ?? -1) + 1;
        
        // Process new images
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] == 0) {
                $filename = time() . '_' . $key . '_' . 
                           basename($_FILES['images']['name'][$key]);
                
                if (move_uploaded_file($tmp_name, $target_dir . $filename)) {
                    // Insert new image record
                    $img_stmt = $conn->prepare("INSERT INTO product_images 
                                               (product_id, image_path, is_primary, display_order) 
                                               VALUES (?, ?, 0, ?)");
                    $img_stmt->bind_param("isi", $product_id, $filename, $display_order);
                    $img_stmt->execute();
                    $display_order++;
                }
            }
        }
    }
}
?>
```

---

### 3. CSS Styling for Gallery

```css
/* Product Image Gallery Styles */

/* Main Image Container */
.product-gallery-main {
    width: 100%;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Main Product Image */
.product-detail-img {
    width: 100%;
    height: auto;
    max-height: 500px;
    object-fit: contain;
    background-color: #f8f9fa;
    transition: transform 0.3s ease;
    cursor: pointer;
}

/* Hover zoom effect */
.product-detail-img:hover {
    transform: scale(1.02);
}

/* Thumbnail Gallery Container */
.product-gallery-thumbnails {
    margin-top: 15px;
}

/* Individual Thumbnail Item */
.thumbnail-item {
    cursor: pointer;
    border: 3px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Thumbnail hover state */
.thumbnail-item:hover {
    border-color: #FF9900;  /* Primary color */
    box-shadow: 0 2px 8px rgba(255, 153, 0, 0.2);
}

/* Thumbnail active state */
.thumbnail-item.active {
    border-color: #FF9900;
    box-shadow: 0 0 10px rgba(255, 153, 0, 0.5);
}

/* Thumbnail image styling */
.thumbnail-item img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* Image count badge (optional) */
.image-count-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

/* Responsive Design */
@media (max-width: 768px) {
    .product-detail-img {
        max-height: 400px;
    }
    
    .thumbnail-item {
        height: 80px;
    }
}

@media (max-width: 576px) {
    .product-detail-img {
        max-height: 300px;
    }
    
    .thumbnail-item {
        height: 70px;
    }
}
```

---

### 4. Useful SQL Queries

#### Get All Images for a Product
```sql
SELECT * FROM product_images 
WHERE product_id = ? 
ORDER BY display_order ASC;
```

#### Get Products with Image Galleries
```sql
SELECT 
    p.product_id,
    p.name,
    COUNT(pi.image_id) as image_count,
    GROUP_CONCAT(pi.image_path ORDER BY pi.display_order SEPARATOR '|') as all_images
FROM products p
LEFT JOIN product_images pi ON p.product_id = pi.product_id
GROUP BY p.product_id, p.name
ORDER BY p.product_id DESC;
```

#### Find Products Without Images
```sql
SELECT p.product_id, p.name, p.seller_id 
FROM products p
WHERE p.product_id NOT IN (SELECT DISTINCT product_id FROM product_images)
ORDER BY p.product_id DESC;
```

#### Get Image Statistics
```sql
SELECT 
    p.name,
    COUNT(pi.image_id) as total_images,
    MAX(pi.uploaded_at) as last_upload
FROM products p
LEFT JOIN product_images pi ON p.product_id = pi.product_id
GROUP BY p.product_id, p.name
HAVING COUNT(pi.image_id) > 0
ORDER BY COUNT(pi.image_id) DESC;
```

#### Update Image Display Order
```sql
UPDATE product_images 
SET display_order = ? 
WHERE image_id = ?;
```

---

### 5. Integration Points

#### In Your PHP Functions File
```php
<?php
// Add to includes/functions.php

/**
 * Get all images for a product
 * @param PDO $conn Database connection
 * @param int $product_id Product ID
 * @return array Array of image paths
 */
function getProductImages($conn, $product_id) {
    $stmt = $conn->prepare("SELECT image_path FROM product_images 
                           WHERE product_id = ? 
                           ORDER BY display_order ASC");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    
    $images = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $images[] = $row['image_path'];
    }
    
    return $images;
}

/**
 * Get image count for a product
 * @param PDO $conn Database connection
 * @param int $product_id Product ID
 * @return int Number of images
 */
function getImageCount($conn, $product_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM product_images 
                           WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}

/**
 * Delete image from product
 * @param PDO $conn Database connection
 * @param int $image_id Image ID
 * @return bool Success
 */
function deleteProductImage($conn, $image_id) {
    $stmt = $conn->prepare("DELETE FROM product_images WHERE image_id = ?");
    $stmt->bind_param("i", $image_id);
    return $stmt->execute();
}
?>
```

---

## 🎓 Example Usage

### In a Product Listing Page
```php
<?php
$products = $conn->query("SELECT * FROM products LIMIT 20");

while ($product = $products->fetch_assoc()) {
    $images = getProductImages($conn, $product['product_id']);
    $main_image = !empty($images) ? $images[0] : 'default.jpg';
    $image_count = count($images);
    
    echo "
    <div class='product-card'>
        <img src='uploads/products/$main_image' alt='{$product['name']}'>
        <h4>{$product['name']}</h4>
        <p>₹{$product['price']}</p>";
    
    if ($image_count > 1) {
        echo "<span class='badge bg-primary'>+$image_count images</span>";
    }
    
    echo "</div>";
}
?>
```

---

## 📝 Notes

- Always escape output with `htmlspecialchars()`
- Use prepared statements to prevent SQL injection
- Validate file uploads on the server side
- Consider adding image size limits
- Implement proper error handling
- Test with various image formats
- Monitor disk space usage

---

**Happy coding!** 🚀
