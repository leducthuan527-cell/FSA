<?php
class Post {
    private $conn;
    private $table_name = "posts";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $title, $content) {
    $query = "INSERT INTO posts (user_id, title, content, status) 
              VALUES (:user_id, :title, :content, 'draft')";
    $stmt = $this->conn->prepare($query);
    return $stmt->execute([
        ':user_id' => $user_id,
        ':title' => $title,
        ':content' => $content
    ]);
    }

public function getPublishedPosts($user_id, $limit = 10, $offset = 0) {
    $query = "SELECT p.*, u.username, u.avatar 
              FROM " . $this->table_name . " p
              JOIN users u ON p.user_id = u.id
              LEFT JOIN reports r 
                ON r.reported_type = 'post' 
               AND r.reported_id = p.id 
               AND r.reporter_id = :user_id
              WHERE p.status = 'published' 
                AND u.status != 'banned'
                AND r.id IS NULL
              ORDER BY p.created_at DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function getPostById($id) {
        $query = "SELECT p.*, u.username, u.avatar 
                  FROM " . $this->table_name . " p 
                  JOIN users u ON p.user_id = u.id 
                  WHERE p.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

public function getUserPosts($profile_id, $viewer_id = null) {
    if ($viewer_id !== null && $viewer_id != $profile_id) {
        // Hide posts reported by the viewer
        $query = "SELECT p.*
                  FROM " . $this->table_name . " p
                  LEFT JOIN reports r 
                    ON r.reported_type = 'post'
                   AND r.reported_id = p.id
                   AND r.reporter_id = :viewer_id
                  WHERE p.user_id = :profile_id
                    AND r.id IS NULL
                  ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':viewer_id', $viewer_id, PDO::PARAM_INT);
        $stmt->bindParam(':profile_id', $profile_id, PDO::PARAM_INT);
    } else {
        // Show all posts (own profile or no viewer specified)
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :profile_id 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':profile_id', $profile_id, PDO::PARAM_INT);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $id]);
    }

public function getPendingPosts() {
    $query = "SELECT p.*, u.username 
              FROM posts p 
              JOIN users u ON p.user_id = u.id 
              WHERE p.status = 'draft'
              ORDER BY p.created_at DESC";
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>