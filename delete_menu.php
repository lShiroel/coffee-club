<?php
include 'db_connection.php';
session_start();

if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    if (isset($_POST['confirm_delete'])) {
        // ลบจากตาราง order_items ก่อนเพราะมีการอ้างอิง
        $stmt = $conn->prepare("DELETE FROM order_items WHERE product_id = ?");
        $stmt->execute([$productId]);

        // ลบจากตาราง menu_ingredients
        $stmt = $conn->prepare("DELETE FROM menu_ingredients WHERE product_id = ?");
        $stmt->execute([$productId]);

        // ลบจากตาราง products
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);

        header("Location: menu.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ลบเมนู</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #ff5c5c;
        }

        p {
            font-size: 18px;
            margin-bottom: 20px;
            color: #555;
        }

        form {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease;
        }

        form:hover {
            transform: translateY(-5px);
        }

        button, a {
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 25px;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease, transform 0.3s ease;
            cursor: pointer;
        }

        button {
            background-color: #ff5c5c;
            color: #fff;
            border: none;
        }

        button:hover {
            background-color: #ff3333;
            transform: scale(1.05);
        }

        a {
            background-color: #ddd;
            color: #555;
        }

        a:hover {
            background-color: #ccc;
            color: #333;
            transform: scale(1.05);
        }

        a, button {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* เพิ่มเอฟเฟกต์ของการเลื่อน */
        form {
            animation: fadeInUp 0.8s ease forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            0% {
                transform: translateY(30px);
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
    <form method="post">
        <h1>ยืนยันการลบเมนู</h1>
        <p>คุณต้องการลบเมนูนี้หรือไม่?</p>
        <button type="submit" name="confirm_delete">ยืนยันการลบ</button>
        <a href="menu.php">ยกเลิก</a>
    </form>
</body>
</html>
