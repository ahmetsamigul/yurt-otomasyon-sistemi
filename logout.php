<?php
session_start();       // Oturumu başlat
session_destroy();     // Tüm oturum verilerini (giriş bilgilerini) sil
header("Location: index.php"); // Kullanıcıyı giriş sayfasına geri gönder
exit;
?>