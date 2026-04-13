<?php
require "db.php";
$dbh = connectDB();

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit();
}

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $hashed_password = hash('sha256', $password);

    $stmt = $dbh->prepare("SELECT customer_id, username, password_hash FROM CUSTOMER WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && $user['password_hash'] === $hashed_password) {
        $_SESSION['customer_id'] = $user['customer_id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit();
    } else {
        $error_msg = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Tech Shop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main-content" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg-light); padding: 0;">
        <div class="container" style="text-align: center; width: 100%;">
            <div class="site-logo" style="margin-bottom: 40px; font-size: 2rem;"><a href="login.php">Tech Shop</a></div>
            
            <div class="form-card">
                <h2 style="margin-bottom: 30px; text-transform: uppercase;">Customer Login</h2>
                
                <?php if(!empty($error_msg)): ?>
                    <p style='color: #dc3545; font-weight:bold; margin-bottom:20px;'><?php echo $error_msg; ?></p>
                <?php endif; ?>

                <form method="post" action="login.php">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" class="btn-ombre" style="margin-top: 15px;">Log in</button>
                </form>
                
                <p style="text-align:center; margin-top:25px; color:var(--text-muted);">
                    Don't have an account? <a href="register.php" style="color:var(--accent-line); font-weight:600;">Register here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>