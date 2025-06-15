<?php
require_once 'User.php';
require_once 'Location.php';
require_once 'SessionManager.php';

SessionManager::start();

// Giriş yapılmamışsa login sayfasına yönlendir
if (!SessionManager::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$location = new Location();
$user_id = SessionManager::getUserId();


// Filtreleme
$filters = [
    'city' => $_GET['city'] ?? '',
    'country' => $_GET['country'] ?? '',
    'genre' => $_GET['genre'] ?? '',
    'visited' => $_GET['visited'] ?? '',
    'title' => $_GET['title'] ?? ''
];

// Lokasyonları getir
$locations = $location->getAllLocations($user_id, $filters);

// İstatistikleri getir
$stats = $location->getStatistics($user_id);

// Türleri getir
$genres = $location->getGenres();

// Başarı/hata mesajları
$success_message = "";
$error_message = "";

if(isset($_GET['success'])) {
    switch($_GET['success']) {
        case 'added':
            $success_message = "Lokasyon başarıyla eklendi!";
            break;
        case 'updated':
            $success_message = "Lokasyon başarıyla güncellendi!";
            break;
        case 'deleted':
            $success_message = "Lokasyon başarıyla silindi!";
            break;
    }
}

if(isset($_GET['error'])) {
    $error_message = "Bir hata oluştu. Lütfen tekrar deneyin.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dizi/Film Lokasyonları - Ana Sayfa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .navbar {
            background: var(--primary-gradient) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .stats-icon.total { background: var(--primary-gradient); }
        .stats-icon.visited { background: var(--secondary-gradient); }
        .stats-icon.percentage { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        
        .location-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .location-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .location-image {
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .visited-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(40, 167, 69, 0.9);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .not-visited-badge {
            background: rgba(220, 53, 69, 0.9);
        }
        
        .filter-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-film me-2"></i>Dizi/Film Lokasyonları
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home me-1"></i>Ana Sayfa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_location.php">
                            <i class="fas fa-plus me-1"></i>Lokasyon Ekle
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo SessionManager::getUserName(); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog me-2"></i>Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Çıkış Yap</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Hoş Geldin Mesajı -->
        <div class="row mb-4">
            <div class="col-12">
          <h2 class="fw-bold mb-1">Hoş Geldin, <?php echo SessionManager::getFullName() ?: SessionManager::getUserName(); ?>! 🎬</h2>
                <p class="text-muted">Dizi ve film lokasyonlarını keşfet, kaydet ve takip et.</p>
            </div>
        </div>

        <!-- Başarı/Hata Mesajları -->
        <?php if(!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- İstatistikler -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stats-card p-4">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon total">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0 fw-bold"><?php echo $stats['total']; ?></h3>
                            <p class="text-muted mb-0">Toplam Lokasyon</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="stats-card p-4">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon visited">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0 fw-bold"><?php echo $stats['visited']; ?></h3>
                            <p class="text-muted mb-0">Ziyaret Edilen</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="stats-card p-4">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon percentage">
                            <i class="fas fa-percent"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="mb-0 fw-bold">
                                <?php echo $stats['total'] > 0 ? round(($stats['visited'] / $stats['total']) * 100) : 0; ?>%
                            </h3>
                            <p class="text-muted mb-0">Tamamlanma</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtreleme -->
        <div class="filter-section">
            <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filtrele</h5>
            <form method="GET" action="index.php">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <input type="text" class="form-control" name="title" 
                               placeholder="Dizi/Film Adı" value="<?php echo htmlspecialchars($filters['title']); ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <input type="text" class="form-control" name="city" 
                               placeholder="Şehir" value="<?php echo htmlspecialchars($filters['city']); ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <input type="text" class="form-control" name="country" 
                               placeholder="Ülke" value="<?php echo htmlspecialchars($filters['country']); ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <select class="form-select" name="genre">
                            <option value="">Tüm Türler</option>
                            <?php foreach($genres as $genre): ?>
                                <option value="<?php echo $genre; ?>" 
                                        <?php echo $filters['genre'] == $genre ? 'selected' : ''; ?>>
                                    <?php echo $genre; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <select class="form-select" name="visited">
                            <option value="">Tüm Durumlar</option>
                            <option value="1" <?php echo $filters['visited'] === '1' ? 'selected' : ''; ?>>Ziyaret Edildi</option>
                            <option value="0" <?php echo $filters['visited'] === '0' ? 'selected' : ''; ?>>Ziyaret Edilmedi</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-3">
                        <button type="submit" class="btn btn-gradient w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Lokasyon Listesi -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">
                        <i class="fas fa-map-marked-alt me-2"></i>
                        Lokasyonlarım (<?php echo count($locations); ?>)
                    </h4>
                    <a href="add_location.php" class="btn btn-gradient">
                        <i class="fas fa-plus me-2"></i>Yeni Lokasyon Ekle
                    </a>
                </div>
            </div>
        </div>

        <?php if(empty($locations)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="empty-state">
                        <i class="fas fa-map-marker-alt"></i>
                        <h4>Henüz lokasyon eklemediniz</h4>
                        <p>İlk lokasyonunuzu ekleyerek başlayın!</p>
                        <a href="add_location.php" class="btn btn-gradient btn-lg">
                            <i class="fas fa-plus me-2"></i>İlk Lokasyonu Ekle
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach($locations as $loc): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="location-card">
                            <div class="position-relative">
                                <?php if(!empty($loc['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($loc['image_url']); ?>" 
                                         class="card-img-top location-image" alt="<?php echo htmlspecialchars($loc['title']); ?>">
                                <?php else: ?>
                                    <div class="location-image d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-white" style="font-size: 3rem; opacity: 0.7;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <span class="visited-badge <?php echo !$loc['visited'] ? 'not-visited-badge' : ''; ?>">
                                    <i class="fas fa-<?php echo $loc['visited'] ? 'check' : 'times'; ?> me-1"></i>
                                    <?php echo $loc['visited'] ? 'Ziyaret Edildi' : 'Ziyaret Edilmedi'; ?>
                                </span>
                            </div>
                            
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($loc['title']); ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($loc['location_name']); ?>
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-globe me-1"></i>
                                    <?php echo htmlspecialchars($loc['city'] . ', ' . $loc['country']); ?>
                                </p>
                                <p class="text-muted mb-3">
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo htmlspecialchars($loc['genre']); ?> • <?php echo $loc['year']; ?>
                                </p>
                                
                                <?php if($loc['rating']): ?>
                                    <div class="mb-3">
                                        <span class="text-warning">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?php echo $i <= $loc['rating'] ? '' : '-o'; ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                        <small class="text-muted ms-2"><?php echo $loc['rating']; ?>/5</small>
                                    </div>
                                <?php endif; ?>
                                
                                <p class="card-text text-muted small">
                                    <?php echo htmlspecialchars(substr($loc['description'], 0, 100)); ?>
                                    <?php echo strlen($loc['description']) > 100 ? '...' : ''; ?>
                                </p>
                                
                                <div class="d-flex gap-2 mt-3">
                                    <a href="view_location.php?id=<?php echo $loc['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="fas fa-eye me-1"></i>Görüntüle
                                    </a>
                                    <a href="edit_location.php?id=<?php echo $loc['id']; ?>" 
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_location.php?id=<?php echo $loc['id']; ?>" 
                                       class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('Bu lokasyonu silmek istediğinizden emin misiniz?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>