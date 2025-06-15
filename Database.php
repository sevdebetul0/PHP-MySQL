<?php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    
     public function __construct() {
        // Geliştirme ortamı ayarları
        $this->host = "localhost";
        $this->db_name = "movie_locations_db";
        $this->username = "root";
        $this->password = "";
      
    }
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                )
            );
        } catch(PDOException $exception) {
            error_log("Veritabanı bağlantı hatası: " . $exception->getMessage());
            throw new Exception("Veritabanı bağlantısı kurulamadı.");
        }
        
        return $this->conn;
    }
    
    public function closeConnection() {
        $this->conn = null;
    }
}

// Güvenlik için yardımcı fonksiyonlar
class SecurityHelper {
    
    /**
     * Kullanıcı girişlerini temizler
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    /**
     * E-posta adresini doğrular
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Güçlü şifre kontrolü yapar
     */
    public static function validatePassword($password) {
        // En az 8 karakter, en az bir büyük harf, bir küçük harf ve bir rakam
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
    }
    
    /**
     * Şifreyi hash'ler
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Şifreyi doğrular
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * CSRF token oluşturur
     */
    public static function generateCSRFToken() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * CSRF token'ı doğrular
     */
    public static function validateCSRFToken($token) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * URL'yi doğrular
     */
    public static function validateURL($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Dosya yükleme güvenlik kontrolü
     */
    public static function validateFileUpload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif']) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $file_info = pathinfo($file['name']);
        $extension = strtolower($file_info['extension']);
        
        if (!in_array($extension, $allowed_types)) {
            return false;
        }
        
        // MIME type kontrolü
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowed_mime_types = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif'
        ];
        
        return in_array($mime_type, $allowed_mime_types);
    }
}
?>