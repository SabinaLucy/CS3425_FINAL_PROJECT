<?php
require "db.php";
$dbh = connectDB();

if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}

$msg = "";
$emp_id = $_SESSION['employee_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass === $confirm_pass) {
        $new_hash = hash('sha256', $new_pass);
        
        // Update password and clear the requires_password_reset flag
        $update = $dbh->prepare("UPDATE EMPLOYEE SET password_hash = :hash, requires_password_reset = 0 WHERE employee_id = :id");
        $update->execute([':hash' => $new_hash, ':id' => $emp_id]);
        
        header("Location: employee_index.php");
        exit();
    } else {
        $msg = "<p style='color: #dc3545; font-weight:bold; margin-bottom: 20px; text-align: center;'>Passwords do not match.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Tech Shop Staff</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="main-content" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg-light);">
        <div class="form-card" style="width: 100%; max-width: 400px;">
            <h2 style="text-align: center; margin-bottom: 20px; color: var(--text-main);">Required Password Reset</h2>
            <p style="text-align: center; color: var(--text-muted); margin-bottom: 20px;">For security reasons, you must change your default password.</p>
            <?php echo $msg; ?>
            <form method="post" action="emp_reset_password.php">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-ombre" style="width: 100%; margin-top: 10px;">Update & Continue</button>
            </form>
        </div>
    </div>
</body>
</html>