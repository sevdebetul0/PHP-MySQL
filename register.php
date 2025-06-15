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
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasyon
    if(empty($username) || empty($email) || empty($full_name) || empty($password) || empty($confirm_password)) {
        $error_message = "Tüm alanları doldurunuz.";
    } elseif(strlen($username) < 3) {
        $error_message = "Kullanıcı adı en az 3 karakter olmalıdır.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Geçerli bir e-posta adresi giriniz.";
    } elseif(strlen($password) < 6) {
        $error_message = "Şifre en az 6 karakter olmalıdır.";
    } elseif($password !== $confirm_password) {
        $error_message = "Şifreler eşleşmiyor.";
    } else {
        try {
            $user = new User();
            
            // Kullanıcı adı veya e-posta kontrolü
            if($user->userExists($username, $email)) {
                $error_message = "Bu kullanıcı adı veya e-posta zaten kullanılıyor.";
            } else {
                // Yeni kullanıcı oluştur
                $user->username = $username;
                $user->email = $email;
                $user->full_name = $full_name;
                $user->password_hash = password_hash($password, PASSWORD_DEFAULT); // Doğru hash'leme
                
                $result = $user->register();
                
                if($result === true) {
                    header("Location: login.php?registered=success");
                    exit();
                } elseif($result === "duplicate") {
                    $error_message = "Bu kullanıcı adı veya e-posta zaten kullanılıyor.";
                } else {
                    $error_message = "Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.";
                }
            }
        } catch(Exception $e) {
            error_log("Kayıt hatası: " . $e->getMessage());
            $error_message = "Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Dizi/Film Lokasyonları</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        .register-container {
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
        .password-strength {
            font-size: 0.875rem;
            margin-top: 5px;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #198754; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="register-container p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-film brand-logo mb-3"></i>
                        <h2 class="fw-bold mb-2">Hesap Oluştur</h2>
                        <p class="welcome-text">Dizi ve Film Lokasyonlarınızı Takip Edin</p>
                    </div>

                    <?php if(!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="registerForm" novalidate>
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-semibold">
                                <i class="fas fa-user me-2"></i>Ad Soyad
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">
                                <i class="fas fa-at me-2"></i>Kullanıcı Adı
                            </label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                   minlength="3" required>
                            <div class="form-text">En az 3 karakter olmalıdır.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope me-2"></i>E-posta
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Şifre
                            </label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   minlength="6" required>
                            <div class="password-strength" id="passwordStrength"></div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Şifre Tekrar
                            </label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   minlength="6" required>
                            <div class="form-text" id="passwordMatch"></div>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-user-plus me-2"></i>Kayıt Ol
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <p class="mb-0">Zaten hesabınız var mı? 
                            <a href="login.php" class="text-decoration-none fw-semibold">
                                Giriş Yap
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form doğrulama
        const form = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        
        // Şifre gücü kontrolü
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            if (password.length < 6) {
                strengthDiv.innerHTML = '<i class="fas fa-times-circle strength-weak"></i> Çok zayıf (en az 6 karakter)';
                strengthDiv.className = 'password-strength strength-weak';
            } else if (password.length < 8) {
                strengthDiv.innerHTML = '<i class="fas fa-minus-circle strength-medium"></i> Orta (daha uzun olabilir)';
                strengthDiv.className = 'password-strength strength-medium';
            } else {
                strengthDiv.innerHTML = '<i class="fas fa-check-circle strength-strong"></i> Güçlü';
                strengthDiv.className = 'password-strength strength-strong';
            }
            
            // Şifre eşleşme kontrolünü tekrar çalıştır
            checkPasswordMatch();
        });

        // Şifre eşleşme kontrolü
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.innerHTML = '<i class="fas fa-check-circle text-success"></i> Şifreler eşleşiyor';
                matchDiv.className = 'form-text text-success';
            } else {
                matchDiv.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Şifreler eşleşmiyor';
                matchDiv.className = 'form-text text-danger';
            }
        }
        
        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
        
        // Form gönderimi öncesi doğrulama
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const email = document.getElementById('email').value;
            const username = document.getElementById('username').value;
            const fullName = document.getElementById('full_name').value;
            
            // Temel doğrulamalar
            if (!fullName.trim() || !username.trim() || !email.trim() || !password || !confirmPassword) {
                e.preventDefault();
                alert('Lütfen tüm alanları doldurun.');
                return;
            }
            
            if (username.length < 3) {
                e.preventDefault();
                alert('Kullanıcı adı en az 3 karakter olmalıdır.');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Şifre en az 6 karakter olmalıdır.');
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Şifreler eşleşmiyor.');
                return;
            }
            
            // E-posta doğrulama
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Geçerli bir e-posta adresi girin.');
                return;
            }
            
            // Form gönderiliyor göstergesi
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Kayıt Oluşturuluyor...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>