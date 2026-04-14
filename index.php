<?php
require "db.php";
require "products_data.php";
$dbh = connectDB();

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
$msg = "";

// 1. Fetch Categories for the Search Dropdown
$cat_stmt = $dbh->query("SELECT category_id, category_name FROM CATEGORY");
$categories = $cat_stmt->fetchAll();

// 2. Handle Quick Add to Cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1; // Default quick add quantity
    
    $stmt = $dbh->prepare("SELECT price, stock_qty FROM PRODUCT WHERE product_id = :pid");
    $stmt->execute([':pid' => $product_id]);
    $product = $stmt->fetch();
    
    if ($product && $product['stock_qty'] >= $quantity) {
        $price = $product['price'];
        
        $check_stmt = $dbh->prepare("SELECT quantity FROM SHOPPING_CART WHERE customer_id = :cid AND product_id = :pid");
        $check_stmt->execute([':cid' => $_SESSION['customer_id'], ':pid' => $product_id]);
        $existing = $check_stmt->fetch();
        
        if ($existing) {
            $new_qty = $existing['quantity'] + $quantity;
            $update_stmt = $dbh->prepare("UPDATE SHOPPING_CART SET quantity = :qty WHERE customer_id = :cid AND product_id = :pid");
            $update_stmt->execute([':qty' => $new_qty, ':cid' => $_SESSION['customer_id'], ':pid' => $product_id]);
        } else {
            $insert_stmt = $dbh->prepare("INSERT INTO SHOPPING_CART (customer_id, product_id, quantity, added_price) VALUES (:cid, :pid, :qty, :price)");
            $insert_stmt->execute([':cid' => $_SESSION['customer_id'], ':pid' => $product_id, ':qty' => $quantity, ':price' => $price]);
        }
        $msg = "<p style='color: #28a745; text-align: center; font-weight: bold; margin-bottom: 20px;'>Item added to cart!</p>";
    } else {
        $msg = "<p style='color: #dc3545; text-align: center; font-weight: bold; margin-bottom: 20px;'>Sorry, out of stock.</p>";
    }
}

// 3. Handle Search Logic (Category and/or Device Name)
$search_category = $_GET['category'] ?? '';
$search_term = $_GET['device_name'] ?? '';

// Build the dynamic SQL query based on user input
$sql = "SELECT p.*, c.category_name FROM PRODUCT p JOIN CATEGORY c ON p.category_id = c.category_id WHERE 1=1";
$params = [];

if (!empty($search_category)) {
    $sql .= " AND p.category_id = :cid";
    $params[':cid'] = $search_category;
}
if (!empty(trim($search_term))) {
    $sql .= " AND p.name LIKE :kw";
    $params[':kw'] = '%' . trim($search_term) . '%';
}

$stmt = $dbh->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tech Shop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Search and Header layout */
        .search-box {
            background: #fff; padding: 20px; border-radius: 10px; box-shadow: var(--card-shadow);
            margin-bottom: 40px; text-align: center;
        }
        .search-form { display: inline-flex; gap: 15px; align-items: center; flex-wrap: wrap; justify-content: center; }
        .search-input { padding: 12px 20px; border: 1px solid var(--border-color); border-radius: 25px; outline: none; font-family: inherit;}
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
            
            <div class="search-box">
                <form method="GET" action="index.php" class="search-form">
                    <select name="category" class="search-input" style="width: 200px;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php if($search_category == $cat['category_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="text" name="device_name" placeholder="Search device name..." value="<?php echo htmlspecialchars($search_term); ?>" class="search-input" style="width: 300px;">
                    
                    <button type="submit" class="btn-ombre" style="width: auto; padding: 12px 30px;">Search</button>
                    <a href="index.php" style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Clear</a>
                </form>
            </div>

            <?php echo $msg; ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px;">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-item">
                            <?php 

    $img_src = isset($product_metadata[$product['product_id']]['img']) ? $product_metadata[$product['product_id']]['img'] : "images/" . $product['product_id'] . ".jpg"; 
?>
<a href="product_detail.php?id=<?php echo $product['product_id']; ?>">
    <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" 
         onerror="this.onerror=null; this.src='https://via.placeholder.com/400x400.png?text=No+Image';" 
         style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 15px;">
</a>
                            
                            <p style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1px; margin-bottom: 5px;"><?php echo htmlspecialchars($product['category_name']); ?></p>
                            <h3 style="margin-bottom: 10px; font-size: 1.1rem;"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p style="font-weight: 700; font-size: 1.3rem; color: var(--accent-line); margin-bottom: 15px;">
                                $<?php echo number_format($product['price'], 2); ?>
                            </p>
                            
                            <div style="display: flex; gap: 10px; flex-direction: column;">
                                <form method="post" action="index.php">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <?php if ($product['stock_qty'] > 0): ?>
                                        <button type="submit" name="add_to_cart" class="btn-ombre">Add to Cart</button>
                                        <p style="font-size: 0.8rem; color: #28a745; margin-top: 10px; text-align: center;">In Stock (<?php echo $product['stock_qty']; ?>)</p>
                                    <?php else: ?>
                                        <button type="button" class="btn-ombre" style="background: #ccc; cursor: not-allowed; box-shadow: none;" disabled>Out of Stock</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1 / -1; text-align: center; padding: 40px; font-size: 1.2rem; color: var(--text-muted); background: #fff; border-radius: 10px;">No products found matching your search.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>