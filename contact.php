<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
$contact = ['contact_email' => 'contact@marketplace.com', 'contact_phone' => '', 'contact_address' => '', 'contact_hours' => ''];
if ($conn->query("SHOW TABLES LIKE 'site_settings'")->num_rows > 0) {
    $r = $conn->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('contact_email','contact_phone','contact_address','contact_hours')");
    while ($r && $row = $r->fetch_assoc()) $contact[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MarketPlace</title>
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
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
            </ul>
        </div>
    </nav>
    <div class="container my-5">
        <h1 class="mb-4">Contact Us</h1>
        <p><i class="fas fa-envelope"></i> <a href="mailto:<?php echo htmlspecialchars($contact['contact_email']); ?>"><?php echo htmlspecialchars($contact['contact_email']); ?></a></p>
        <?php if (!empty($contact['contact_phone'])): ?><p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact['contact_phone']); ?></p><?php endif; ?>
        <?php if (!empty($contact['contact_address'])): ?><p><i class="fas fa-map-marker-alt"></i> <?php echo nl2br(htmlspecialchars($contact['contact_address'])); ?></p><?php endif; ?>
        <?php if (!empty($contact['contact_hours'])): ?><p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($contact['contact_hours']); ?></p><?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
