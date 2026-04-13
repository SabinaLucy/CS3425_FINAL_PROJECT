<?php
require "db.php";
$dbh = connectDB();

if (!isset($_SESSION['employee_id'])) {
    header("Location: employee_login.php");
    exit();
}
$emp_username = htmlspecialchars($_SESSION['emp_username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard - Tech Shop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .top-nav-links { list-style: none; display: flex; gap: 20px; flex-wrap: wrap; justify-content: flex-end;}
        .top-nav-links a { font-weight: 600; color: var(--accent-line); font-size: 0.95rem; }
        .top-nav-links a:hover { color: #2575fc; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px; }
        .dash-card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: var(--card-shadow); text-align: center; border-top: 4px solid var(--accent-line); }
    </style>
</head>
<body>
    <header>
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div class="site-logo"><a href="employee_index.php">Tech Shop (Staff)</a></div>
            <div style="text-align: right;">
                <h3 style="margin-bottom: 10px; color: var(--text-main);">Welcome Staff <span style="color: var(--accent-line);"><?php echo $emp_username; ?></span> !!</h3>
                <ul class="top-nav-links">
                    <li><a href="employee_index.php">Dashboard</a></li>
                    <li><a href="emp_manage.php">Manage Inventory</a></li>
                    <li><a href="emp_history.php">View Histories</a></li>
                    <li><a href="employee_login.php?action=logout" style="color: #dc3545;">Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="container">
            <h2 style="text-transform: uppercase;">Employee Control Panel</h2>
            <div class="dashboard-grid">
                <div class="dash-card">
                    <h3>Manage Products</h3>
                    <p style="color: var(--text-muted); margin-bottom: 15px;">Update product stock quantities and change prices.</p>
                    <a href="emp_manage.php" class="btn-ombre" style="display: inline-block; padding: 10px 20px;">Manage Inventory</a>
                </div>
                <div class="dash-card">
                    <h3>View Histories</h3>
                    <p style="color: var(--text-muted); margin-bottom: 15px;">View logs for stock quantity changes and price adjustments.</p>
                    <a href="emp_history.php" class="btn-ombre" style="display: inline-block; padding: 10px 20px;">View Logs</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>