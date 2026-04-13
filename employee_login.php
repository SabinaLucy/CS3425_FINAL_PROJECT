<?php
require "db.php";
$dbh = connectDB();

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: employee_login.php");
    exit();
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $hashed_password = hash('sha256', $password);

    $stmt = $dbh->prepare("SELECT * FROM EMPLOYEE WHERE username = :username AND password_hash = :password");
    $stmt->execute([':username' => $username, ':password' => $hashed_password]);
    $employee = $stmt->fetch();

    if ($employee) {
        $_SESSION['employee_id'] = $employee['employee_id']; 
        $_SESSION['emp_username'] = $employee['username'];
        
        // Check the database's specific column for password resets
        if ($employee['requires_password_reset'] == 1) {
            header("Location: emp_reset_password.php");
            exit();
        } else {
            header("Location: employee_index.php");
            exit();
        }
    } else {
        $msg = "<p style='color: #dc3545; text-align: center; margin-bottom: 15px; font-weight: bold;'>Invalid employee credentials.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Login - Tech Shop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main-content" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg-light);">
        <div class="form-card" style="width: 100%; max-width: 400px;">
            <div class="site-logo" style="text-align: center; margin-bottom: 10px; font-size: 2rem; font-weight: bold; color: var(--accent-line);">Tech Shop</div>
            
            <h2 style="text-align: center; margin-bottom: 20px; color: var(--text-main); text-transform: uppercase;">Staff Login</h2>
            <?php echo $msg; ?>
            <form method="post" action="employee_login.php">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-ombre" style="width: 100%; margin-top: 10px;">Login</button>
            </form>
            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" style="color: var(--text-muted); font-size: 0.9rem;">Return to Customer Login</a>
            </div>
        </div>
    </div>
</body>
</html>