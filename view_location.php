<?php
require_once 'SessionManager.php'; // SessionManager'ı dahil et
require_once 'User.php';
require_once 'Location.php';

SessionManager::start();

// Giriş yapmamış kullanıcıları login sayfasına yönlendir
if(!SessionManager::isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$location = new Location();
$error_message = "";
$success_message = "";

// ID parametresi kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?error=invalid_id");
    exit();
}

$location_id = (int)$_GET['id'];

// Lokasyonu getir
$location_data = $location->getLocationById($location_id);

// Lokasyon bulunamadı
if(!$location_data) {
    header("Location: index.php?error=not_found");
    exit();
}

// Başarı mesajları
if(isset($_GET['success'])) {
    switch($_GET['success']) {
        case 'updated':
            $success_message = "Lokasyon başarıyla güncellendi!";
            break;
        case 'added':
            $success_message = "Lokasyon başarıyla eklendi!";
            break;
    }
}

// Hata mesajları
if(isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'delete_failed':
            $error_message = "Lokasyon silinirken bir hata oluştu!";
            break;
    }
}

// Kullanıcının kendi lokasyonu mu kontrol et
$is_owner = ($location_data['user_id'] == SessionManager::getUserId());

// Tarih formatla
$created_date = date('d.m.Y H:i', strtotime($location_data['created_at']));
$updated_date = $location_data['updated_at'] ? date('d.m.Y H:i', strtotime($location_data['updated_at'])) : null;

// Google Maps linki oluştur
$google_maps_url = "https://www.google.com/maps/search/" . urlencode($location_data['location_name'] . " " . $location_data['city'] . " " . $location_data['country']);

