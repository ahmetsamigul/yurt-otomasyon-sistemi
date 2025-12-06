<?php
session_start();
require_once '../config/db.php';

// 1. Güvenlik
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header("Location: ../index.php");
    exit;
}

$mesaj = "";

// 2. ÖĞRENCİ EKLEME (Transaction Kullanımı)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ogrenci_ekle'])) {
    $ad = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $sifre = trim($_POST['password']); // Öğrenci için varsayılan şifre atanabilir
    $bolum = trim($_POST['department']);
    $tel = trim($_POST['phone']);
    $oda_id = $_POST['room_id']; // Seçilen odanın ID'si

    if (!empty($ad) && !empty($email) && !empty($oda_id)) {
        try {
            // TRANSACTION BAŞLAT (Hepsi ya olur ya hiçbiri olmaz)
            $pdo->beginTransaction();

            // A. Kullanıcıyı Ekle (Users Tablosu)
            $hashli_sifre = password_hash($sifre, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role_id) VALUES (?, ?, ?, 3)");
            $stmt->execute([$ad, $email, $hashli_sifre]);
            $yeni_user_id = $pdo->lastInsertId(); // Eklenen kişinin ID'sini al

            // B. Öğrenci Detayını Ekle (Students Tablosu)
            $stmt = $pdo->prepare("INSERT INTO students (user_id, department, phone, room_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$yeni_user_id, $bolum, $tel, $oda_id]);

            // C. Odanın Doluluk Sayısını Artır (Rooms Tablosu)
            $stmt = $pdo->prepare("UPDATE rooms SET current_count = current_count + 1 WHERE room_id = ?");
            $stmt->execute([$oda_id]);

            // Hata yoksa ONAYLA
            $pdo->commit();
            $mesaj = "<div class='alert alert-success'>Öğrenci başarıyla kaydedildi ve odaya yerleştirildi!</div>";

        } catch (Exception $e) {
            // Hata varsa GERİ AL
            $pdo->rollBack();
            $mesaj = "<div class='alert alert-danger'>Hata oluştu: " . $e->getMessage() . "</div>";
        }
    } else {
        $mesaj = "<div class='alert alert-warning'>Lütfen zorunlu alanları doldurun.</div>";
    }
}

// 3. ÖĞRENCİ SİLME
if (isset($_GET['sil_id'])) {
    $sil_user_id = $_GET['sil_id']; // URL'den gelen user_id

    try {
        // Önce öğrencinin kaldığı odayı bul (Sayacı düşürmek için)
        $stmt = $pdo->prepare("SELECT room_id FROM students WHERE user_id = ?");
        $stmt->execute([$sil_user_id]);
        $ogrenci = $stmt->fetch();

        $pdo->beginTransaction();

        // Öğrenciyi Sil (Users tablosundan silince Students da silinir - Cascade)
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role_id = 3");
        $stmt->execute([$sil_user_id]);

        // Odanın doluluğunu 1 azalt
        if ($ogrenci && $ogrenci->room_id) {
            $stmt = $pdo->prepare("UPDATE rooms SET current_count = current_count - 1 WHERE room_id = ?");
            $stmt->execute([$ogrenci->room_id]);
        }

        $pdo->commit();
        header("Location: ogrenci_islemleri.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $mesaj = "Hata: " . $e->getMessage();
    }
}

// 4. LİSTELEME İŞLEMLERİ
// A. Müsait Odaları Getir (Dropdown için)
$musait_odalar = $pdo->query("SELECT * FROM rooms WHERE current_count < capacity ORDER BY room_number ASC")->fetchAll();

// B. Öğrencileri Listele (Tablo için - JOIN Kullanarak)
$sql = "SELECT users.user_id, users.full_name, users.email, students.department, students.phone, rooms.room_number 
        FROM users 
        JOIN students ON users.user_id = students.user_id 
        LEFT JOIN rooms ON students.room_id = rooms.room_id 
        WHERE users.role_id = 3 
        ORDER BY users.user_id DESC";
$ogrenciler = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Öğrenci İşlemleri - Yurt Otomasyonu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; color: white; }
        .sidebar a { color: #cfd2d6; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #495057; color: white; border-left: 4px solid #0d6efd; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0">
            <h4 class="text-center py-4 border-bottom border-secondary">Yurt v1.0</h4>
            <div class="d-flex flex-column">
                <a href="dashboard.php"><i class="fas fa-home me-2"></i> Anasayfa</a>
                <a href="personel_islemleri.php"><i class="fas fa-user-tie me-2"></i> Personel İşlemleri</a>
                <a href="ogrenci_islemleri.php" class="active"><i class="fas fa-user-graduate me-2"></i> Öğrenci İşlemleri</a>
                <a href="oda_islemleri.php"><i class="fas fa-bed me-2"></i> Oda Yönetimi</a>
                <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                <a href="yoklama_raporu.php"><i class="fas fa-calendar-check me-2"></i> Yoklama Raporu</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Öğrenci Kaydı ve Yerleştirme</h2>
            
            <?php echo $mesaj; ?>

            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-user-plus"></i> Yeni Öğrenci Kaydet
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="row g-3">
                        <div class="col-md-4">
                            <label>Ad Soyad</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>E-Posta</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Şifre</label>
                            <input type="text" name="password" class="form-control" placeholder="123456" required>
                        </div>
                        <div class="col-md-4">
                            <label>Bölüm</label>
                            <input type="text" name="department" class="form-control" placeholder="Bilgisayar Müh.">
                        </div>
                        <div class="col-md-4">
                            <label>Telefon</label>
                            <input type="text" name="phone" class="form-control" placeholder="0555...">
                        </div>
                        <div class="col-md-4">
                            <label>Oda Seçimi (Sadece Müsait Olanlar)</label>
                            <select name="room_id" class="form-select" required>
                                <option value="">Oda Seçiniz...</option>
                                <?php foreach ($musait_odalar as $oda): ?>
                                    <option value="<?php echo $oda->room_id; ?>">
                                        <?php echo $oda->room_number; ?> (Kapasite: <?php echo $oda->capacity - $oda->current_count; ?> Kişi Kaldı)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" name="ogrenci_ekle" class="btn btn-primary">
                                <i class="fas fa-save"></i> Kaydet ve Yerleştir
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Kayıtlı Öğrenciler</h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Ad Soyad</th>
                                <th>Bölüm</th>
                                <th>Telefon</th>
                                <th>Oda</th>
                                <th>E-Posta</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($ogrenciler) > 0): ?>
                                <?php foreach ($ogrenciler as $ogr): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ogr->full_name); ?></td>
                                        <td><?php echo htmlspecialchars($ogr->department); ?></td>
                                        <td><?php echo htmlspecialchars($ogr->phone); ?></td>
                                        <td class="fw-bold text-success">
                                            <?php echo $ogr->room_number ? $ogr->room_number : '<span class="text-danger">Odası Yok</span>'; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($ogr->email); ?></td>
                                        <td>
                                            <a href="?sil_id=<?php echo $ogr->user_id; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Silmek istediğinize emin misiniz? Odanın kontenjanı 1 azaltılacak.');">
                                                Sil
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">Kayıtlı öğrenci yok.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>