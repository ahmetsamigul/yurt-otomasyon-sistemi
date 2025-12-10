<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) { header("Location: ../index.php"); exit; }
if (!isset($_GET['id'])) { header("Location: ogrenci_islemleri.php"); exit; }
$id = $_GET['id'];

// Mevcut Bilgileri Çek
$sql = "SELECT u.*, s.department, s.phone, s.room_id FROM users u JOIN students s ON u.user_id = s.user_id WHERE u.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$ogr = $stmt->fetch();

if (!$ogr) { header("Location: ogrenci_islemleri.php"); exit; }

// Müsait Odaları Çek (Listede mevcut odası da görünsün diye mantık kuruyoruz)
$odalar = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC")->fetchAll();

$mesaj = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ad = $_POST['full_name'];
    $email = $_POST['email'];
    $bolum = $_POST['department'];
    $tel = $_POST['phone'];
    $yeni_oda = $_POST['room_id'];
    $eski_oda = $ogr->room_id;

    try {
        $pdo->beginTransaction();

        // 1. Kullanıcı Bilgilerini Güncelle
        $sqlUser = "UPDATE users SET full_name = ?, email = ? WHERE user_id = ?";
        $pdo->prepare($sqlUser)->execute([$ad, $email, $id]);

        // Şifre varsa güncelle
        if (!empty($_POST['password'])) {
            $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?")->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), $id]);
        }

        // 2. Öğrenci Detaylarını Güncelle
        $sqlStudent = "UPDATE students SET department = ?, phone = ?, room_id = ? WHERE user_id = ?";
        $pdo->prepare($sqlStudent)->execute([$bolum, $tel, $yeni_oda, $id]);

        // 3. ODA DEĞİŞİKLİĞİ VARSA KAPASİTELERİ GÜNCELLE
        if ($yeni_oda != $eski_oda) {
            // Eski odadan 1 düş (Eğer eski odası varsa)
            if ($eski_oda) {
                $pdo->prepare("UPDATE rooms SET current_count = current_count - 1 WHERE room_id = ?")->execute([$eski_oda]);
            }
            // Yeni odaya 1 ekle
            $pdo->prepare("UPDATE rooms SET current_count = current_count + 1 WHERE room_id = ?")->execute([$yeni_oda]);
        }

        $pdo->commit();
        header("Location: ogrenci_islemleri.php?msg=updated");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $mesaj = "<div class='alert alert-danger'>Hata: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Öğrenci Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="container mt-4">
        <div class="card shadow-sm mx-auto" style="max-width: 600px;">
            <div class="card-header bg-warning text-dark">
                <h4>Öğrenci Bilgilerini Düzenle</h4>
            </div>
            <div class="card-body">
                <?php echo $mesaj; ?>
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label>Ad Soyad</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($ogr->full_name); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>E-Posta</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($ogr->email); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label>Bölüm</label>
                        <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($ogr->department); ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Telefon</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($ogr->phone); ?>">
                    </div>
                    <div class="col-md-12">
                        <label>Oda Seçimi (Kapasite Kontrolü Yapın)</label>
                        <select name="room_id" class="form-select" required>
                            <?php foreach ($odalar as $oda): ?>
                                <?php 
                                    $dolu = ($oda->current_count >= $oda->capacity);
                                    $kendi_odasi = ($oda->room_id == $ogr->room_id);
                                    $disabled = ($dolu && !$kendi_odasi) ? 'disabled' : '';
                                    $selected = $kendi_odasi ? 'selected' : '';
                                    $bilgi = $dolu ? '(DOLU)' : '(Müsait)';
                                ?>
                                <option value="<?php echo $oda->room_id; ?>" <?php echo $selected; ?> <?php echo $disabled; ?>>
                                    <?php echo $oda->room_number . " - " . $oda->current_count . "/" . $oda->capacity . " " . $bilgi; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label>Yeni Şifre <small class="text-muted">(Opsiyonel)</small></label>
                        <input type="text" name="password" class="form-control" placeholder="******">
                    </div>
                    <div class="col-12 d-flex justify-content-between mt-3">
                        <a href="ogrenci_islemleri.php" class="btn btn-secondary">İptal</a>
                        <button type="submit" class="btn btn-primary">Kaydet ve Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>