// Sosyal medya paylaşım linkleri
$page_url = "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$share_text = urlencode($location_data['title'] . " dizisinden " . $location_data['location_name'] . " lokasyonu");
$twitter_url = "https://twitter.com/intent/tweet?text=" . $share_text . "&url=" . urlencode($page_url);
$facebook_url = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($page_url);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($location_data['title']); ?> - <?php echo htmlspecialchars($location_data['location_name']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: var(--primary-gradient) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .location-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .location-header {
            background: var(--primary-gradient);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .location-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .location-header h1 {
            margin: 0 0 10px 0;
            font-weight: 700;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .location-header .subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 20px;
        }
        
        .header-badges {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .header-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .header-badge:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .owner-actions {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
        }
        
        .location-body {
            padding: 40px;
        }
        
        .info-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 5px solid #667eea;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .info-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .info-section h4 {
            color: #667eea;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            background: white;
            padding: 18px;
            border-radius: 12px;
            border: 2px solid #e3e6f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .info-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-gradient);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .info-item:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        
        .info-item:hover::before {
            transform: scaleY(1);
        }
        
        .info-item .label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .info-item .value {
            font-size: 1.1rem;
            color: #333;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .rating-display {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stars {
            color: #ffc107;
            font-size: 1.2rem;
        }
        
        .visited-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .visited-yes {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .visited-no {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }
        
        .location-image {
            width: 100%;
            max-height: 450px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .location-image:hover {
            transform: scale(1.02);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }
        
        .description-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 2px solid #e3e6f0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-secondary-gradient {
            background: var(--secondary-gradient);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
        }
        
        .btn-secondary-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(240, 147, 251, 0.4);
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .share-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
        }
        
        .share-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .share-btn:hover {
            transform: translateY(-2px);
            text-decoration: none;
        }
        
        .twitter-btn {
            background: #1da1f2;
            color: white;
        }
        
        .facebook-btn {
            background: #1877f2;
            color: white;
        }
        
        .maps-btn {
            background: #4285f4;
            color: white;
        }
        
        .meta-info {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            padding: 20px;
            border-radius: 12px;
            font-size: 0.9rem;
            color: #6c757d;
            text-align: center;
            margin-top: 20px;
            border: 1px solid #dee2e6;
        }
        
        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            animation: fadeIn 0.3s ease;
        }
        
        .modal-content-img {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90vh;
            margin-top: 5vh;
            border-radius: 10px;
        }
        
        .close-modal {
            position: absolute;
            top: 30px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .close-modal:hover {
            color: #bbb;
            transform: scale(1.1);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .location-header {
                padding: 30px 20px;
            }
            
            .location-header h1 {
                font-size: 2rem;
            }
            
            .location-body {
                padding: 30px 20px;
            }
            
            .owner-actions {
                position: static;
                margin-top: 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .share-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
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
                        <a class="nav-link" href="index.php">
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
        <!-- Başarı Mesajları -->
        <?php if(!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Hata Mesajları -->
        <?php if(!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="location-container">
            <!-- Header -->
            <div class="location-header">
                <?php if($is_owner): ?>
                    <div class="owner-actions">
                        <a href="edit_location.php?id=<?php echo $location_id; ?>" class="btn btn-light btn-sm me-2">
                            <i class="fas fa-edit me-1"></i>Düzenle
                        </a>
                        <button class="btn btn-danger btn-sm" onclick="confirmDelete()">
                            <i class="fas fa-trash me-1"></i>Sil
                        </button>
                    </div>
                <?php endif; ?>
                
                <h1><?php echo htmlspecialchars($location_data['title']); ?></h1>
                <div class="subtitle">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <?php echo htmlspecialchars($location_data['location_name']); ?>
                </div>
                
                <div class="header-badges">
                    <div class="header-badge">
                        <i class="fas fa-tag me-1"></i>
                        <?php echo htmlspecialchars($location_data['genre']); ?>
                    </div>
                    
                    <?php if($location_data['year']): ?>
                        <div class="header-badge">
                            <i class="fas fa-calendar me-1"></i>
                            <?php echo htmlspecialchars($location_data['year']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="header-badge">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($location_data['username']); ?>
                    </div>
                </div>
            </div>
            
            <!-- Body -->
            <div class="location-body">
                <!-- Paylaşım Butonları -->
                <div class="share-buttons">
                    <a href="<?php echo $twitter_url; ?>" target="_blank" class="share-btn twitter-btn">
                        <i class="fab fa-twitter"></i>
                        Twitter'da Paylaş
                    </a>
                    <a href="<?php echo $facebook_url; ?>" target="_blank" class="share-btn facebook-btn">
                        <i class="fab fa-facebook"></i>
                        Facebook'ta Paylaş
                    </a>
                    <a href="<?php echo $google_maps_url; ?>" target="_blank" class="share-btn maps-btn">
                        <i class="fas fa-map-marked-alt"></i>
                        Google Maps'te Aç
                    </a>
                </div>

                <!-- Resim -->
                <?php if($location_data['image_url']): ?>
                    <div class="text-center mb-4">
                        <img src="<?php echo htmlspecialchars($location_data['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($location_data['location_name']); ?>" 
                             class="location-image"
                             onclick="openImageModal(this.src)"
                             onerror="this.parentElement.style.display='none'">
                    </div>
                <?php endif; ?>

                <!-- Lokasyon Bilgileri -->
                <div class="info-section">
                    <h4><i class="fas fa-map-marker-alt"></i>Lokasyon Bilgileri</h4>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="label">Lokasyon Adı</div>
                            <div class="value"><?php echo htmlspecialchars($location_data['location_name']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="label">Şehir</div>
                            <div class="value">
                                <i class="fas fa-city"></i>
                                <?php echo htmlspecialchars($location_data['city']); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="label">Ülke</div>
                            <div class="value">
                                <i class="fas fa-globe"></i>
                                <?php echo htmlspecialchars($location_data['country']); ?>
                            </div>
                        </div>
                        
                        <?php if($location_data['address']): ?>
                            <div class="info-item">
                                <div class="label">Adres</div>
                                <div class="value">
                                    <i class="fas fa-map"></i>
                                    <?php echo htmlspecialchars($location_data['address']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dizi/Film Bilgileri -->
                <div class="info-section">
                    <h4><i class="fas fa-tv"></i>Dizi/Film Bilgileri</h4>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="label">Yapım Adı</div>
                            <div class="value"><?php echo htmlspecialchars($location_data['title']); ?></div>
                        </div>
                        
                        <div class="info-item">
                            <div class="label">Tür</div>
                            <div class="value">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars($location_data['genre']); ?>
                            </div>
                        </div>
                        
                        <?php if($location_data['year']): ?>
                            <div class="info-item">
                                <div class="label">Yıl</div>
                                <div class="value">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo htmlspecialchars($location_data['year']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <div class="label">Ziyaret Durumu</div>
                            <div class="value">
                                <?php if($location_data['visited']): ?>
                                    <span class="visited-badge visited-yes">
                                        <i class="fas fa-check-circle"></i>
                                        Ziyaret Edildi
                                    </span>
                                <?php else: ?>
                                    <span class="visited-badge visited-no">
                                        <i class="fas fa-clock"></i>
                                        Henüz Ziyaret Edilmedi
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if($location_data['rating']): ?>
                        <div class="info-item">
                            <div class="label">Değerlendirme</div>
                            <div class="value">
                                <div class="rating-display">
                                    <div class="stars">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <?php if($i <= $location_data['rating']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="ms-2">(<?php echo $location_data['rating']; ?>/5)</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Açıklamalar -->
                <?php if($location_data['description'] || $location_data['scene_description']): ?>
                    <div class="info-section">
                        <h4><i class="fas fa-file-text"></i>Açıklamalar</h4>
                        
                        <?php if($location_data['description']): ?>
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Genel Açıklama
                                </h6>
                                <div class="description-text">
                                    <?php echo nl2br(htmlspecialchars($location_data['description'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($location_data['scene_description']): ?>
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-video me-2"></i>Sahne Açıklaması
                                </h6>
                                <div class="description-text">
                                    <?php echo nl2br(htmlspecialchars($location_data['scene_description'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Meta Bilgiler -->
                <div class="meta-info">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <i class="fas fa-user me-2"></i>
                            Ekleyen: <strong><?php echo htmlspecialchars($location_data['username']); ?></strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <i class="fas fa-calendar-plus me-2"></i>
                            Ekleme Tarihi: <strong><?php echo $created_date; ?></strong>
                        </div>
                        <?php if($updated_date): ?>
                            <div class="col-md-4 mb-2">
                                <i class="fas fa-calendar-edit me-2"></i>
                                Son Güncelleme: <strong><?php echo $updated_date; ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Aksiyon Butonları -->
                <div class="action-buttons">
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Ana Sayfaya Dön
                    </a>
                    
                    <?php if($is_owner): ?>
                        <a href="edit_location.php?id=<?php echo $location_id; ?>" class="btn btn-gradient">
                            <i class="fas fa-edit me-2"></i>Düzenle
                        </a>
                    <?php endif;