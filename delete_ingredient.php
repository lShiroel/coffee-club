<?php
include 'db_connection.php';
session_start();

if (isset($_GET['id'])) {
    $ingredientId = $_GET['id'];

    // ลบจากตาราง menu_ingredients ก่อนเพราะมีการอ้างอิง
    $stmt = $conn->prepare("DELETE FROM menu_ingredients WHERE ingredient_id = ?");
    $stmt->execute([$ingredientId]);

    // ลบจากตาราง ingredients
    $stmt = $conn->prepare("DELETE FROM ingredients WHERE id = ?");
    $stmt->execute([$ingredientId]);

    header("Location: stock.php");
}
?>
