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

// --- HANDLE ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // UPDATE QUANTITY
    if (isset($_POST['update_cart'])) {
        $pid = $_POST['product_id'];
        $qty = (int)$_POST['quantity'];
        $stmt = $dbh->prepare("UPDATE SHOPPING_CART SET quantity = :qty WHERE customer_id = :cid AND product_id = :pid");
        $stmt->execute([':qty' => $qty, ':cid' => $customer_id, ':pid' => $pid]);
        $msg = "<p style='color: #28a745; font-weight: bold; margin-bottom: 20px;'>The quantity has been updated!</p>";
    } 
    
    // REMOVE ITEM
    elseif (isset($_POST['remove_item'])) {
        $pid = $_POST['product_id'];
        $stmt = $dbh->prepare("DELETE FROM SHOPPING_CART WHERE customer_id = :cid AND product_id = :pid");
        $stmt->execute([':cid' => $customer_id, ':pid' => $pid]);
        $msg = "<p style='color: #28a745; font-weight: bold; margin-bottom: 20px;'>The item has been removed!</p>";
    } 

    // CHECKOUT (Using your Stored Procedure)
    elseif (isset($_POST['checkout'])) {
        // First, check if the cart is actually empty
        $check_cart = $dbh->prepare("SELECT COUNT(*) FROM SHOPPING_CART WHERE customer_id = :cid");
        $check_cart->execute([':cid' => $customer_id]);
        
        if ($check_cart->fetchColumn() > 0) {
            try {
                // 1. CALL your checkout procedure from createPSM.sql
                // The procedure handles: START TRANSACTION, stock check, logging, and COMMIT/ROLLBACK
                $stmt = $dbh->prepare("CALL checkout(:cid, @order_id, @out_of_stock)");
                $stmt->execute([':cid' => $customer_id]);

                // 2. Fetch the OUT parameters to see what happened
                $res = $dbh->query("SELECT @order_id AS oid, @out_of_stock AS oos")->fetch();
                $order_id = $res['oid'];
                $out_of_stock_id = $res['oos'];

                // 3. Handle the outcome based on the Procedure results
                if ($out_of_stock_id) {
                    // This matches the Requirement: "If there is insufficient stock, display an error"
                    // We fetch the name so the user knows which item failed
                    $p_stmt = $dbh->prepare("SELECT name, stock_qty FROM PRODUCT WHERE product_id = :pid");
                    $p_stmt->execute([':pid' => $out_of_stock_id]);
                    $p_info = $p_stmt->fetch();
                    
                    $msg = "<p style='color: #dc3545; font-weight: bold; margin-bottom: 20px;'>
                            There are only {$p_info['stock_qty']} left for " . htmlspecialchars($p_info['name']) . " (ID: $out_of_stock_id). 
                            Please update your cart.
                            </p>";
                } elseif ($order_id) {
                    $msg = "<p style='color: #28a745; font-weight: bold; margin-bottom: 20px;'>Order placed successfully! Order ID: #$order_id</p>";
                }
            } catch (PDOException $e) {
                $msg = "<p style='color: #dc3545; font-weight: bold; margin-bottom: 20px;'>System Error: " . $e->getMessage() . "</p>";
            }
        } else {
            $msg = "<p style='color: #dc3545; font-weight: bold; margin-bottom: 20px;'>Your cart is empty.</p>";
        }
    }
}

// --- FETCH CART FOR DISPLAY ---
$stmt = $dbh->prepare("SELECT c.*, p.name FROM SHOPPING_CART c JOIN PRODUCT p ON c.product_id = p.product_id WHERE c.customer_id = :cid");
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
        .cart-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; box-shadow: var(--card-shadow); border-radius: 8px; overflow: hidden; }
        .cart-table th, .cart-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .cart-table th { background: #f8f9fa; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; }
        .qty-input { width: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <header>
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="site-logo"><a href="index.php">Tech Shop</a></div>
            <div style="text-align: right;">
                <h3 style="margin-bottom: 10px;">Welcome <span style="color: var(--accent-line);"><?php echo $username; ?></span> !!</h3>
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
            <a href="index.php" class="btn-back" style="text-decoration: none;">&larr; Back to Shop</a>
            <h2 style="margin: 20px 0; text-transform: uppercase;">Your Shopping Cart</h2>
            
            <?php echo $msg; ?>

            <?php if (count($cart_items) > 0): ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): 
                            $sub = $item['quantity'] * $item['added_price'];
                            $grand_total += $sub;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                            <td>$<?php echo number_format($item['added_price'], 2); ?></td>
                            <td>
                                <form method="post" style="display: flex; gap: 5px;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="qty-input">
                                    <button type="submit" name="update_cart" class="btn-ombre" style="padding: 5px 10px; font-size: 0.8rem;">Update</button>
                                </form>
                            </td>
                            <td>$<?php echo number_format($sub, 2); ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" name="remove_item" style="color: red; border:none; background:none; cursor:pointer; text-decoration: underline;">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="text-align: right; margin-top: 30px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: var(--card-shadow);">
                    <h3 style="margin-bottom: 15px;">Grand Total: <span style="color: var(--accent-line);">$<?php echo number_format($grand_total, 2); ?></span></h3>
                    <form method="post">
                        <button type="submit" name="checkout" class="btn-ombre" style="padding: 15px 40px; font-size: 1.1rem; width: auto;">Checkout</button>
                    </form>
                </div>
            <?php else: ?>
                <p style="text-align: center; padding: 50px; background: #fff; border-radius: 8px; border: 1px dashed #ccc;">Your cart is empty. <a href="index.php">Go shopping!</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>