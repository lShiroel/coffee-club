<?php include 'db_connection.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Coffee Club</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .page-title {
            text-align: center;
            margin: 20px 0;
            font-size: 32px;
            color: #444;
        }

        .table-menu {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            color: #fff;
        }

        .table-menu th, .table-menu td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .table-menu th {
            background-color: #4B4540;
            font-size: 18px;
        }

        .table-menu td {
            font-size: 16px;
            color: #333;
        }

        .quantity-controls {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .quantity-controls button {
            width: 30px;
            height: 30px;
            font-size: 16px;
            background-color: #A0796A;
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            outline: none;
            transition: background-color 0.2s;
        }

        .quantity-controls button:hover {
            background-color: #4B4540;
        }

        .quantity-controls input {
            width: 50px;
            text-align: center;
            margin: 0 5px;
            border: 1px solid #ddd;
            font-size: 16px;
            padding: 5px;
            border-radius: 4px;
            outline: none;
        }
        .submit-button {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 12px;
            background-color: #A08162;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-align: center;
            outline: none;
        }

        .submit-button:hover {
            background-color: #4B4540;
        }
        .table-menu {
            animation: fadeInUp 0.8s ease forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            0% {
                transform: translateY(20px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

    </style>
</head>
<body>

    <div class="navbar">
        <a class="navbar-link" href="index.php">หน้าแรก</a>
        <a class="navbar-link" href="menu.php">จัดการเมนู</a>
        <a class="navbar-link" href="stock.php">จัดการสต๊อก</a>
        <a class="navbar-link" href="order.php">รายการสั่งซื้อ</a>
        <a class="navbar-link" href="bill.php">ใบเสร็จ</a>
        <a class="navbar-link" href="receipts.php">ใบเสร็จทั้งหมด</a>
    </div>

    <h1 class="page-title">เมนูกาแฟ</h1>
    <form class="order-form" method="post" action="order.php">
        <table class="table-menu">
            <tr>
                <th class="menu-header">ชื่อ</th>
                <th class="menu-header">ราคา</th>
                <th class="menu-header">จำนวน</th>
            </tr>
            <?php
            $stmt = $conn->query("SELECT * FROM products");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                    <td class='menu-name'>{$row['name']}</td>
                    <td class='menu-price'>{$row['price']}</td>
                    <td class='menu-quantity'>
                        <div class='quantity-controls'>
                            <button class='quantity-button' type='button' onclick='changeQuantity({$row['id']}, -1)'>-</button>
                            <input class='quantity-input' type='texe' min='0' value='0' id='qty_{$row['id']}' oninput='updateQuantity({$row['id']})'>
                            <button class='quantity-button' type='button' onclick='changeQuantity({$row['id']}, 1)'>+</button>
                        </div>
                        <input class='hidden-input' type='hidden' name='product_ids[]' value='{$row['id']}'>
                        <input class='hidden-input' type='hidden' name='quantities[]' id='quantity_{$row['id']}' value='0'>
                    </td>
                </tr>";
            }
            ?>
        </table>
        <button class="submit-button" type="submit">ยืนยันการสั่งซื้อ</button>
    </form>

    <script>
        function changeQuantity(productId, delta) {
            const quantityInput = document.getElementById(`qty_${productId}`);
            let currentQuantity = parseInt(quantityInput.value) || 0;
            currentQuantity += delta;
            if (currentQuantity < 0) {
                currentQuantity = 0;
            }
            quantityInput.value = currentQuantity;
            document.getElementById(`quantity_${productId}`).value = currentQuantity;
        }

        function updateQuantity(productId) {
            const quantityInput = document.getElementById(`qty_${productId}`);
            let quantity = parseInt(quantityInput.value);
            if (isNaN(quantity) || quantity < 0) {
                quantityInput.value = 0;
            }
            document.getElementById(`quantity_${productId}`).value = quantityInput.value;
        }
    </script>

</body>
</html>