<?php
class Report {
    private $conn;
    private $table_name = "reports";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($reporter_id, $reported_type, $reported_id, $reason) {
        // Check if user already reported this content
        $check_query = "SELECT id FROM " . $this->table_name . " 
                       WHERE reporter_id = ? AND reported_type = ? AND reported_id = ?";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->execute([$reporter_id, $reported_type, $reported_id]);
        
        if($check_stmt->rowCount() > 0) {
            return false; // Already reported
        }

        $query = "INSERT INTO " . $this->table_name . " (reporter_id, reported_type, reported_id, reason) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        if($stmt->execute([$reporter_id, $reported_type, $reported_id, $reason])) {
            // Hide content immediately for the reporter
            $this->hideContentForUser($reporter_id, $reported_type, $reported_id);
            return true;
        }
        return false;
    }

    private function hideContentForUser($user_id, $type, $content_id) {
        // This would typically be handled with a user_hidden_content table
        // For simplicity, we'll just mark it in session
        if(!isset($_SESSION['hidden_content'])) {
            $_SESSION['hidden_content'] = [];
        }
        $_SESSION['hidden_content'][] = $type . '_' . $content_id;
    }

    public function getPendingReports() {
        $query = "SELECT r.*, u.username as reporter_username,
                  CASE 
                    WHEN r.reported_type = 'post' THEN p.title
                    WHEN r.reported_type = 'comment' THEN c.content
                  END as content_preview
                  FROM " . $this->table_name . " r
                  JOIN users u ON r.reporter_id = u.id
                  LEFT JOIN posts p ON r.reported_type = 'post' AND r.reported_id = p.id
                  LEFT JOIN comments c ON r.reported_type = 'comment' AND r.reported_id = c.id
                  WHERE r.status = 'pending'
                  ORDER BY r.created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$status, $id]);
    }

    public function takeAction($report_id, $action) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$report_id]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);

        if($report) {
            if($action === 'hide') {
                if($report['reported_type'] === 'post') {
                    $update_query = "UPDATE posts SET status = 'hidden' WHERE id = ?";
                } else {
                    $update_query = "UPDATE comments SET status = 'hidden' WHERE id = ?";
                }
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->execute([$report['reported_id']]);
            }
            
            $this->updateStatus($report_id, 'reviewed');
            return true;
        }
        return false;
    }
}
?>