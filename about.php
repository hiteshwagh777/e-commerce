<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
$about = 'Your trusted online marketplace connecting buyers with quality sellers.';
if ($conn->query("SHOW TABLES LIKE 'site_settings'")->num_rows > 0) {
    $r = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key = 'about_us'");
    if ($r && $row = $r->fetch_assoc()) $about = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - MarketPlace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-shopping-bag"></i> MarketPlace</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
            </ul>
        </div>
    </nav>
    <div class="container my-5">
        <h1 class="mb-4">About Us</h1>
        <p class="lead"><?php echo nl2br(htmlspecialchars($about)); ?></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
