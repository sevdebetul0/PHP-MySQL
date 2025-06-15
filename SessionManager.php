<?php
class SessionManager {
    
    /**
     * Session'ı başlatır
     */
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Kullanıcının giriş yapıp yapmadığını kontrol eder
     */
    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Kullanıcı girişi yapar
     */
    public static function login($user_id, $username, $full_name) {
        self::start();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['login_time'] = time();
        
        // Session güvenliği için session ID'yi yenile
        session_regenerate_id(true);
    }

    /**
     * Kullanıcı çıkışı yapar
     */
    public static function logout() {
        self::start();
        
        // Session verilerini temizle
        $_SESSION = array();
        
        // Session cookie'sini sil
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Session'ı sonlandır
        session_destroy();
    }

    /**
     * Kullanıcı ID'sini döndürür
     */
    public static function getUserId() {
        self::start();
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Kullanıcı adını döndürür
     */
    public static function getUserName() {
        self::start();
        return $_SESSION['username'] ?? '';
    }

    /**
     * Tam adı döndürür
     */
    public static function getFullName() {
        self::start();
        return $_SESSION['full_name'] ?? '';
    }

    /**
     * Giriş zamanını döndürür
     */
    public static function getLoginTime() {
        self::start();
        return $_SESSION['login_time'] ?? null;
    }

    /**
     * Session timeout kontrolü yapar
     */
    public static function checkTimeout($timeout_minutes = 30) {
        self::start();
        
        if (self::isLoggedIn()) {
            $login_time = self::getLoginTime();
            if ($login_time && (time() - $login_time) > ($timeout_minutes * 60)) {
                self::logout();
                return false;
            }
            // Aktivite zamanını güncelle
            $_SESSION['login_time'] = time();
        }
        
        return self::isLoggedIn();
    }

    /**
     * Giriş yapmamış kullanıcıları login sayfasına yönlendirir
     */
    public static function requireLogin($redirect_url = 'login.php') {
        if (!self::isLoggedIn()) {
            header("Location: " . $redirect_url);
            exit();
        }
    }

    /**
     * Admin yetkisi kontrolü
     */
    public static function isAdmin() {
        self::start();
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
    }

    /**
     * Admin yetkisi gerekli sayfalar için kontrol
     */
    public static function requireAdmin($redirect_url = 'index.php') {
        self::requireLogin();
        if (!self::isAdmin()) {
            header("Location: " . $redirect_url);
            exit();
        }
    }

    /**
     * Flash mesaj ekler
     */
    public static function setFlashMessage($type, $message) {
        self::start();
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Flash mesajları alır ve temizler
     */
    public static function getFlashMessages() {
        self::start();
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    /**
     * Session verilerini debug için yazdırır
     */
    public static function debug() {
        self::start();
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
    }
}
?>