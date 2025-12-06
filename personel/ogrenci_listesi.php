<?php
session_start();
require_once '../config/db.php';

// 1. Güvenlik Kontrolü (Sadece Personel)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header("Location: ../index.php");
    exit;
}

// 2. Arama İşlemi
$arama_terimi = "";
$params = [];

$sql = "SELECT u.full_name, u.email, s.department, s.phone, r.room_number 
        FROM users u 
        JOIN students s ON u.user_id = s.user_id 
        LEFT JOIN rooms r ON s.room_id = r.room_id 
        WHERE u.role_id = 3";

// Eğer arama kutusuna bir şey yazıldıysa SQL'i güncelle
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $arama_terimi = trim($_GET['q']);
    $sql .= " AND (u.full_name LIKE ? OR s.department LIKE ? OR r.room_number LIKE ?)";
    $params[] = "%$arama_terimi%";
    $params[] = "%$arama_terimi%";
    $params[] = "%$arama_terimi%";
}

$sql .= " ORDER BY u.full_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ogrenciler = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Öğrenci Listesi - Personel Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #2c3e50; color: white; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; color: white; border-left: 4px solid #1abc9c; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0">
            <h4 class="text-center py-4 border-bottom border-secondary">Personel</h4>
            <div class="d-flex flex-column">
                <a href="dashboard.php"><i class="fas fa-home me-2"></i> Anasayfa</a>
                <a href="yoklama_al.php"><i class="fas fa-clipboard-check me-2"></i> Yoklama Al</a>
                <a href="ogrenci_listesi.php" class="active"><i class="fas fa-list me-2"></i> Öğrenci Listesi</a>
                <a href="ariza_takip.php"><i class="fas fa-tools me-2"></i> Arıza Takip</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Öğrenci Listesi</h2>

            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="" class="d-flex gap-2">
                        <input type="text" name="q" class="form-control" placeholder="Öğrenci adı, bölüm veya oda no ara..." value="<?php echo htmlspecialchars($arama_terimi); ?>">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Ara</button>
                        <?php if($arama_terimi): ?>
                            <a href="ogrenci_listesi.php" class="btn btn-secondary">Temizle</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Ad Soyad</th>
                                <th>Bölüm</th>
                                <th>Telefon</th>
                                <th>Oda Numarası</th>
                                <th>E-Posta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($ogrenciler) > 0): ?>
                                <?php foreach ($ogrenciler as $ogr): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ogr->full_name); ?></td>
                                        <td><?php echo htmlspecialchars($ogr->department); ?></td>
                                        <td><?php echo htmlspecialchars($ogr->phone); ?></td>
                                        <td>
                                            <?php if($ogr->room_number): ?>
                                                <span class="badge bg-success"><?php echo $ogr->room_number; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Oda Yok</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($ogr->email); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Kriterlere uygun öğrenci bulunamadı.</td>
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