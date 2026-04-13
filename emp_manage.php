<?php
require "db.php";
$dbh = connectDB();

if (!isset($_SESSION['employee_id'])) { header("Location: employee_login.php"); exit(); }
$emp_username = htmlspecialchars($_SESSION['emp_username']);
$emp_id = $_SESSION['employee_id'];
$msg = "";

// Handle Restock
if (isset($_POST['update_stock'])) {
    $pid = $_POST['product_id'];
    $new_stock = $_POST['new_stock'];
    
    $old_stmt = $dbh->prepare("SELECT stock_qty FROM PRODUCT WHERE product_id = :pid");
    $old_stmt->execute([':pid' => $pid]);
    $old_stock = $old_stmt->fetchColumn();
    
    $stmt = $dbh->prepare("UPDATE PRODUCT SET stock_qty = :qty WHERE product_id = :pid");
    if($stmt->execute([':qty' => $new_stock, ':pid' => $pid])) {
        // CALL YOUR STORED PROCEDURE
        $hist = $dbh->prepare("CALL log_product_update(:pid, 'UPDATE', NULL, NULL, :old, :new, :emp, NULL, NULL)");
        $hist->execute([':pid' => $pid, ':old' => $old_stock, ':new' => $new_stock, ':emp' => $emp_id]);
        
        $msg = "<p style='color: #28a745; font-weight: bold;'>Stock updated successfully!</p>";
    }
}

// Handle Price Change
if (isset($_POST['update_price'])) {
    $pid = $_POST['product_id'];
    $new_price = $_POST['new_price'];
    
    $old_stmt = $dbh->prepare("SELECT price FROM PRODUCT WHERE product_id = :pid");
    $old_stmt->execute([':pid' => $pid]);
    $old_price = $old_stmt->fetchColumn();
    
    $stmt = $dbh->prepare("UPDATE PRODUCT SET price = :price WHERE product_id = :pid");
    if($stmt->execute([':price' => $new_price, ':pid' => $pid])) {
        // CALL YOUR STORED PROCEDURE
        $hist = $dbh->prepare("CALL log_product_update(:pid, 'UPDATE', :old, :new, NULL, NULL, :emp, NULL, NULL)");
        $hist->execute([':pid' => $pid, ':old' => $old_price, ':new' => $new_price, ':emp' => $emp_id]);
        
        $msg = "<p style='color: #28a745; font-weight: bold;'>Price updated successfully!</p>";
    }
}

$products = $dbh->query("SELECT product_id, name, price, stock_qty FROM PRODUCT")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Inventory - Tech Shop Staff</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .top-nav-links { list-style: none; display: flex; gap: 20px; justify-content: flex-end;}
        .top-nav-links a { font-weight: 600; color: var(--accent-line); }
        .simple-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; box-shadow: var(--card-shadow);}
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
            
            <h2 style="margin-bottom: 20px; text-transform: uppercase;">Manage Products</h2>
            <?php echo $msg; ?>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Current Stock</th>
                        <th>Update Stock</th>
                        <th>Current Price</th>
                        <th>Update Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?php echo $p['product_id']; ?></td>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><strong><?php echo $p['stock_qty']; ?></strong></td>
                        <td>
                            <form method="post" style="display:flex; gap:5px;">
                                <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                                <input type="number" name="new_stock" value="<?php echo $p['stock_qty']; ?>" style="width: 70px;" required>
                                <button type="submit" name="update_stock" class="btn-ombre" style="padding: 5px 10px;">Update</button>
                            </form>
                        </td>
                        <td>$<?php echo number_format($p['price'], 2); ?></td>
                        <td>
                            <form method="post" style="display:flex; gap:5px;">
                                <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                                <input type="number" step="0.01" name="new_price" value="<?php echo $p['price']; ?>" style="width: 80px;" required>
                                <button type="submit" name="update_price" class="btn-ombre" style="padding: 5px 10px;">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>