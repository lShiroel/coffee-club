
<?php include 'db_connection.php'; 
        if (isset($_POST['add_ingredient'])) {
            $name = $_POST['name'];
            $stock = $_POST['stock'];
            $stmt = $conn->prepare("INSERT INTO ingredients (name, stock) VALUES (?, ?)");
            $stmt->execute([$name, $stock]);
            header("Location: stock.php");
            exit; // ป้องกันการทำซ้ำหลังจากการรีเฟรช
        }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการสต๊อก</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            text-align: center;
            color: #333;
        }

        .section-title {
            margin-top: 20px;
            color: #4B4540;
            border-bottom: 2px solid #A08162;
            padding-bottom: 10px;
        }

        .input-field {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .submit-btn {
            background-color: #A08162;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        .table {
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

        .action-link {
            margin-right: 10px;
            color: #4B4540;
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid #A08162;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .action-link:hover {
            background-color: #A08162;
            color: white;
        }

        .delete {
            color: red;
        }

        .delete:hover {
            background-color: #ff4d4d;
            color: white;
        }
    </style>
<body>
    <div class="navbar">
        <a href="index.php" class="nav-link">หน้าแรก</a>
        <a href="menu.php" class="nav-link">จัดการเมนู</a>
        <a href="stock.php" class="nav-link active">จัดการสต๊อก</a>
        <a href="order.php" class="nav-link">รายการสั่งซื้อ</a>
        <a href="bill.php" class="nav-link">ใบเสร็จ</a>
        <a href="receipts.php" class="nav-link">ใบเสร็จทั้งหมด</a>
    </div>
    
    <div class="container">
        <h1 class="page-title">จัดการสต๊อกวัตถุดิบ</h1>

        <!-- ฟอร์มเพิ่มวัตถุดิบใหม่ -->
        <form method="post" class="ingredient-form">
            <input type="text" name="name" placeholder="ชื่อวัตถุดิบ" required class="input-field">
            <input type="number" name="stock" placeholder="จำนวนในสต๊อก" required class="input-field">
            <button type="submit" name="add_ingredient" class="submit-btn">เพิ่มวัตถุดิบ</button>
        </form>

        <h2 class="section-title">รายการวัตถุดิบ</h2>
        <table class="table">
            <tr>
                <th>ชื่อ</th>
                <th>จำนวนในสต๊อก(กรัม)</th>
                <th>การดำเนินการ</th>
            </tr>
            <?php
            // แสดงรายการวัตถุดิบทั้งหมด
            $stmt = $conn->query("SELECT * FROM ingredients");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                    <td>{$row['name']}</td>
                    <td>{$row['stock']}</td>
                    <td>
                        <a href='edit_ingredient.php?id={$row['id']}' class='action-link'>เพิ่มวัตถุดิบ</a>
                        <a href='delete_ingredient.php?id={$row['id']}' class='action-link delete'>ลบ</a>
                    </td>
                </tr>";
            }
            ?>
        </table>

        <!-- ส่วนแสดงวัตถุดิบที่ใช้ในการสั่งซื้อ -->
        <h2 class="section-title">วัตถุดิบที่ใช้ในการสั่งซื้อ</h2>
        <table class="table">
            <tr>
                <th>ชื่อเมนู</th>
                <th>วัตถุดิบ</th>
                <th>จำนวนที่ใช้</th>
            </tr>
            <?php
            // ดึงข้อมูลคำสั่งซื้อทั้งหมด
            $ordersStmt = $conn->query("SELECT * FROM orders");
            while ($order = $ordersStmt->fetch(PDO::FETCH_ASSOC)) {
                $orderId = $order['id'];

                // ดึงข้อมูลรายการสินค้าที่สั่ง
                $orderItemsStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $orderItemsStmt->execute([$orderId]);

                while ($item = $orderItemsStmt->fetch(PDO::FETCH_ASSOC)) {
                    // ดึงข้อมูลเมนู
                    $productStmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                    $productStmt->execute([$item['product_id']]);
                    $product = $productStmt->fetch(PDO::FETCH_ASSOC);

                    if ($product) {
                        // ดึงวัตถุดิบที่ใช้ในการทำเมนู
                        $ingredientsStmt = $conn->prepare("SELECT * FROM menu_ingredients WHERE product_id = ?");
                        $ingredientsStmt->execute([$product['id']]);

                        while ($ingredient = $ingredientsStmt->fetch(PDO::FETCH_ASSOC)) {
                            // ดึงชื่อวัตถุดิบ
                            $ingredientNameStmt = $conn->prepare("SELECT * FROM ingredients WHERE id = ?");
                            $ingredientNameStmt->execute([$ingredient['ingredient_id']]);
                            $ingredientName = $ingredientNameStmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($ingredientName) {
                                echo "<tr>
                                    <td>{$product['name']}</td>
                                    <td>{$ingredientName['name']}</td>
                                    <td>" . ($ingredient['quantity_needed'] * $item['quantity']) . "</td>
                                </tr>";
                            }
                        }
                    }
                }
            }
            ?>
        </table>
    </div>
</body>
</html>
