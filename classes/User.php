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
                     u.created_at, u.avatar,
                     COALESCE(u.gender, 'prefer_not_to_say') AS gender,
                     COUNT(DISTINCT p.id) AS total_posts,
                     COUNT(DISTINCT c.id) AS total_comments
              FROM users u
              LEFT JOIN posts p ON u.id = p.user_id AND p.status = 'published'
              LEFT JOIN comments c ON u.id = c.user_id AND c.status = 'approved'
              WHERE u.id = ?
              GROUP BY u.id";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Convert gender display
        if ($result['gender'] === 'prefer_not_to_say') {
            $result['gender_display'] = 'Unknown';
        } else {
            $result['gender_display'] = ucfirst($result['gender']);
        }
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
        // Get current user data for notification
        $current_user = $this->getUserById($id);
        
        $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([$status, $id]);
        
        if ($result) {
            // Create notification
            require_once 'Notification.php';
            $notification = new Notification($this->conn);
            
            $title = '';
            $message = '';
            $type = '';
            
            switch($status) {
                case 'limited':
                    $title = 'Account Limited';
                    $message = 'Your account has been limited. Some features may be unavailable.';
                    $type = 'account_limited';
                    break;
                case 'banned':
                    $title = 'Account Banned';
                    $message = 'Your account has been banned due to violations of our community guidelines.';
                    $type = 'account_banned';
                    break;
                case 'active':
                    if ($current_user['status'] !== 'active') {
                        $title = 'Account Restored';
                        $message = 'Your account has been restored. You now have full access to all features.';
                        $type = 'account_restored';
                    }
                    break;
            }
            
            if ($title && $message && $type) {
                $notification->create($id, $type, $title, $message);
            }
        }
        
        return $result;
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