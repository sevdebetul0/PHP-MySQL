<?php
require_once 'Database.php';

class Location {
    private $conn;
    private $table_name = "locations";
    
    public $id;
    public $user_id;
    public $title;
    public $location_name;
    public $address;
    public $city;
    public $country;
    public $description;
    public $scene_description;
    public $genre;
    public $year;
    public $visited;
    public $rating;
    public $image_url;
    public $created_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Lokasyon ekleme
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, title=:title, location_name=:location_name, 
                      address=:address, city=:city, country=:country, description=:description,
                      scene_description=:scene_description, genre=:genre, year=:year, 
                      visited=:visited, rating=:rating, image_url=:image_url";
        
        $stmt = $this->conn->prepare($query);
        
        // Verileri temizle
        $this->title = SecurityHelper::sanitizeInput($this->title);
        $this->location_name = SecurityHelper::sanitizeInput($this->location_name);
        $this->address = SecurityHelper::sanitizeInput($this->address);
        $this->city = SecurityHelper::sanitizeInput($this->city);
        $this->country = SecurityHelper::sanitizeInput($this->country);
        $this->description = SecurityHelper::sanitizeInput($this->description);
        $this->scene_description = SecurityHelper::sanitizeInput($this->scene_description);
        $this->image_url = SecurityHelper::sanitizeInput($this->image_url);
        
        // Parametreleri bağla
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":location_name", $this->location_name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":scene_description", $this->scene_description);
        $stmt->bindParam(":genre", $this->genre);
        $stmt->bindParam(":year", $this->year);
        $stmt->bindParam(":visited", $this->visited, PDO::PARAM_BOOL);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":image_url", $this->image_url);
        
        return $stmt->execute();
    }
    
    // Tüm lokasyonları getir (filtreleme ile)
    public function getAllLocations($user_id = null, $filters = []) {
        $query = "SELECT l.*, u.username, u.full_name 
                  FROM " . $this->table_name . " l 
                  LEFT JOIN users u ON l.user_id = u.id 
                  WHERE 1=1";
        
        $params = [];
        
        // Sadece kullanıcının kendi lokasyonları
        if($user_id) {
            $query .= " AND l.user_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        
        // Şehir filtresi
        if(!empty($filters['city'])) {
            $query .= " AND l.city LIKE :city";
            $params[':city'] = '%' . $filters['city'] . '%';
        }
        
        // Ülke filtresi
        if(!empty($filters['country'])) {
            $query .= " AND l.country LIKE :country";
            $params[':country'] = '%' . $filters['country'] . '%';
        }
        
        // Tür filtresi
        if(!empty($filters['genre'])) {
            $query .= " AND l.genre = :genre";
            $params[':genre'] = $filters['genre'];
        }
        
        // Ziyaret durumu filtresi
        if(isset($filters['visited']) && $filters['visited'] !== '') {
            $query .= " AND l.visited = :visited";
            $params[':visited'] = (bool)$filters['visited'];
        }
        
        // Dizi/Film adı filtresi
        if(!empty($filters['title'])) {
            $query .= " AND l.title LIKE :title";
            $params[':title'] = '%' . $filters['title'] . '%';
        }
        
        $query .= " ORDER BY l.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ID ile lokasyon getir
    public function getLocationById($id) {
        $query = "SELECT l.*, u.username, u.full_name 
                  FROM " . $this->table_name . " l 
                  LEFT JOIN users u ON l.user_id = u.id 
                  WHERE l.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
    
    // Lokasyon güncelleme
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, location_name=:location_name, address=:address, 
                      city=:city, country=:country, description=:description,
                      scene_description=:scene_description, genre=:genre, year=:year, 
                      visited=:visited, rating=:rating, image_url=:image_url,
                      updated_at=CURRENT_TIMESTAMP
                  WHERE id=:id AND user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Verileri temizle
        $this->title = SecurityHelper::sanitizeInput($this->title);
        $this->location_name = SecurityHelper::sanitizeInput($this->location_name);
        $this->address = SecurityHelper::sanitizeInput($this->address);
        $this->city = SecurityHelper::sanitizeInput($this->city);
        $this->country = SecurityHelper::sanitizeInput($this->country);
        $this->description = SecurityHelper::sanitizeInput($this->description);
        $this->scene_description = SecurityHelper::sanitizeInput($this->scene_description);
        $this->image_url = SecurityHelper::sanitizeInput($this->image_url);
        
        // Parametreleri bağla
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":location_name", $this->location_name);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":scene_description", $this->scene_description);
        $stmt->bindParam(":genre", $this->genre);
        $stmt->bindParam(":year", $this->year);
        $stmt->bindParam(":visited", $this->visited, PDO::PARAM_BOOL);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);
        
        return $stmt->execute();
    }
    
    // Lokasyon silme
    public function delete($id, $user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $user_id);
        
        return $stmt->execute();
    }
    
    // İstatistikler
    public function getStatistics($user_id) {
        $stats = [];
        
        // Toplam lokasyon sayısı
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Ziyaret edilen lokasyon sayısı
        $query = "SELECT COUNT(*) as visited FROM " . $this->table_name . " WHERE user_id = :user_id AND visited = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $stats['visited'] = $stmt->fetch(PDO::FETCH_ASSOC)['visited'];
        
        // Türlere göre dağılım
        $query = "SELECT genre, COUNT(*) as count FROM " . $this->table_name . " WHERE user_id = :user_id GROUP BY genre";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $stats['genres'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ülkelere göre dağılım
        $query = "SELECT country, COUNT(*) as count FROM " . $this->table_name . " WHERE user_id = :user_id GROUP BY country ORDER BY count DESC LIMIT 5";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $stats['countries'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    // Türleri getir
    public function getGenres() {
        return ['Dram', 'Komedi', 'Aksiyon', 'Romantik', 'Gerilim', 'Korku', 'Bilim Kurgu', 'Fantastik', 'Tarih', 'Belgesel', 'Diğer'];
    }
}
?>