<?php
session_start();
require_once '../config/db.php';

// Güvenlik
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) { header("Location: ../index.php"); exit; }

// ID Kontrolü
if (!isset($_GET['id'])) { header("Location: personel_islemleri.php"); exit; }
$id = $_GET['id'];

// Mevcut Bilgileri Çek
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role_id = 2");
$stmt->execute([$id]);
$personel = $stmt->fetch();

if (!$personel) { header("Location: personel_islemleri.php"); exit; }

$mesaj = "";

// GÜNCELLEME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ad = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    
    // Şifre alanı doluysa sorguya ekle, boşsa ekleme
    $sifre_sql = "";
    $params = [$ad, $email]; // İlk iki soru işareti: İsim ve Email
    
    if (!empty($_POST['password'])) {
        $sifre_sql = ", password = ?";
        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }
    
    $params[] = $id; // Son soru işareti: WHERE user_id = ?

    try {
        // HATALI OLAN KISIM DÜZELTİLDİ: "email = ?" şeklinde soru işareti eklendi.
        $sql = "UPDATE users SET full_name = ?, email = ?" . $sifre_sql . " WHERE user_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Başarılıysa listeye dön
        header("Location: personel_islemleri.php?msg=updated");
        exit;
    } catch (PDOException $e) {
        // Hatanın gerçek nedenini görmek için e->getMessage() ekledik (Geliştirme aşamasında)
        $mesaj = "<div class='alert alert-danger'>Hata: E-posta kullanılıyor olabilir veya veritabanı hatası.<br><small>".$e->getMessage()."</small></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Personel Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-sm mx-auto" style="max-width: 500px;">
            <div class="card-header bg-primary text-white">
                <h4>Personel Düzenle</h4>
            </div>
            <div class="card-body">
                <?php echo $mesaj; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Ad Soyad</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($personel->full_name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>E-Posta</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($personel->email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Yeni Şifre <small class="text-muted">(Değiştirmek istemiyorsanız boş bırakın)</small></label>
                        <input type="text" name="password" class="form-control" placeholder="******">
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="personel_islemleri.php" class="btn btn-secondary">İptal</a>
                        <button type="submit" class="btn btn-success">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>