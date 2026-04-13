<?php
require "db.php";
$dbh = connectDB();

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$username = htmlspecialchars($_SESSION['username']);
$msg = "";

// Handle Cart Actions (Update, Remove, Checkout)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        $pid = $_POST['product_id'];
        $qty = (int)$_POST['quantity'];
        
        $stmt = $dbh->prepare("UPDATE SHOPPING_CART SET quantity = :qty WHERE customer_id = :cid AND product_id = :pid");
        $stmt->execute([':qty' => $qty, ':cid' => $customer_id, ':pid' => $pid]);
        $msg = "<p style='color: #28a745; font-weight: bold; margin-bottom: 20px;'>The quantity has been updated!</p>";
        
    } elseif (isset($_POST['remove_item'])) {
        $pid = $_POST['product_id'];
        
        $stmt = $dbh->prepare("DELETE FROM SHOPPING_CART WHERE customer_id = :cid AND product_id = :pid");
        $stmt->execute([':cid' => $customer_id, ':pid' => $pid]);
        $msg = "<p style='color: #28a745; font-weight: bold; margin-bottom: 20px;'>The item has been removed!</p>";
        
    } elseif (isset($_POST['checkout'])) {
        // Simple Checkout Logic:
        // 1. Calculate total
        $stmt = $dbh->prepare("SELECT SUM(c.quantity * c.added_price) as total FROM SHOPPING_CART c WHERE c.customer_id = :cid");
        $stmt->execute([':cid' => $customer_id]);
        $total = $stmt->fetchColumn();

        if ($total > 0) {
            // 2. Check stock before finalizing (Optional but recommended)
            $cart_items = $dbh->prepare("SELECT c.product_id, c.quantity, p.stock_qty FROM SHOPPING_CART c JOIN PRODUCT p ON c.product_id = p.product_id WHERE c.customer_id = :cid");
            $cart_items->execute([':cid' => $customer_id]);
            $items = $cart_items->fetchAll();
            
            $can_checkout = true;
            foreach ($items as $item) {
                if ($item['quantity'] > $item['stock_qty']) {
                    $can_checkout = false;
                    $msg = "<p style='color: #dc3545; font-weight: bold; margin-bottom: 20px;'>There are only {$item['stock_qty']} left for product id {$item['product_id']}. Please update your cart.</p>";
                    break;
                }
            }

            if ($can_checkout) {
                // Insert into CUSTOMER_ORDER
                $insert_order = $dbh->prepare("INSERT INTO CUSTOMER_ORDER (customer_id, order_time, total_amount) VALUES (:cid, NOW(), :total)");
                $insert_order->execute([':cid' => $customer_id, ':total' => $total]);
                $order_id = $dbh->lastInsertId();

                // Insert into ORDER_LINE and deduct stock
                $insert_line = $dbh->prepare("INSERT INTO ORDER_LINE (order_id, product_id, quantity, ordered_price) VALUES (:oid, :pid, :qty, :price)");
                $update_stock = $dbh->prepare("UPDATE PRODUCT SET stock_qty = stock_qty - :qty WHERE product_id = :pid");
                
                $cart_full = $dbh->prepare("SELECT product_id, quantity, added_price FROM SHOPPING_CART WHERE customer_id = :cid");
                $cart_full->execute([':cid' => $customer_id]);
                
                while ($row = $cart_full->fetch()) {
                    $insert_line->execute([':oid' => $order_id, ':pid' => $row['product_id'], ':qty' => $row['quantity'], ':price' => $row['added_price']]);
                    $update_stock->execute([':qty' => $row['quantity'], ':pid' => $row['product_id']]);
                }

                // Clear Cart
                $clear_cart = $dbh->prepare("DELETE FROM SHOPPING_CART WHERE customer_id = :cid");
                $clear_cart->execute([':cid' => $customer_id]);

                $msg = "<p style='color: #28a745; font-weight: bold; margin-bottom: 20px;'>Order placed successfully!</p>";
            }
        } else {
            $msg = "<p style='color: #dc3545; font-weight: bold; margin-bottom: 20px;'>Your cart is empty.</p>";
        }
    }
}

// Fetch Cart Items
$stmt = $dbh->prepare("
    SELECT c.product_id, c.quantity, c.added_price, p.name 
    FROM SHOPPING_CART c 
    JOIN PRODUCT p ON c.product_id = p.product_id 
    WHERE c.customer_id = :cid
");
$stmt->execute([':cid' => $customer_id]);
$cart_items = $stmt->fetchAll();

$grand_total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart - Tech Shop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .top-nav-links { list-style: none; display: flex; gap: 20px; flex-wrap: wrap; justify-content: flex-end;}
        .top-nav-links a { font-weight: 600; color: var(--accent-line); font-size: 0.95rem; }
        .top-nav-links a:hover { color: #2575fc; }
        
        .cart-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: var(--card-shadow); }
        .cart-table th, .cart-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .cart-table th { background: #f8f9fa; font-weight: 700; text-transform: uppercase; font-size: 0.9rem; color: var(--text-muted); }
        .btn-table { padding: 8px 15px; font-size: 0.85rem; border-radius: 4px; }
        .btn-remove { background: #dc3545; box-shadow: none; }
        .btn-remove:hover { background: #c82333; }
        .qty-input { width: 70px; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; text-align: center; }
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
            
            <h2 style="margin-top: 20px; margin-bottom: 20px; text-transform: uppercase;">Your Shopping Cart</h2>
            
            <?php echo $msg; ?>

            <?php if (count($cart_items) > 0): ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): 
                            $line_total = $item['quantity'] * $item['added_price'];
                            $grand_total += $line_total;
                        ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>$<?php echo number_format($item['added_price'], 2); ?></td>
                            <td>
                                <form method="post" action="cart.php" style="display: flex; gap: 10px; align-items: center;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="qty-input">
                                    <button type="submit" name="update_cart" class="btn-ombre btn-table">Update</button>
                                </form>
                            </td>
                            <td><strong style="color: var(--accent-line);">$<?php echo number_format($line_total, 2); ?></strong></td>
                            <td>
                                <form method="post" action="cart.php" style="display:inline-block;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" name="remove_item" class="btn-ombre btn-table btn-remove">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="text-align: right; margin-top: 40px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: var(--card-shadow);">
                    <h3 style="display: inline-block; margin-right: 20px; font-size: 1.5rem; color: var(--text-main);">Grand Total: <span style="color: var(--accent-line);">$<?php echo number_format($grand_total, 2); ?></span></h3>
                    <form method="post" action="cart.php" style="display:inline-block;">
                        <button type="submit" name="checkout" class="btn-ombre" style="width: auto; padding: 15px 50px; font-size: 1.1rem;">Checkout</button>
                    </form>
                </div>
            <?php else: ?>
                <p style="text-align: center; font-size: 1.2em; border: 1px dashed var(--border-color); padding: 40px; background: #fff; border-radius: 10px; color: var(--text-muted);">Your cart is empty.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>