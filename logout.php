<?php
require_once 'SessionManager.php';

// Session'ı başlat
SessionManager::start();

// Kullanıcı adını al (çıkış mesajı için)
$username = SessionManager::getUserName();

// Session'ı temizle ve sonlandır
SessionManager::logout();

// Güvenlik için session verilerini tamamen temizle
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Session'ı yok et
session_destroy();

// Çıkış işlemi sonrası login sayfasına yönlendir
header("Location: login.php?logout=success");
exit();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çıkış Yapılıyor - Dizi/Film Lokasyonları</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logout-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            text-align: center;
            max-width: 400px;
        }
        
        .logout-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .spinner-border {
            color: #667eea;
        }
        
        .progress {
            height: 8px;
            border-radius: 10px;
            background-color: rgba(102, 126, 234, 0.2);
        }
        
        .progress-bar {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        
        <h3 class="fw-bold mb-3">Çıkış Yapılıyor...</h3>
        
        <?php if($username): ?>
            <p class="text-muted mb-4">Güle güle <?php echo htmlspecialchars($username); ?>!</p>
        <?php endif; ?>
        
        <div class="spinner-border mb-4" role="status">
            <span class="visually-hidden">Yükleniyor...</span>
        </div>
        
        <div class="progress mb-3">
            <div class="progress-bar progress-bar-animated" role="progressbar" style="width: 100%"></div>
        </div>
        
        <p class="text-muted small">Güvenli çıkış işlemi tamamlanıyor...</p>
        
        <div class="mt-4">
            <a href="login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt me-2"></i>Tekrar Giriş Yap
            </a>
        </div>
    </div>

    <script>
        // 2 saniye sonra login sayfasına yönlendir
        setTimeout(function() {
            window.location.href = 'login.php?logout=success';
        }, 2000);
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>