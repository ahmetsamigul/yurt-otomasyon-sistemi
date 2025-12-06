<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) { header("Location: ../index.php"); exit; }

// Tarih Seçimi (Varsayılan bugün)
$tarih = isset($_GET['tarih']) ? $_GET['tarih'] : date("Y-m-d");

// Seçilen tarihe göre rapor çek
$sql = "SELECT u.full_name, r.room_number, a.status, p.full_name as alan_kisi 
        FROM attendance a
        JOIN students s ON a.student_id = s.student_id
        JOIN users u ON s.user_id = u.user_id
        LEFT JOIN rooms r ON s.room_id = r.room_id
        LEFT JOIN users p ON a.created_by = p.user_id
        WHERE a.date = ? ORDER BY r.room_number ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$tarih]);
$yoklamalar = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yoklama Raporu - Müdür</title>
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
                <a href="oda_islemleri.php"><i class="fas fa-bed me-2"></i> Oda Yönetimi</a>
                <a href="duyurular.php"><i class="fas fa-bullhorn me-2"></i> Duyurular</a>
                <a href="yoklama_raporu.php" class="active"><i class="fas fa-calendar-check me-2"></i> Yoklama Raporu</a>
                <a href="../logout.php" class="mt-5 text-danger"><i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2 class="mb-4">Günlük Yoklama Raporu</h2>
            
            <form method="GET" class="row g-3 mb-4 bg-white p-3 shadow-sm rounded">
                <div class="col-auto">
                    <label class="col-form-label fw-bold">Tarih Seçin:</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="tarih" class="form-control" value="<?php echo $tarih; ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Raporu Getir</button>
                </div>
            </form>

            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if(count($yoklamalar) > 0): ?>
                        <table class="table table-bordered">
                            <thead class="table-light"><tr><th>Öğrenci</th><th>Oda</th><th>Durum</th><th>Yoklamayı Alan</th></tr></thead>
                            <tbody>
                                <?php foreach($yoklamalar as $y): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($y->full_name); ?></td>
                                    <td><?php echo $y->room_number; ?></td>
                                    <td>
                                        <?php 
                                        if($y->status == 'var') echo '<span class="badge bg-success">Var</span>';
                                        elseif($y->status == 'yok') echo '<span class="badge bg-danger">Yok</span>';
                                        else echo '<span class="badge bg-warning text-dark">İzinli</span>';
                                        ?>
                                    </td>
                                    <td class="text-muted text-small"><?php echo $y->alan_kisi; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-warning">Seçilen tarihte yoklama kaydı bulunamadı.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>