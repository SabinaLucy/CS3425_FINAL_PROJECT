<?php
require "db.php";
$dbh = connectDB();

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$msg = "";
$username = htmlspecialchars($_SESSION['username']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $stmt = $dbh->prepare("SELECT password_hash FROM CUSTOMER WHERE customer_id = :id");
    $stmt->execute([':id' => $_SESSION['customer_id']]);
    $user = $stmt->fetch();

    if ($user && hash('sha256', $current_pass) === $user['password_hash']) {
        if ($new_pass === $confirm_pass) {
            $new_hash = hash('sha256', $new_pass);
            $update = $dbh->prepare("UPDATE CUSTOMER SET password_hash = :hash WHERE customer_id = :id");
            $update->execute([':hash' => $new_hash, ':id' => $_SESSION['customer_id']]);
            $msg = "<p style='color: #28a745; font-weight:bold; margin-bottom: 20px;'>Password updated successfully!</p>";
        } else {
            $msg = "<p style='color: #dc3545; font-weight:bold; margin-bottom: 20px;'>New passwords do not match.</p>";
        }
    } else {
        $msg = "<p style='color: #dc3545; font-weight:bold; margin-bottom: 20px;'>Incorrect current password.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password -Tech Shop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .top-nav-links { list-style: none; display: flex; gap: 20px; flex-wrap: wrap; justify-content: flex-end;}
        .top-nav-links a { font-weight: 600; color: var(--accent-line); font-size: 0.95rem; }
        .top-nav-links a:hover { color: #2575fc; }
    </style>
</head>
<body>
    <header>
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div class="site-logo"><a href="index.php">Tech Shop</a></div>
            
            <div style="text-align: right;">
                <h3 style="margin-bottom: 10px; color: var(--text-main);">Welcome <span style="color: var(--accent-line);"><?php echo $username; ?></span> !!</h3>
                <ul class="top-nav-links">
                    <li><a href="orders.php">View Orders</a></li>
                    <li><a href="cart.php">Shopping Cart</a></li>
                    <li><a href="change_password.php">Change Password</a></li>
                    <li><a href="login.php?action=logout" style="color: #dc3545;">Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="container">
            <a href="index.php" class="btn-back">Back to Shop</a>
            
            <div class="form-card">
                <h2 style="margin-bottom: 20px; text-transform: uppercase;">Change Password</h2>
                <?php echo $msg; ?>
                
                <form method="post" action="change_password.php" style="margin-top: 20px;">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn-ombre" style="margin-top: 15px;">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>