<?php
require "db.php";
$dbh = connectDB();

$error_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $fname = trim($_POST['first_name']);
    $lname = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['shipping_address']);
    $password = $_POST['password'];

    $hashed_password = hash('sha256', $password);

    try {
        $stmt = $dbh->prepare("INSERT INTO CUSTOMER (username, first_name, last_name, email, shipping_address, password_hash) VALUES (:username, :fname, :lname, :email, :address, :password_hash)");
        $stmt->execute([
            ':username' => $username,
            ':fname' => $fname,
            ':lname' => $lname,
            ':email' => $email,
            ':address' => $address,
            ':password_hash' => $hashed_password
        ]);
        $success_msg = "Registration successful! You can now <a href='login.php' style='text-decoration:underline;'>Login here</a>.";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error_msg = "Error: Username or Email already exists.";
        } else {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Tech Shop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main-content" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg-light); padding: 40px 0;">
        <div class="container" style="text-align: center; width: 100%;">
            <div class="site-logo" style="margin-bottom: 30px; font-size: 2rem;"><a href="login.php">Tech Shop</a></div>
            
            <div class="form-card">
                <h2 style="margin-bottom: 30px; text-transform: uppercase;">Create an Account</h2>
                
                <?php if(!empty($error_msg)) echo "<p style='color: #dc3545; font-weight:bold; margin-bottom:20px;'>$error_msg</p>"; ?>
                <?php if(!empty($success_msg)) echo "<p style='color: #28a745; font-weight:bold; margin-bottom:20px;'>$success_msg</p>"; ?>

                <form method="post" action="register.php">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>

                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label>First Name</label>
                            <input type="text" name="first_name" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Last Name</label>
                            <input type="text" name="last_name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Shipping Address</label>
                        <input type="text" name="shipping_address" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <button type="submit" class="btn-ombre" style="margin-top: 10px;">Register</button>
                </form>
                
                <p style="text-align:center; margin-top:20px; color:var(--text-muted);">
                    Already have an account? <a href="login.php" style="color:var(--accent-line); font-weight:600;">Log in</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>