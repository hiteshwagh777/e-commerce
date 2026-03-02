<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$role = isset($_GET['role']) ? $_GET['role'] : 'customer';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $login_role = $_POST['role'];
    
    if (empty($email) || empty($password)) {
        $error = "Email and password are required!";
    } else {
        $table = '';
        $id_field = '';
        $redirect = '';
        
        switch($login_role) {
            case 'admin':
                $stmt = $conn->prepare("SELECT admin_id, username, password FROM admins WHERE username = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $admin = $result->fetch_assoc();
                    if (password_verify($password, $admin['password'])) {
                        $_SESSION['admin_id'] = $admin['admin_id'];
                        $_SESSION['admin_name'] = $admin['username'];
                        header("Location: admin/dashboard.php");
                        exit();
                    } else {
                        $error = "Invalid credentials!";
                    }
                } else {
                    $error = "Admin not found!";
                }
                break;
                
            case 'seller':
                $stmt = $conn->prepare("SELECT seller_id, name, password, status FROM sellers WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $seller = $result->fetch_assoc();
                    if ($seller['status'] == 'blocked') {
                        $error = "Your account has been blocked!";
                    } elseif ($seller['status'] != 'approved') {
                        $error = "Your account is pending approval!";
                    } elseif (password_verify($password, $seller['password'])) {
                        $_SESSION['seller_id'] = $seller['seller_id'];
                        $_SESSION['seller_name'] = $seller['name'];
                        header("Location: seller/dashboard.php");
                        exit();
                    } else {
                        $error = "Invalid credentials!";
                    }
                } else {
                    $error = "Seller not found!";
                }
                break;
                
            case 'delivery':
                $stmt = $conn->prepare("SELECT delivery_id, name, password, status FROM delivery_partners WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $delivery = $result->fetch_assoc();
                    if ($delivery['status'] == 'blocked') {
                        $error = "Your account has been blocked!";
                    } elseif ($delivery['status'] != 'approved') {
                        $error = "Your account is pending approval!";
                    } elseif (password_verify($password, $delivery['password'])) {
                        $_SESSION['delivery_id'] = $delivery['delivery_id'];
                        $_SESSION['delivery_name'] = $delivery['name'];
                        header("Location: delivery/dashboard.php");
                        exit();
                    } else {
                        $error = "Invalid credentials!";
                    }
                } else {
                    $error = "Delivery partner not found!";
                }
                break;
                
            default: // customer
                $stmt = $conn->prepare("SELECT user_id, name, password, status FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    if ($user['status'] == 'blocked') {
                        $error = "Your account has been blocked!";
                    } elseif (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['user_name'] = $user['name'];
                        header("Location: index.php");
                        exit();
                    } else {
                        $error = "Invalid credentials!";
                    }
                } else {
                    $error = "User not found!";
                }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Commerce Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <ul class="nav nav-pills nav-fill mb-4">
                <li class="nav-item">
                    <a class="nav-link <?php echo $role == 'customer' ? 'active' : ''; ?>" href="?role=customer">Customer</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $role == 'seller' ? 'active' : ''; ?>" href="?role=seller">Seller</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $role == 'delivery' ? 'active' : ''; ?>" href="?role=delivery">Delivery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $role == 'admin' ? 'active' : ''; ?>" href="?role=admin">Admin</a>
                </li>
            </ul>
            
            <form method="POST" action="">
                <input type="hidden" name="role" value="<?php echo $role; ?>">
                
                <div class="form-group">
                    <label><?php echo $role == 'admin' ? 'Username' : 'Email Address'; ?></label>
                    <input type="<?php echo $role == 'admin' ? 'text' : 'email'; ?>" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt"></i> Login as <?php echo ucfirst($role); ?>
                </button>
            </form>
            
            <?php if ($role != 'admin'): ?>
            <div class="text-center mt-3">
                <p>Don't have an account?</p>
                <?php if ($role == 'customer'): ?>
                    <a href="register.php" class="btn btn-secondary">Register as Customer</a>
                <?php elseif ($role == 'seller'): ?>
                    <a href="seller_register.php" class="btn btn-secondary">Register as Seller</a>
                <?php else: ?>
                    <a href="delivery_register.php" class="btn btn-secondary">Register as Delivery Partner</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($role == 'admin'): ?>
            <div class="alert alert-info mt-3">
                <strong>Demo Admin Credentials:</strong><br>
                Username: admin<br>
                Password: admin123
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
