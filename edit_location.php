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

// Lokasyon bulunamadı veya kullanıcıya ait değil
if(!$location_data || $location_data['user_id'] != SessionManager::getUserId()) {
    header("Location: index.php?error=not_found");
    exit();
}

// Form gönderildiğinde
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Form verilerini al
    $title = trim($_POST['title'] ?? '');
    $location_name = trim($_POST['location_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $scene_description = trim($_POST['scene_description'] ?? '');
    $genre = $_POST['genre'] ?? '';
    $year = $_POST['year'] ?? '';
    $visited = isset($_POST['visited']) ? 1 : 0;
    $rating = $_POST['rating'] ?? null;
    $image_url = trim($_POST['image_url'] ?? '');
    
    // Validasyon
    $errors = [];
    
    if(empty($title)) {
        $errors[] = "Dizi/Film adı gereklidir.";
    }
    
    if(empty($location_name)) {
        $errors[] = "Lokasyon adı gereklidir.";
    }
    
    if(empty($city)) {
        $errors[] = "Şehir gereklidir.";
    }
    
    if(empty($country)) {
        $errors[] = "Ülke gereklidir.";
    }
    
    if(empty($genre)) {
        $errors[] = "Tür seçimi gereklidir.";
    }
    
    if(!empty($year) && (!is_numeric($year) || $year < 1900 || $year > date('Y') + 5)) {
        $errors[] = "Geçerli bir yıl giriniz.";
    }
    
    if(!empty($rating) && (!is_numeric($rating) || $rating < 1 || $rating > 5)) {
        $errors[] = "Rating 1-5 arasında olmalıdır.";
    }
    
    if(!empty($image_url) && !filter_var($image_url, FILTER_VALIDATE_URL)) {
        $errors[] = "Geçerli bir resim URL'si giriniz.";
    }
    
    // Hata yoksa güncelle
    if(empty($errors)) {
        $location->id = $location_id;
        $location->user_id = SessionManager::getUserId();
        $location->title = $title;
        $location->location_name = $location_name;
        $location->address = $address;
        $location->city = $city;
        $location->country = $country;
        $location->description = $description;
        $location->scene_description = $scene_description;
        $location->genre = $genre;
        $location->year = $year;
        $location->visited = $visited;
        $location->rating = $rating;
        $location->image_url = $image_url;
        
        if($location->update()) {
            header("Location: view_location.php?id=" . $location_id . "&success=updated");
            exit();
        } else {
            $error_message = "Lokasyon güncellenirken bir hata oluştu.";
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Türleri getir
$genres = $location->getGenres();

// Mevcut yıl
$current_year = date('Y');
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lokasyon Düzenle - <?php echo htmlspecialchars($location_data['title']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: var(--primary-gradient) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .form-header {
            background: var(--primary-gradient);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .form-header h2 {
            margin: 0;
            font-weight: 700;
        }
        
        .form-body {
            padding: 40px;
        }
        
        .form-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #667eea;
        }
        
        .form-section h5 {
            color: #667eea;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e3e6f0;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .rating-stars {
            display: flex;
            gap: 5px;
            margin-top: 5px;
        }
        
        .rating-stars input[type="radio"] {
            display: none;
        }
        
        .rating-stars label {
            font-size: 1.5rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .rating-stars label:hover,
        .rating-stars label:hover ~ label,
        .rating-stars input[type="radio"]:checked ~ label {
            color: #ffc107;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
            margin-top: 10px;
            display: none;
        }
        
        .visited-toggle {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            border: 2px solid #e3e6f0;
            transition: all 0.3s ease;
        }
        
        .visited-toggle.active {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }
        
        .form-floating label {
            color: #6c757d;
        }
        
        .character-count {
            font-size: 0.8rem;
            color: #6c757d;
            float: right;
            margin-top: 5px;
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

    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-container">
                    <div class="form-header">
                        <h2><i class="fas fa-edit me-2"></i>Lokasyon Düzenle</h2>
                        <p class="mb-0 mt-2"><?php echo htmlspecialchars($location_data['title']); ?> - <?php echo htmlspecialchars($location_data['location_name']); ?></p>
                    </div>
                    
                    <div class="form-body">
                        <!-- Hata Mesajları -->
                        <?php if(!empty($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $location_id); ?>" id="locationForm">
                            <!-- Dizi/Film Bilgileri -->
                            <div class="form-section">
                                <h5><i class="fas fa-tv me-2"></i>Dizi/Film Bilgileri</h5>
                                
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : htmlspecialchars($location_data['title']); ?>" 
                                                   placeholder="Dizi/Film Adı" required>
                                            <label for="title"><i class="fas fa-film me-2"></i>Dizi/Film Adı *</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" id="year" name="year" 
                                                   value="<?php echo isset($_POST['year']) ? htmlspecialchars($_POST['year']) : htmlspecialchars($location_data['year']); ?>" 
                                                   min="1900" max="<?php echo $current_year + 5; ?>" placeholder="Yıl">
                                            <label for="year"><i class="fas fa-calendar me-2"></i>Yıl</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="genre" class="form-label fw-semibold">
                                        <i class="fas fa-tag me-2"></i>Tür *
                                    </label>
                                    <select class="form-select" id="genre" name="genre" required>
                                        <option value="">Tür Seçin</option>
                                        <?php foreach($genres as $genre): ?>
                                            <option value="<?php echo $genre; ?>" 
                                                    <?php 
                                                    $selected_genre = isset($_POST['genre']) ? $_POST['genre'] : $location_data['genre'];
                                                    echo ($selected_genre == $genre) ? 'selected' : ''; 
                                                    ?>>
                                                <?php echo $genre; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Lokasyon Bilgileri -->
                            <div class="form-section">
                                <h5><i class="fas fa-map-marker-alt me-2"></i>Lokasyon Bilgileri</h5>
                                
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="location_name" name="location_name" 
                                               value="<?php echo isset($_POST['location_name']) ? htmlspecialchars($_POST['location_name']) : htmlspecialchars($location_data['location_name']); ?>" 
                                               placeholder="Lokasyon Adı" required>
                                        <label for="location_name"><i class="fas fa-building me-2"></i>Lokasyon Adı *</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="address" name="address" 
                                                  style="height: 80px" placeholder="Adres"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : htmlspecialchars($location_data['address']); ?></textarea>
                                        <label for="address"><i class="fas fa-map me-2"></i>Adres</label>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="city" name="city" 
                                                   value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : htmlspecialchars($location_data['city']); ?>" 
                                                   placeholder="Şehir" required>
                                            <label for="city"><i class="fas fa-city me-2"></i>Şehir *</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="country" name="country" 
                                                   value="<?php echo isset($_POST['country']) ? htmlspecialchars($_POST['country']) : htmlspecialchars($location_data['country']); ?>" 
                                                   placeholder="Ülke" required>
                                            <label for="country"><i class="fas fa-globe me-2"></i>Ülke *</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Açıklamalar -->
                            <div class="form-section">
                                <h5><i class="fas fa-file-text me-2"></i>Açıklamalar</h5>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold">
                                        <i class="fas fa-info-circle me-2"></i>Genel Açıklama
                                    </label>
                                    <textarea class="form-control" id="description" name="description" rows="4" 
                                              placeholder="Bu lokasyon hakkında genel bilgiler..."
                                              maxlength="500" onkeyup="updateCharCount('description', 500)"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : htmlspecialchars($location_data['description']); ?></textarea>
                                    <div class="character-count" id="description-count">0/500</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="scene_description" class="form-label fw-semibold">
                                        <i class="fas fa-video me-2"></i>Sahne Açıklaması
                                    </label>
                                    <textarea class="form-control" id="scene_description" name="scene_description" rows="3" 
                                              placeholder="Bu lokasyonda hangi sahneler çekildi..."
                                              maxlength="300" onkeyup="updateCharCount('scene_description', 300)"><?php echo isset($_POST['scene_description']) ? htmlspecialchars($_POST['scene_description']) : htmlspecialchars($location_data['scene_description']); ?></textarea>
                                    <div class="character-count" id="scene_description-count">0/300</div>
                                </div>
                            </div>

                            <!-- Kişisel Değerlendirme -->
                            <div class="form-section">
                                <h5><i class="fas fa-heart me-2"></i>Kişisel Değerlendirme</h5>
                                
                                <!-- Ziyaret Durumu -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold mb-3">
                                        <i class="fas fa-check-circle me-2"></i>Ziyaret Durumu
                                    </label>
                                    <div class="visited-toggle" id="visitedToggle">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="visited" name="visited" 
                                                   <?php 
                                                   $is_visited = isset($_POST['visited']) ? isset($_POST['visited']) : $location_data['visited'];
                                                   echo $is_visited ? 'checked' : ''; 
                                                   ?>
                                                   onchange="toggleVisited()">
                                            <label class="form-check-label fw-semibold" for="visited">
                                                <span id="visitedText">Bu lokasyonu ziyaret ettim</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Rating -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-star me-2"></i>Değerlendirme
                                    </label>
                                    <div class="rating-stars">
                                        <?php 
                                        $current_rating = isset($_POST['rating']) ? $_POST['rating'] : $location_data['rating'];
                                        for($i = 5; $i >= 1; $i--): 
                                        ?>
                                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>"
                                                   <?php echo ($current_rating == $i) ? 'checked' : ''; ?>>
                                            <label for="star<?php echo $i; ?>">★</label>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted">İsteğe bağlı - Bu lokasyonu nasıl buldunuz?</small>
                                </div>
                            </div>

                            <!-- Resim -->
                            <div class="form-section">
                                <h5><i class="fas fa-image me-2"></i>Resim</h5>
                                
                                <div class="mb-3">
                                    <div class="form-floating">
                                        <input type="url" class="form-control" id="image_url" name="image_url" 
                                               value="<?php echo isset($_POST['image_url']) ? htmlspecialchars($_POST['image_url']) : htmlspecialchars($location_data['image_url']); ?>" 
                                               placeholder="Resim URL'si" onchange="previewImage()">
                                        <label for="image_url"><i class="fas fa-link me-2"></i>Resim URL'si</label>
                                    </div>
                                    <small class="text-muted">İsteğe bağlı - Lokasyon fotoğrafının web adresini giriniz</small>
                                    <img id="imagePreview" class="image-preview" alt="Resim Önizleme">
                                </div>
                            </div>

                            <!-- Butonlar -->
                            <div class="d-flex gap-3 mt-4">
                                <button type="submit" class="btn btn-gradient flex-fill">
                                    <i class="fas fa-save me-2"></i>Değişiklikleri Kaydet
                                </button>
                                <a href="view_location.php?id=<?php echo $location_id; ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Karakter sayacı
        function updateCharCount(fieldId, maxLength) {
            const field = document.getElementById(fieldId);
            const counter = document.getElementById(fieldId + '-count');
            const currentLength = field.value.length;
            counter.textContent = currentLength + '/' + maxLength;
            
            if(currentLength > maxLength * 0.9) {
                counter.style.color = '#dc3545';
            } else if(currentLength > maxLength * 0.7) {
                counter.style.color = '#ffc107';
            } else {
                counter.style.color = '#6c757d';
            }
        }
        
        // Ziyaret durumu toggle
        function toggleVisited() {
            const checkbox = document.getElementById('visited');
            const text = document.getElementById('visitedText');
            const toggle = document.getElementById('visitedToggle');
            
            if(checkbox.checked) {
                text.textContent = 'Bu lokasyonu ziyaret ettim ✅';
                toggle.classList.add('active');
            } else {
                text.textContent = 'Henüz ziyaret etmedim ⏳';
                toggle.classList.remove('active');
            }
        }
        
        // Resim önizleme
        function previewImage() {
            const url = document.getElementById('image_url').value;
            const preview = document.getElementById('imagePreview');
            
            if(url && isValidUrl(url)) {
                preview.src = url;
                preview.style.display = 'block';
                preview.onerror = function() {
                    this.style.display = 'none';
                };
            } else {
                preview.style.display = 'none';
            }
        }
        
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
        
        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            updateCharCount('description', 500);
            updateCharCount('scene_description', 300);
            toggleVisited();
            previewImage();
        });
        
        // Rating yıldızları hover efekti
        document.querySelectorAll('.rating-stars label').forEach(function(star, index) {
            star.addEventListener('mouseenter', function() {
                const stars = document.querySelectorAll('.rating-stars label');
                stars.forEach(function(s, i) {
                    if(i >= stars.length - index - 1) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });
        
        document.querySelector('.rating-stars').addEventListener('mouseleave', function() {
            const checkedStar = document.querySelector('.rating-stars input[type="radio"]:checked');
            const stars = document.querySelectorAll('.rating-stars label');
            
            stars.forEach(function(star, index) {
                if(checkedStar && index >= stars.length - checkedStar.value) {
                    star.style.color = '#ffc107';
                } else {
                    star.style.color = '#ddd';
                }
            });
        });
    </script>
</body>
</html>