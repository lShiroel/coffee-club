
<?php 
ob_start();
include 'db_connection.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการเมนู</title>
    <link rel="stylesheet" href="styles.css">
    <style>


        .container {
            width: 80%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        form {
            margin-bottom: 30px;
        }

        form input, form textarea {
            width: 95%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form button {
            background-color: #A08162;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #45a049;
        }

        .ingredient-list {
            margin-top: 20px;
        }

        .ingredient-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .ingredient-item label {
            margin-right: 10px;
        }

        .ingredient-item input[type='number'] {
            width: 80px;
            padding: 5px;
            margin-left: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .actions a {
            margin-right: 10px;
            color: #4B4540;
            text-decoration: none;
        }

        .actions a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">หน้าแรก</a>
        <a href="menu.php">จัดการเมนู</a>
        <a href="stock.php">จัดการสต๊อก</a>
        <a href="order.php">รายการสั่งซื้อ</a>
        <a href="bill.php">ใบเสร็จ</a>
        <a href="receipts.php">ใบเสร็จทั้งหมด</a>
    </div>

    <div class="container">
        <h1>จัดการเมนู</h1>
        <form method="post" class="menu-form">
            <input class="menu-input" type="text" name="name" placeholder="ชื่อเมนู" required>
            <input class="menu-input" type="text" name="price" placeholder="ราคา" required>
            <textarea class="menu-textarea" name="description" placeholder="รายละเอียด"></textarea>

            <h3>เลือกวัตถุดิบสำหรับเมนู</h3>
            <div id="ingredientList" class="ingredient-list">
                <?php
                $ingredientStmt = $conn->query("SELECT * FROM ingredients");
                while ($ingredient = $ingredientStmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<div class='ingredient-item'>
                            <label>{$ingredient['name']}</label>
                            <input class='quantity-input' type='number' name='quantities[{$ingredient['id']}]' placeholder='จำนวน' min='0' required>&nbspกรัม
                          </div>";
                }
                ?>
            </div>

            <button type="submit" name="add_menu" class="submit-button">เพิ่มเมนู</button>
        </form>

        <h2>รายการเมนู</h2>
        <table>
            <tr>
                <th>ชื่อ</th>
                <th>ราคา</th>
                <th>รายละเอียด</th>
                <th>วัตถุดิบที่ใช้</th>
                <th>การดำเนินการ</th>
            </tr>
            <?php
            $stmt = $conn->query("SELECT * FROM products");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // ดึงข้อมูลวัตถุดิบที่ใช้ในเมนู
                $ingredientStmt = $conn->prepare("SELECT i.name, mi.quantity_needed FROM menu_ingredients mi JOIN ingredients i ON mi.ingredient_id = i.id WHERE mi.product_id = ?");
                $ingredientStmt->execute([$row['id']]);
                $ingredientsUsed = $ingredientStmt->fetchAll(PDO::FETCH_ASSOC);

                // แสดงข้อมูลวัตถุดิบที่ใช้ในเมนู
                $ingredientsList = "";
                foreach ($ingredientsUsed as $ingredient) {
                    $ingredientsList .= "{$ingredient['name']} (จำนวน: {$ingredient['quantity_needed']} กรัม)<br>";
                }

                echo "<tr>
                    <td>{$row['name']}</td>
                    <td>{$row['price']}</td>
                    <td>{$row['description']}</td>
                    <td>{$ingredientsList}</td>
                    <td class='actions'>
                        <a href='edit_menu.php?id={$row['id']}'>อัปเดต</a>
                        <a href='delete_menu.php?id={$row['id']}'>ลบ</a>
                    </td>
                </tr>";
            }
            ?>
        </table>
    </div>

    <?php
    if (isset($_POST['add_menu'])) {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];

        // เพิ่มเมนูใหม่
        $stmt = $conn->prepare("INSERT INTO products (name, price, description) VALUES (?, ?, ?)");
        $stmt->execute([$name, $price, $description]);
        $productId = $conn->lastInsertId();

        // เพิ่มวัตถุดิบที่ใช้ในเมนู
        if (!empty($_POST['quantities'])) {
            $quantities = $_POST['quantities'];

            foreach ($quantities as $ingredientId => $quantityNeeded) {
                // ตรวจสอบว่ามีการกำหนดจำนวนหรือไม่
                if (!empty($quantityNeeded) && is_numeric($quantityNeeded) && $quantityNeeded > 0) {
                    $ingredientStmt = $conn->prepare("INSERT INTO menu_ingredients (product_id, ingredient_id, quantity_needed) VALUES (?, ?, ?)");
                    $ingredientStmt->execute([$productId, $ingredientId, $quantityNeeded]);
                }
            }
        }

        header("Location: menu.php");
        exit();
    }
    ?>
</body>
</html>
