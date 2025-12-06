<?php
require_once 'config/db.php';

// 123456 şifresinin PHP tarafından oluşturulmuş güvenli hali
$yeni_sifre = "123456";
$hashli_sifre = password_hash($yeni_sifre, PASSWORD_DEFAULT);
$email = "admin@yurt.com";

try {
    // Veritabanındaki şifreyi güncelle
    $stmt = $pdo->prepare("UPDATE users SET password = :pass WHERE email = :email");
    $sonuc = $stmt->execute(['pass' => $hashli_sifre, 'email' => $email]);

    if ($stmt->rowCount() > 0) {
        echo "<h1>✅ Başarılı!</h1>";
        echo "<p>Admin ($email) şifresi '<b>123456</b>' olarak güncellendi.</p>";
        echo "<p>Şimdi <a href='index.php'>Giriş Yapmayı Dene</a></p>";
    } else {
        echo "<h1>⚠️ Uyarı</h1>";
        echo "<p>Kullanıcı bulundu ama şifre zaten aynı veya güncelleme yapılamadı. Kullanıcı e-postası doğru mu?</p>";
    }
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>