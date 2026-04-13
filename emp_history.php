<?php
require "db.php";
$dbh = connectDB();

if (!isset($_SESSION['employee_id'])) { header("Location: employee_login.php"); exit(); }
$emp_username = htmlspecialchars($_SESSION['emp_username']);

// Fetch Stock History from unified PRODUCT_HISTORY table
$stock_history = $dbh->query("
    SELECT h.*, p.name 
    FROM PRODUCT_HISTORY h 
    JOIN PRODUCT p ON h.product_id = p.product_id 
    WHERE h.old_stock IS NOT NULL AND h.new_stock IS NOT NULL
    ORDER BY h.timestamp DESC
")->fetchAll();

// Fetch Price History from unified PRODUCT_HISTORY table
$price_history = $dbh->query("
    SELECT h.*, p.name 
    FROM PRODUCT_HISTORY h 
    JOIN PRODUCT p ON h.product_id = p.product_id 
    WHERE h.old_price IS NOT NULL AND h.new_price IS NOT NULL
    ORDER BY h.timestamp DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Histories - Tech Shop Staff</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .top-nav-links { list-style: none; display: flex; gap: 20px; justify-content: flex-end;}
        .top-nav-links a { font-weight: 600; color: var(--accent-line); }
        .simple-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; background: #fff; box-shadow: var(--card-shadow);}
        .simple-table th, .simple-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .simple-table th { background: #f8f9fa; }
    </style>
</head>
<body>
    <header>
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
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
            <a href="employee_index.php" class="btn-ombre" style="display: inline-block; margin-bottom: 20px; padding: 8px 15px; text-decoration: none;">&larr; Back to Dashboard</a>
            
            <h2 style="margin-bottom: 20px; text-transform: uppercase;">Price History</h2>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Old Price</th>
                        <th>New Price</th>
                        <th>% Change</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($price_history as $ph): 
                        $pct_change = (($ph['new_price'] - $ph['old_price']) / $ph['old_price']) * 100;
                        $color = $pct_change > 0 ? "color: #dc3545;" : "color: #28a745;";
                    ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($ph['timestamp'])); ?></td>
                        <td><?php echo htmlspecialchars($ph['name']); ?> (ID: <?php echo $ph['product_id']; ?>)</td>
                        <td>$<?php echo number_format($ph['old_price'], 2); ?></td>
                        <td>$<?php echo number_format($ph['new_price'], 2); ?></td>
                        <td style="font-weight:bold; <?php echo $color; ?>">
                            <?php echo number_format($pct_change, 2); ?>%
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($price_history) == 0): ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No price changes recorded.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h2 style="margin-bottom: 20px; text-transform: uppercase;">Stock Update History</h2>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Old Quantity</th>
                        <th>New Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stock_history as $sh): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($sh['timestamp'])); ?></td>
                        <td><?php echo htmlspecialchars($sh['name']); ?> (ID: <?php echo $sh['product_id']; ?>)</td>
                        <td><?php echo $sh['old_stock']; ?></td>
                        <td><?php echo $sh['new_stock']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($stock_history) == 0): ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--text-muted);">No stock changes recorded.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>
</body>
</html>