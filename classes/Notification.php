<?php
class Notification {
    private $conn;
    private $table_name = "notifications";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $type, $title, $message) {
        $query = "INSERT INTO " . $this->table_name . " (user_id, type, title, message) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $type, $title, $message]);
    }
    
    public function createPostSubmittedNotification($user_id, $post_title) {
        return $this->create(
            $user_id,
            'post_submitted',
            'Post Submitted for Review',
            "Your post \"$post_title\" has been submitted for admin review."
        );
    }
    
    public function createCommentSubmittedNotification($user_id, $post_title) {
        return $this->create(
            $user_id,
            'comment_submitted',
            'Comment Submitted for Review',
            "Your comment on \"$post_title\" has been submitted for admin review."
        );
    }
    
    public function createPostDeletedNotification($user_id, $post_title) {
        return $this->create(
            $user_id,
            'post_deleted',
            'Post Removed',
            "Your post \"$post_title\" has been removed by an administrator."
        );
    }
    
    public function createCommentDeletedNotification($user_id, $post_title) {
        return $this->create(
            $user_id,
            'comment_deleted',
            'Comment Removed',
            "Your comment on \"$post_title\" has been removed by an administrator."
        );
    }

    public function getUserNotifications($user_id, $limit = 12, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC 
                  LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->bindParam(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE user_id = ? AND is_read = FALSE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function markAsRead($user_id, $notification_id = null) {
        if ($notification_id) {
            $query = "UPDATE " . $this->table_name . " SET is_read = TRUE WHERE id = ? AND user_id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$notification_id, $user_id]);
        } else {
            $query = "UPDATE " . $this->table_name . " SET is_read = TRUE WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$user_id]);
        }
    }

    public function getTotalCount($user_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
?>