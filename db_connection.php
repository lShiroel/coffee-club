<?php
$host = 'localhost'; // ชื่อโฮสต์
$dbname = 'coffee club'; // ชื่อฐานข้อมูล
$username = 'root'; // ชื่อผู้ใช้
$password = ''; // รหัสผ่าน

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
