<?php
class Comment {
    private $conn;
    private $table_name = "comments";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($post_id, $user_id, $content) {
        $query = "INSERT INTO " . $this->table_name . " (post_id, user_id, content) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$post_id, $user_id, $content]);
    }

    public function createWithMedia($post_id, $user_id, $content) {
        $query = "INSERT INTO " . $this->table_name . " (post_id, user_id, content) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$post_id, $user_id, $content]);
    }

    public function update($id, $content) {
        // When updating, set status back to pending for admin review
        $query = "UPDATE " . $this->table_name . " 
                  SET content = ?, status = 'pending', updated_at = NOW() 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$content, $id]);
    }

    public function getPostComments($post_id) {
        $query = "SELECT c.*, u.username, u.avatar 
                  FROM " . $this->table_name . " c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.post_id = ? AND c.status = 'approved' AND u.status != 'banned'
                  ORDER BY c.created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$post_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $id]);
    }

    public function getCommentById($id) {
    $query = "SELECT * FROM comments WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    public function getPendingComments() {
        $query = "SELECT c.*, u.username, p.title as post_title 
                  FROM " . $this->table_name . " c 
                  JOIN users u ON c.user_id = u.id 
                  JOIN posts p ON c.post_id = p.id 
                  WHERE c.status = 'pending' 
                  ORDER BY c.created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function canUserEdit($comment_id, $user_id) {
        $query = "SELECT user_id FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$comment_id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $comment && $comment['user_id'] == $user_id;
    }
}
?>