<?php
require "db.php";
require "products_data.php"; 
$dbh = connectDB();

// Allow guests to view, but track login status 
$is_logged_in = isset($_SESSION['customer_id']);
$username = $is_logged_in ? htmlspecialchars($_SESSION['username']) : "Guest";

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    // Redirect to login if a guest tries to add to cart
    if (!$is_logged_in) {
        header("Location: login.php");
        exit();
    }
    
    $qty = (int)$_POST['quantity'];
    
    $stmt = $dbh->prepare("SELECT price, stock_qty FROM PRODUCT WHERE product_id = :pid");
    $stmt->execute([':pid' => $product_id]);
    $product = $stmt->fetch();
    
    if ($product && $product['stock_qty'] >= $qty) {
        $price = $product['price'];
        
        $check_stmt = $dbh->prepare("SELECT quantity FROM SHOPPING_CART WHERE customer_id = :cid AND product_id = :pid");
        $check_stmt->execute([':cid' => $_SESSION['customer_id'], ':pid' => $product_id]);
        $existing = $check_stmt->fetch();
        
        if ($existing) {
            $new_qty = $existing['quantity'] + $qty;
            $update_stmt = $dbh->prepare("UPDATE SHOPPING_CART SET quantity = :qty WHERE customer_id = :cid AND product_id = :pid");
            $update_stmt->execute([':qty' => $new_qty, ':cid' => $_SESSION['customer_id'], ':pid' => $product_id]);
        } else {
            $insert_stmt = $dbh->prepare("INSERT INTO SHOPPING_CART (customer_id, product_id, quantity, added_price) VALUES (:cid, :pid, :qty, :price)");
            $insert_stmt->execute([':cid' => $_SESSION['customer_id'], ':pid' => $product_id, ':qty' => $qty, ':price' => $price]);
        }
        $msg = "<p style='color: #28a745; font-weight: bold; margin-bottom: 20px;'>Item added to cart!</p>";
    } else {
        $msg = "<p style='color: #dc3545; font-weight: bold; margin-bottom: 20px;'>Not enough stock available.</p>";
    }
}

$stmt = $dbh->prepare("SELECT p.*, c.category_name FROM PRODUCT p LEFT JOIN CATEGORY c ON p.category_id = c.category_id WHERE p.product_id = :pid");
$stmt->execute([':pid' => $product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found.");
}

// Safely get metadata for this specific product ID
$meta = isset($product_metadata[$product_id]) ? $product_metadata[$product_id] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?> - Tech Shop</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
                    <?php if ($is_logged_in): ?>
                       <li><a href="orders.php">View Orders</a></li>
                       <li><a href="cart.php">Shopping Cart</a></li>
                       <li><a href="change_password.php">Change Password</a></li>
                       <li><a href="login.php?action=logout" style="color: #dc3545;">Logout</a></li>
                    <?php else: ?>
                       <li><a href="login.php" class="btn-ombre" style="padding: 8px 25px; text-decoration: none;">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="container">
            <a href="index.php" class="btn-back">Back to Shop</a>
            
            <div class="form-card" style="max-width: 900px; margin-top: 20px;">
                <?php echo $msg; ?>
                
                <div style="display: flex; flex-wrap: wrap; gap: 40px;">
                    <div style="flex: 1; min-width: 300px; text-align: center;">
                        <?php 
    // Pull the exact image path from products_data.php
    $main_img = isset($meta['img']) ? $meta['img'] : "images/" . $product['product_id'] . ".jpg"; 
?>
                        <img src="<?php echo $main_img; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             onerror="this.onerror=null; this.src='https://via.placeholder.com/400x400.png?text=No+Image';" 
                             style="width: 100%; max-width: 400px; border-radius: 8px; box-shadow: var(--card-shadow);">
                        
                        <?php if ($meta && !empty($meta['alt_img'])): ?>
                            <div style="margin-top: 15px; display: flex; justify-content: center; gap: 10px;">
                                <img src="<?php echo $meta['alt_img']; ?>" alt="Alternate angle" 
                                     onerror="this.style.display='none';" 
                                     style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px; cursor: pointer; border: 1px solid var(--border-color);">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="flex: 1; min-width: 300px;">
                        <h2 style="margin-bottom: 10px; font-size: 2rem;"><?php echo htmlspecialchars($product['name']); ?></h2>
                        <span style="display: inline-block; background: var(--bg-body); padding: 5px 10px; border-radius: 4px; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 20px; font-weight: 600; text-transform: uppercase;">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </span>
                        
                        <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 30px; color: var(--text-main);">
                            <?php 
                                $desc = $meta ? $meta['description'] : "No description currently available.";
                                echo nl2br(htmlspecialchars($desc)); 
                            ?>
                        </p>
                        
                        <h3 style="font-size: 2rem; color: var(--accent-line); margin-bottom: 10px;">$<?php echo number_format($product['price'], 2); ?></h3>
                        <p style="color: var(--text-muted); margin-bottom: 30px; font-weight: 500;">Stock Available: <?php echo $product['stock_qty']; ?></p>

                        <form method="post" action="product_detail.php?id=<?php echo $product_id; ?>">
                            <div class="form-group" style="display: flex; align-items: center; gap: 15px;">
                                <label style="margin: 0; font-weight: 600;">Quantity:</label>
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_qty']; ?>" style="width: 80px; padding: 10px;" required>
                            </div>
                            
                            <?php if ($product['stock_qty'] > 0): ?>
                                <button type="submit" name="add_to_cart" class="btn-ombre" style="margin-top: 10px; max-width: 200px;">Add to Cart</button>
                            <?php else: ?>
                                <button type="button" class="btn-ombre" style="background: #ccc; cursor: not-allowed; box-shadow: none; margin-top: 10px; max-width: 200px;" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>