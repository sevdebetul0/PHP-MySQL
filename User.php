<?php
require_once 'Database.php';

class User {
    private $conn;
    private $table_name = "users";
    
    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $full_name;
    public $created_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Kullanıcı kaydı
   public function register() {
    $query = "INSERT INTO " . $this->table_name . " 
              SET username=:username, email=:email, password_hash=:password_hash, full_name=:full_name";
    
    $stmt = $this->conn->prepare($query);
    
    // Verileri temizle
    $this->username = SecurityHelper::sanitizeInput($this->username);
    $this->email = SecurityHelper::sanitizeInput($this->email);
    $this->full_name = SecurityHelper::sanitizeInput($this->full_name);
    
    // ÖNEMLİ: Bu satırı kaldırın çünkü şifre zaten register.php'de hash'leniyor
    // $this->password_hash = SecurityHelper::hashPassword($this->password_hash); // ← BU SATIRI SİLİN!
    
    // Parametreleri bağla
    $stmt->bindParam(":username", $this->username);
    $stmt->bindParam(":email", $this->email);
    $stmt->bindParam(":password_hash", $this->password_hash); // Zaten hash'lenmiş gelecek
    $stmt->bindParam(":full_name", $this->full_name);
    
    try {
        if($stmt->execute()) {
            return true;
        }
        return false;
    } catch(PDOException $e) {
        // Duplicate entry hatası kontrolü
        if($e->getCode() == 23000) {
            return "duplicate";
        }
        return false;
    }
}
    
    // Kullanıcı girişi
    public function login($username, $password) {
        $query = "SELECT id, username, email, password_hash, full_name 
                  FROM " . $this->table_name . " 
                  WHERE username = :username OR email = :username";
        
        $stmt = $this->conn->prepare($query);
        $username = SecurityHelper::sanitizeInput($username);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(SecurityHelper::verifyPassword($password, $row['password_hash'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->full_name = $row['full_name'];
                return true;
            }
        }
        return false;
    }
    
    // Kullanıcının var olup olmadığını kontrol et
    public function userExists($username, $email) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE username = :username OR email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    // Kullanıcı bilgilerini getir
    public function getUserById($id) {
        $query = "SELECT id, username, email, full_name, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
    
    // Profil güncelleme
    public function updateProfile($id, $full_name, $email) {
        $query = "UPDATE " . $this->table_name . " 
                  SET full_name = :full_name, email = :email, updated_at = CURRENT_TIMESTAMP 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $full_name = SecurityHelper::sanitizeInput($full_name);
        $email = SecurityHelper::sanitizeInput($email);
        
        $stmt->bindParam(":full_name", $full_name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":id", $id);
        
        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            if($e->getCode() == 23000) {
                return "duplicate";
            }
            return false;
        }
    }
    
    // Şifre değiştirme
    public function changePassword($id, $old_password, $new_password) {
        // Önce eski şifreyi kontrol et
        $query = "SELECT password_hash FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(SecurityHelper::verifyPassword($old_password, $row['password_hash'])) {
                // Yeni şifreyi güncelle
                $update_query = "UPDATE " . $this->table_name . " 
                               SET password_hash = :new_password_hash, updated_at = CURRENT_TIMESTAMP 
                               WHERE id = :id";
                
                $update_stmt = $this->conn->prepare($update_query);
                $new_password_hash = SecurityHelper::hashPassword($new_password);
                $update_stmt->bindParam(":new_password_hash", $new_password_hash);
                $update_stmt->bindParam(":id", $id);
                
                return $update_stmt->execute();
            }
        }
        return false;
    }
}
?>