<?php
require "db.php";
$dbh = connectDB();

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$username = htmlspecialchars($_SESSION['username']); // Added username for the header

// Fetch all orders for this customer, newest first
$stmt = $dbh->prepare("SELECT order_id, order_date, total_amount FROM ORDERS WHERE customer_id = :cid ORDER BY order_date DESC");
$stmt->execute([':cid' => $customer_id]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Orders - Tech Shop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Only added styles for the new top navigation */
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
            <a href="index.php" class="btn-back">Continue Shopping</a>
            
            <h2 style="margin-bottom: 40px; text-transform: uppercase;">Your Order History</h2>
            
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $index => $order): ?>
                    <div class="order-card" style="margin-bottom: 30px; max-width: 800px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px;">
                            <h3 style="margin: 0; font-size: 1.2rem;">Order #<?php echo $order['order_id']; ?></h3>
                            <div style="text-align: right;">
                                <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">Placed on: <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                                <p style="margin: 0; font-weight: 700; color: var(--accent-line); font-size: 1.1rem;">Total: $<?php echo number_format($order['total_amount'], 2); ?></p>
                            </div>
                        </div>
                        
                        <?php
                        // CORRECTED TABLE NAME HERE: ORDER_ITEM instead of ORDER_LINE
                        $item_stmt = $dbh->prepare("
                            SELECT oi.product_id, p.name AS prod_name, oi.price_at_order, oi.quantity 
                            FROM ORDER_ITEM oi 
                            JOIN PRODUCT p ON oi.product_id = p.product_id 
                            WHERE oi.order_id = :oid
                        ");
                        $item_stmt->execute([':oid' => $order['order_id']]);
                        $items = $item_stmt->fetchAll();
                        ?>
                        
                        <table class="table-stylish" style="margin-top: 0; box-shadow: none; border: 1px solid var(--border-color);">
                            <thead>
                                <tr>
                                    <th style="background: var(--bg-body);">Item</th>
                                    <th style="background: var(--bg-body);">Price</th>
                                    <th style="background: var(--bg-body);">Qty</th>
                                    <th style="background: var(--bg-body);">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['prod_name']); ?></strong><br>
                                        <span style="color:var(--text-muted); font-size:0.8rem;">ID: <?php echo $item['product_id']; ?></span>
                                    </td>
                                    <td>$<?php echo number_format($item['price_at_order'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><strong>$<?php echo number_format($item['price_at_order'] * $item['quantity'], 2); ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; font-size: 1.2em; border: 1px dashed var(--border-color); padding: 40px; background: #fff; border-radius: 10px;">You have not placed any orders yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>