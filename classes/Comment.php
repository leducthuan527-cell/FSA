<?php
class Comment {
    private $conn;
    private $table_name = "comments";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($post_id, $user_id, $content) {
    $query = "INSERT INTO " . $this->table_name . " (post_id, user_id, content, status) 
              VALUES (?, ?, ?, 'pending')";
    $stmt = $this->conn->prepare($query);
    $result = $stmt->execute([$post_id, $user_id, $content]);

    if ($result) {
        // Get post title for notification
        $post_query = "SELECT title FROM posts WHERE id = ?";
        $post_stmt = $this->conn->prepare($post_query);
        $post_stmt->execute([$post_id]);
        $post_data = $post_stmt->fetch(PDO::FETCH_ASSOC);

        if ($post_data) {
            require_once 'Notification.php';
            $notification = new Notification($this->conn);
            $notification->createCommentSubmittedNotification($user_id, $post_data['title']);
        }
    }

    return $result;
    }

    public function createWithMedia($post_id, $user_id, $content) {
    $query = "INSERT INTO " . $this->table_name . " (post_id, user_id, content, status) 
              VALUES (?, ?, ?, 'pending')";
    $stmt = $this->conn->prepare($query);
    $result = $stmt->execute([$post_id, $user_id, $content]);

    if ($result) {
        $post_query = "SELECT title FROM posts WHERE id = ?";
        $post_stmt = $this->conn->prepare($post_query);
        $post_stmt->execute([$post_id]);
        $post_data = $post_stmt->fetch(PDO::FETCH_ASSOC);

        if ($post_data) {
            require_once 'Notification.php';
            $notification = new Notification($this->conn);
            $notification->createCommentSubmittedNotification($user_id, $post_data['title']);
        }
    }

    return $result;
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
    $query = "SELECT c.*, u.username, u.avatar, p.title as post_title 
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
        // Get comment details for notification before deletion
        $comment_query = "SELECT c.user_id, p.title 
                         FROM comments c 
                         JOIN posts p ON c.post_id = p.id 
                         WHERE c.id = ?";
        $comment_stmt = $this->conn->prepare($comment_query);
        $comment_stmt->execute([$id]);
        $comment_data = $comment_stmt->fetch(PDO::FETCH_ASSOC);
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([$id]);
        
        if ($result && $comment_data) {
            // Create notification for comment deletion
            require_once 'Notification.php';
            $notification = new Notification($this->conn);
            $notification->createCommentDeletedNotification($comment_data['user_id'], $comment_data['title']);
        }
        
        return $result;
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