<?php
include 'db_connection.php';
session_start();

if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ดึงข้อมูลวัตถุดิบที่ใช้ในเมนู
$ingredientsStmt = $conn->prepare("SELECT i.id, i.name, mi.quantity_needed FROM menu_ingredients mi JOIN ingredients i ON mi.ingredient_id = i.id WHERE mi.product_id = ?");
$ingredientsStmt->execute([$productId]);
$ingredientsUsed = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['update_menu'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    // อัปเดตเมนู
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $price, $description, $productId]);

    // อัปเดตวัตถุดิบที่ใช้
    if (!empty($_POST['ingredients']) && !empty($_POST['quantities'])) {
        $ingredients = $_POST['ingredients'];
        $quantities = $_POST['quantities'];

        // ลบวัตถุดิบเดิมก่อนที่จะเพิ่มข้อมูลใหม่
        $stmt = $conn->prepare("DELETE FROM menu_ingredients WHERE product_id = ?");
        $stmt->execute([$productId]);

        foreach ($ingredients as $index => $ingredientId) {
            // ตรวจสอบจำนวนวัตถุดิบ
            if (!empty($quantities[$index]) && is_numeric($quantities[$index]) && $quantities[$index] > 0) {
                $quantityNeeded = $quantities[$index]; // ใช้จำนวนที่กำหนด
                $ingredientStmt = $conn->prepare("INSERT INTO menu_ingredients (product_id, ingredient_id, quantity_needed) VALUES (?, ?, ?)");
                $ingredientStmt->execute([$productId, $ingredientId, $quantityNeeded]);
            }
        }
    }

    header("Location: menu.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>อัปเดตเมนู</title>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        input[type="text"], input[type="number"], textarea {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .ingredient-checkbox {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        .ingredient-checkbox label {
            margin-right: 10px;
        }
        .back-link {
            text-decoration: none;
            color: #007bff;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>อัปเดตเมนู</h1>
        <form method="post">
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            <input type="text" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
            <textarea name="description" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>

            <h3>วัตถุดิบที่ใช้</h3>
            <div>
                <?php
                $ingredientStmt = $conn->query("SELECT * FROM ingredients");
                while ($ingredient = $ingredientStmt->fetch(PDO::FETCH_ASSOC)) {
                    // ตรวจสอบว่ามีการเลือกวัตถุดิบในเมนูนี้หรือไม่
                    $isChecked = '';
                    $quantity = '';
                    foreach ($ingredientsUsed as $used) {
                        if ($used['id'] == $ingredient['id']) {
                            $isChecked = 'checked';
                            $quantity = $used['quantity_needed'];
                        }
                    }
                    echo "<div class='ingredient-checkbox'>
                            <label>
                                <input type='checkbox' name='ingredients[]' value='{$ingredient['id']}' $isChecked> {$ingredient['name']}
                            </label>
                            <input type='number' name='quantities[]' value='$quantity' placeholder='จำนวน' min='0' required>
                          </div>";
                }
                ?>
            </div>

            <button type="submit" name="update_menu">อัปเดตเมนู</button>
        </form>
    </div>
    <a class="back-link" href="menu.php">กลับไปยังรายการเมนู</a>
</body>
</html>
