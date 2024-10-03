<?php 
include 'db_connection.php'; 
session_start();

if (isset($_POST['export_excel'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=receipts.xls");
    header("Pragma: no-cache");
    header("Expires: 0");


    echo "<table border='1'>
            <tr>
                <th>บิล ID</th>
                <th>รวมราคา</th>
                <th>วันที่ซื้อ</th>
                <th>รายละเอียด</th>
            </tr>";

    $stmt = $conn->prepare("SELECT id, total_price, created_at FROM orders");
    $stmt->execute();

    while ($order = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $formattedDate = date("d/m/Y H:i:s", strtotime($order['created_at']));

        $orderId = $order['id'];
        $itemsStmt = $conn->prepare("SELECT p.name, oi.quantity, p.price, (oi.quantity * p.price) AS total FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $itemsStmt->execute([$orderId]);

        $details = "";
        while ($item = $itemsStmt->fetch(PDO::FETCH_ASSOC)) {
            $details .= "ชื่อสินค้า: {$item['name']}, ราคา: {$item['price']} บาท, จำนวน: {$item['quantity']}, ราคารวม: {$item['total']} บาท<br/>";
        }

        echo "<tr>
                <td>{$order['id']}</td>
                <td>{$order['total_price']} บาท</td>
                <td>{$formattedDate}</td>
                <td>{$details}</td>
              </tr>";
    }
    echo "</table>";
    exit(); 
}


if (isset($_POST['reset_ids'])) {
    $conn->exec("DELETE FROM order_items");
    $conn->exec("DELETE FROM orders");
    $conn->exec("ALTER TABLE orders AUTO_INCREMENT = 1");
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบเสร็จทั้งหมด</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
    .container {
    max-width: 1000px;
    margin: 50px auto;
    padding: 20px;
    background-color: #f9f9f9;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    }

    .page-title {
        font-size: 36px;
        color: #4B4540;
        text-align: center;
        margin-bottom: 30px;
    }

    .form-buttons {
        text-align: center;
        margin-bottom: 20px;
    }

    .btn {
        background-color: #A08162;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin: 0 10px;
    }

    .btn:hover {
        background-color: #4B4540;
    }

    .receipt-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        background-color: white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .receipt-table th, .receipt-table td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
    }

    .receipt-table th {
        background-color: #f2f2f2;
        font-weight: bold;
        color: #333;
    }

    .receipt-table td {
        color: #4B4540;
    }

    .receipt-table tr:hover {
        background-color: #f9f9f9;
    }

    .receipt-table td:nth-child(4) {
        white-space: pre-line;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .page-title {
            font-size: 28px;
        }

        .btn {
            font-size: 14px;
            padding: 8px 15px;
        }

        .receipt-table th, .receipt-table td {
            font-size: 14px;
            padding: 10px;
        }
    }
</style>
<body>
    <div class="navbar">
        <a href="index.php" class="nav-link">หน้าแรก</a>
        <a href="menu.php" class="nav-link">จัดการเมนู</a>
        <a href="stock.php" class="nav-link">จัดการสต๊อก</a>
        <a href="order.php" class="nav-link">รายการสั่งซื้อ</a>
        <a href="bill.php" class="nav-link">ใบเสร็จ</a>
        <a href="receipts.php" class="nav-link active">ใบเสร็จทั้งหมด</a>
    </div>
    <div class="container">
        <h1 class="page-title">ใบเสร็จทั้งหมด</h1>

        <form method="post" class="form-buttons">
            <button type="submit" name="export_excel" class="btn btn-download">ดาวน์โหลดใบเสร็จ (Excel)</button>
            <button type="submit" name="reset_ids" class="btn btn-reset">รีเซ็ตบิล ID</button>
        </form>

        <table class="receipt-table">
            <tr>
                <th>บิล ID</th>
                <th>รวมราคา</th>
                <th>วันที่ซื้อ</th>
                <th>รายละเอียด</th>
            </tr>
            <?php

            $stmt = $conn->prepare("SELECT id, total_price, created_at FROM orders");
            $stmt->execute();

            while ($order = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $formattedDate = date("d/m/Y H:i:s", strtotime($order['created_at']));

                $orderId = $order['id'];
                $itemsStmt = $conn->prepare("SELECT p.name, oi.quantity, p.price, (oi.quantity * p.price) AS total FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                $itemsStmt->execute([$orderId]);

                $details = "";
                while ($item = $itemsStmt->fetch(PDO::FETCH_ASSOC)) {
                    $details .= " {$item['name']} จำนวน {$item['quantity']} ราคา {$item['total']} บาท<br/>";
                }

                echo "<tr>
                        <td>{$order['id']}</td>
                        <td>{$order['total_price']} บาท</td>
                        <td>{$formattedDate}</td>
                        <td>{$details}</td>
                      </tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
