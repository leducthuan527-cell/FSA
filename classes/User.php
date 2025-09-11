<?php
class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($username, $email, $password) {
        $query = "INSERT INTO " . $this->table_name . " (username, email, password) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        if($stmt->execute([$username, $email, $password_hash])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function login($email, $password) {
        $query = "SELECT id, username, email, password, role, status FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        
        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
public function getUserById($id) {
    $query = "SELECT u.id, u.username, u.email, u.role, u.status, 
                     u.created_at,
                     COALESCE(u.gender, 'unknown') AS gender,
                     COALESCE(us.total_posts, 0) AS total_posts,
                     COALESCE(us.total_comments, 0) AS total_comments,
                     u.avatar
              FROM users u
              LEFT JOIN user_stats us ON u.id = us.id
              WHERE u.id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && !isset($result['gender'])) {
        $result['gender'] = 'unknown'; 
    }

    return $result;
}

    public function updateProfile($id, $data) {
        $fields = [];
        $values = [];
        
        foreach($data as $key => $value) {
            if($key !== 'id') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $id;
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($values);
    }

    public function limitUser($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $id]);
    }

  public function getAllUsers() {
    $query = "
        SELECT 
            u.id, 
            u.username, 
            u.email, 
            u.role, 
            u.status, 
            u.avatar, 
            u.created_at, 
            u.updated_at,
            COUNT(DISTINCT p.id) AS total_posts,
            COUNT(DISTINCT c.id) AS total_comments
        FROM users u
        LEFT JOIN posts p ON u.id = p.user_id
        LEFT JOIN comments c ON u.id = c.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
?>