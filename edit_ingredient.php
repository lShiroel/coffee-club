<?php
include 'db_connection.php';
session_start();

if (isset($_GET['id'])) {
    $ingredientId = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM ingredients WHERE id = ?");
    $stmt->execute([$ingredientId]);
    $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_POST['update_ingredient'])) {
    $name = $_POST['name'];
    $stock = $_POST['stock'];

    $stmt = $conn->prepare("UPDATE ingredients SET name = ?, stock = ? WHERE id = ?");
    $stmt->execute([$name, $stock, $ingredientId]);

    header("Location: stock.php");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>อัปเดตวัตถุดิบ</title>
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
        input[type="text"], input[type="number"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
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
        <h1>อัปเดตวัตถุดิบ</h1>
        <form method="post">
            <input type="text" name="name" value="<?= htmlspecialchars($ingredient['name']) ?>" required>
            <input type="number" name="stock" value="<?= htmlspecialchars($ingredient['stock']) ?>" required>
            <button type="submit" name="update_ingredient">อัปเดตวัตถุดิบ</button>
        </form>
    </div>
    <a class="back-link" href="stock.php">กลับไปยังรายการวัตถุดิบ</a>
</body>
</html>
