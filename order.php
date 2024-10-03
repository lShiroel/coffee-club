<?php
include 'db_connection.php';
session_start();

// เพิ่มรายการในตะกร้า
if (isset($_POST['product_ids']) && isset($_POST['quantities'])) {
    foreach ($_POST['product_ids'] as $index => $productId) {
        $quantity = $_POST['quantities'][$index];

        if ($quantity > 0) {
            if (isset($_SESSION['order'][$productId])) {
                $_SESSION['order'][$productId]['quantity'] += $quantity;
            } else {
                $_SESSION['order'][$productId] = [
                    'product_id' => $productId,
                    'quantity' => $quantity
                ];
            }
        }
    }
}

// ลดจำนวนในตะกร้า
if (isset($_POST['action'])) {
    $productId = $_POST['product_id'];
    if ($_POST['action'] == 'increase') {
        $_SESSION['order'][$productId]['quantity'] += 1;
    } elseif ($_POST['action'] == 'decrease' && $_SESSION['order'][$productId]['quantity'] > 1) {
        $_SESSION['order'][$productId]['quantity'] -= 1;
    } elseif ($_POST['action'] == 'remove') {
        unset($_SESSION['order'][$productId]);
    }
}

// ยกเลิกคำสั่งซื้อ
if (isset($_POST['cancel_order'])) {
    unset($_SESSION['order']);
    header("Location: order.php");
    exit();
}

// ยืนยันการสั่งซื้อ
if (isset($_POST['confirm_order'])) {
    if (empty($_SESSION['order'])) {
        echo "<script>alert('กรุณาเลือกสินค้าก่อนยืนยันการสั่งซื้อ');</script>";
    } else {
        $ingredientsSufficient = true;
        foreach ($_SESSION['order'] as $item) {
            $stmtIngredients = $conn->prepare("SELECT ingredient_id, quantity_needed FROM menu_ingredients WHERE product_id = ?");
            $stmtIngredients->execute([$item['product_id']]);

            while ($ingredient = $stmtIngredients->fetch(PDO::FETCH_ASSOC)) {
                $stmtCheck = $conn->prepare("SELECT stock FROM ingredients WHERE id = ?");
                $stmtCheck->execute([$ingredient['ingredient_id']]);
                $ingredientStock = $stmtCheck->fetchColumn();

                if ($ingredientStock < ($ingredient['quantity_needed'] * $item['quantity'])) {
                    $ingredientsSufficient = false;
                    break;
                }
            }
            if (!$ingredientsSufficient) break;
        }

        if ($ingredientsSufficient) {
            $totalPrice = 0;
            $stmt = $conn->prepare("INSERT INTO orders (total_price) VALUES (?)");
            $stmt->execute([$totalPrice]);
            $orderId = $conn->lastInsertId();

            foreach ($_SESSION['order'] as $item) {
                $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    $subtotal = $product['price'] * $item['quantity'];
                    $totalPrice += $subtotal;

                    $stmtOrder = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    $stmtOrder->execute([$orderId, $item['product_id'], $item['quantity'], $subtotal]);
                }
            }

            $stmt = $conn->prepare("UPDATE orders SET total_price = ? WHERE id = ?");
            $stmt->execute([$totalPrice, $orderId]);

            foreach ($_SESSION['order'] as $item) {
                $stmtIngredients = $conn->prepare("SELECT ingredient_id, quantity_needed FROM menu_ingredients WHERE product_id = ?");
                $stmtIngredients->execute([$item['product_id']]);

                while ($ingredient = $stmtIngredients->fetch(PDO::FETCH_ASSOC)) {
                    $stmtUpdate = $conn->prepare("UPDATE ingredients SET stock = stock - ? WHERE id = ?");
                    $stmtUpdate->execute([$ingredient['quantity_needed'] * $item['quantity'], $ingredient['ingredient_id']]);
                }
            }

            unset($_SESSION['order']);
            header("Location: bill.php?id=$orderId");
            exit();
        } else {
            echo "<script>alert('วัตถุดิบไม่เพียงพอสำหรับการสั่งซื้อ กรุณาตรวจสอบสต๊อก');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตะกร้าสินค้า</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .cart-title {
            text-align: center;
            margin: 20px 0;
            color: #4B4540;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .cart-table th, .cart-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .cart-table th {
            background-color: #f2f2f2;
            color: #333;
        }

        .total-price {
            text-align: right;
            font-weight: bold;
            margin-top: 20px;
            color: #4B4540;
        }

        .confirm-btn, .cancel-btn {
            background-color: #A08162;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }

        .confirm-btn:hover {
            background-color: #45a049;
        }

        .cancel-btn:hover {
            background-color: #DB0F00;
        }

        .remove-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 10px 40px;
            border-radius: 5px;
            cursor: pointer;
        }

        .remove-btn:hover {
            background-color: darkred;
        }

        .quantity-form {
            display: inline-flex;
            align-items: center;
        }

        .quantity-btn {
            border: none;
            background-color: #A08162;
            color: white;
            padding: 5px 10px;
            margin: 0 5px;
            cursor: pointer;
        }

        .quantity-btn:hover {
            background-color: #8B6A55;
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
    <h1 class="cart-title">ตะกร้าสินค้า</h1>
    <form method="post" class="cart-form">
        <table class="cart-table">
            <tr>
                <th>ชื่อ</th>
                <th>ราคา</th>
                <th>จำนวน</th>
                <th>รวม</th>
                <th>จัดการ</th>
            </tr>
            <?php
            $totalPrice = 0;
            if (isset($_SESSION['order'])) {
                foreach ($_SESSION['order'] as $item) {
                    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                    $stmt->execute([$item['product_id']]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($product) {
                        $subtotal = $product['price'] * $item['quantity'];
                        $totalPrice += $subtotal;

                        echo "<tr>
                            <td>{$product['name']}</td>
                            <td>{$product['price']}</td>
                            <td>
                                <form method='post' class='quantity-form'>
                                    <input type='hidden' name='product_id' value='{$item['product_id']}'>
                                    <input type='hidden' name='quantities[]' value='{$item['quantity']}'>
                                    <button type='submit' name='action' value='decrease' class='quantity-btn'>-</button>
                                    <span style='margin: 0 10px;'>{$item['quantity']}</span>
                                    <button type='submit' name='action' value='increase' class='quantity-btn'>+</button>
                                </form>
                            </td>
                            <td>{$subtotal}</td>
                            <td>
                                <form method='post' class='remove-form'>
                                    <input type='hidden' name='product_id' value='{$item['product_id']}'>
                                    <button type='submit' name='action' value='remove' class='remove-btn'>ลบ</button>
                                </form>
                            </td>
                        </tr>";
                    } else {
                        echo "<tr><td colspan='5'>ไม่พบข้อมูลสินค้า</td></tr>";
                    }
                }
            } else {
                echo "<tr><td colspan='5'>ตะกร้าสินค้าไม่มีรายการ</td></tr>";
            }
            ?>
        </table>
        <h3 class="total-price">รวมทั้งหมด: <?= number_format($totalPrice, 2) ?> บาท</h3>
        <button type="submit" name="confirm_order" class="confirm-btn">ยืนยันการสั่งซื้อ</button>
        <button type="submit" name="cancel_order" class="cancel-btn">ยกเลิกการสั่งซื้อ</button>
    </form>
</body>
</html>
