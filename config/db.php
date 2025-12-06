<?php
$host = "localhost";
$dbname = "yurt_yonetim_db";
$username = "root";
$password = "root"; 

try {
    // Mac MAMP genellikle MySQL için 8889 portunu kullanır. O yüzden portu ekliyoruz.
    $pdo = new PDO("mysql:host=$host;port=8889;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    
    // Bağlantı başarılıysa ekrana bir şey yazmasın, sessizce devam etsin.
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>