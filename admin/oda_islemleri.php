<?php
session_start();
require_once '../config/db.php';

// 1. Güvenlik Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header("Location: ../index.php");
    exit;
}

// 2. Oda Ekleme İşlemi (POST)
$mesaj = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['oda_ekle'])) {
    $oda_no = trim($_POST['room_number']);
    $kapasite = (int)$_POST['capacity'];

    if (!empty($oda_no) && $kapasite > 0) {
        try {
            // Yeni oda eklerken mevcut sayı (current_count) 0 olarak başlar
            $sql = "INSERT INTO rooms (room_number, capacity, current_count) VALUES (?, ?, 0)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$oda_no, $kapasite]);
            $mesaj = "<div class='alert alert-success'>Oda başarıyla oluşturuldu!</div>";
        } catch (PDOException $e) {
            $mesaj = "<div class='alert alert-danger'>Hata: Bu oda numarası zaten var olabilir.</div>";
        }
    } else {
        $mesaj = "<div class='alert alert-warning'>Lütfen geçerli bilgiler girin.</div>";
    }
}

// 3. Oda Silme İşlemi (GET)
if (isset($_GET['sil_id'])) {
    $sil_id = $_GET['sil_id'];
    
    // Odayı sil (İçinde öğrenci varsa veritabanı Foreign Key ayarı sayesinde öğrencinin odası NULL olur veya hata verir)
    try {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
        $stmt->execute([$sil_id]);
        header("Location: oda_islemleri.php");
        exit;
    } catch (PDOException $e) {
        $mesaj = "<div class='alert alert-danger'>Hata: Bu odada öğrenciler olabilir, önce onları çıkarın.</div>";
    }
}

// 4. Odaları Listeleme (READ)
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY room_number ASC");
$odalar = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Oda Yönetimi - Yurt Otomasyonu</title>
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
                <a href="ogrenci_islemleri.php"><i class="fas fa-user-graduate me-2"></i> Öğrenci İşlemleri</a>
                <a href="oda_islemleri.php" class="active"><i class="fas fa-bed me-2"></i> Oda Yönetimi</a>
                <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                <a href="yoklama_raporu.php"><i class="fas fa-calendar-check me-2"></i> Yoklama Raporu</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Oda Yönetimi</h2>
            
            <?php echo $mesaj; ?>

            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-plus-circle"></i> Yeni Oda Ekle
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="row g-3">
                        <div class="col-md-5">
                            <label>Oda Numarası (Örn: A-101)</label>
                            <input type="text" name="room_number" class="form-control" required>
                        </div>
                        <div class="col-md-5">
                            <label>Kapasite (Kişi Sayısı)</label>
                            <input type="number" name="capacity" class="form-control" min="1" max="10" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="oda_ekle" class="btn btn-primary w-100">Oluştur</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Mevcut Odalar</h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Oda No</th>
                                <th>Kapasite</th>
                                <th>Doluluk</th>
                                <th>Durum</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($odalar) > 0): ?>
                                <?php foreach ($odalar as $oda): ?>
                                    <?php 
                                        // Doluluk oranına göre renk belirleme
                                        $doluluk_orani = ($oda->current_count / $oda->capacity) * 100;
                                        $renk = "bg-success";
                                        if($doluluk_orani >= 100) $renk = "bg-danger";
                                        elseif($doluluk_orani > 50) $renk = "bg-warning";
                                    ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($oda->room_number); ?></td>
                                        <td><?php echo $oda->capacity; ?> Kişilik</td>
                                        <td>
                                            <?php echo $oda->current_count; ?> / <?php echo $oda->capacity; ?>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar <?php echo $renk; ?>" role="progressbar" 
                                                     style="width: <?php echo $doluluk_orani; ?>%;">
                                                    %<?php echo round($doluluk_orani); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="?sil_id=<?php echo $oda->room_id; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Bu odayı silmek istediğinize emin misiniz?');">
                                                <i class="fas fa-trash"></i> Sil
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-muted">Henüz hiç oda eklenmemiş.</td>
                                </tr>
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