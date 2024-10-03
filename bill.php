<?php include 'db_connection.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบเสร็จ</title>
    <link rel="stylesheet" href="styles.css">
    <style>

        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            text-align: center;
            color: #4B4540;
            margin-bottom: 20px;
        }

        .error-message {
            color: red;
            text-align: center;
        }

        .receipt-title {
            font-size: 24px;
            margin: 20px 0;
            color: #4B4540;
        }

        .receipt-content {
            font-family: 'Arial', sans-serif; 
            font-size: 16px; 
            line-height: 1.5; 
            background-color: #f9f9f9; 
            border: 1px solid #ccc; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 5px; 
            white-space: pre-wrap; 
            overflow-wrap: break-word; 
        }

        .receipt-content h2 {
            font-size: 20px;
            margin-bottom: 10px; 
            text-align: center; 
        }

        .receipt-content p {
            margin: 5px 0; 
        }

        .receipt-item {
            margin-bottom: 15px; 
            border-bottom: 1px solid #ccc; 
            padding-bottom: 5px; 
        }

        .total-price {
            font-weight: bold; 
            font-size: 22px; 
            text-align: right; 
            color: #4B4540; 
            margin-top: 15px; 
        }

    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php" class="nav-link">หน้าแรก</a>
        <a href="menu.php" class="nav-link">จัดการเมนู</a>
        <a href="stock.php" class="nav-link">จัดการสต๊อก</a>
        <a href="order.php" class="nav-link">รายการสั่งซื้อ</a>
        <a href="bill.php" class="nav-link active">ใบเสร็จ</a>
        <a href="receipts.php" class="nav-link">ใบเสร็จทั้งหมด</a>
    </div>
    
    <div class="container">
        <h1 class="page-title">ใบเสร็จการสั่งซื้อ</h1>

        <?php
        if (isset($_GET['id'])) {
            $orderId = $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                echo "<h3 class='error-message'>ไม่พบคำสั่งซื้อนี้</h3>";
                exit;
            }

            // สร้างข้อความใบเสร็จ
            $receipt = "บิล ID: {$order['id']}\n";
            
            // ดึงข้อมูลรายการสินค้า
            $stmtItems = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmtItems->execute([$orderId]);

            $totalReceiptPrice = 0; // ตัวแปรสำหรับคำนวณราคาสุทธิในใบเสร็จ

            while ($item = $stmtItems->fetch(PDO::FETCH_ASSOC)) {
                $productStmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                $productStmt->execute([$item['product_id']]);
                $product = $productStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($product) {
                    // คำนวณราคา
                    $subtotal = $item['quantity'] * $product['price'];
                    $totalReceiptPrice += $subtotal; // บวกยอดรวม
                    // เพิ่มรายการสินค้าในใบเสร็จ
                    $receipt .= "{$product['name']}  จำนวน: {$item['quantity']}  รวม: {$subtotal} บาท\n";
                }
            }

            // เพิ่มราคารวมสุดท้าย
            $receipt .= "รวมทั้งหมด: {$totalReceiptPrice} บาท\n";

            // แสดงใบเสร็จ
            echo "<pre class='receipt-content'>{$receipt}</pre>";
        }
        ?>
    </div>
</body>
</html>
