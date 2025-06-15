<?php
require_once 'SessionManager.php';
require_once 'User.php';

SessionManager::start();

// Zaten giriş yapmış kullanıcıları ana sayfaya yönlendir
if(SessionManager::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error_message = "";
$success_message = "";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if(empty($username) || empty($password)) {
        $error_message = "Kullanıcı adı ve şifre gereklidir.";
    } else {
        $user = new User();
        if($user->login($username, $password)) {
            SessionManager::login($user->id, $user->username, $user->full_name);
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Geçersiz kullanıcı adı veya şifre.";
        }
    }
}

// Kayıt başarılı mesajı kontrolü
if(isset($_GET['registered']) && $_GET['registered'] == 'success') {
    $success_message = "Kayıt başarılı! Şimdi giriş yapabilirsiniz.";
}

// Çıkış başarılı mesajı kontrolü
if(isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $success_message = "Başarıyla çıkış yaptınız. Tekrar görüşmek üzere!";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Dizi/Film Lokasyonları</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        .brand-logo {
            font-size: 2.5rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .form-control {
            border-radius: 15px;
            padding: 12px 20px;
            border: 2px solid #e3e6f0;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 15px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .welcome-text {
            color: #64748b;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-container p-5">
                    <div class="text-center mb-5">
                        <i class="fas fa-film brand-logo mb-3"></i>
                        <h2 class="fw-bold mb-2">Hoş Geldiniz!</h2>
                        <p class="welcome-text">Dizi ve Film Lokasyonları Takip Sistemi</p>
                    </div>

                    <?php if(!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-4">
                            <label for="username" class="form-label fw-semibold">
                                <i class="fas fa-user me-2"></i>Kullanıcı Adı veya E-posta
                            </label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                   required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Şifre
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">Hesabınız yok mu? 
                            <a href="register.php" class="text-decoration-none fw-semibold">
                                Kayıt Ol
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>