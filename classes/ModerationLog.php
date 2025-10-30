<?php
class ModerationLog {
    private $supabase_url;
    private $supabase_key;

    public function __construct() {
        $this->supabase_url = getenv('VITE_SUPABASE_URL');
        $this->supabase_key = getenv('VITE_SUPABASE_ANON_KEY');
    }

    public function getAllLogs($limit = 100, $offset = 0) {
        $url = $this->supabase_url . '/rest/v1/moderation_logs?order=created_at.desc&limit=' . $limit . '&offset=' . $offset;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->supabase_key,
            'Authorization: Bearer ' . $this->supabase_key
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            return json_decode($response, true);
        }

        return [];
    }

    public function getFlaggedLogs($limit = 100, $offset = 0) {
        $url = $this->supabase_url . '/rest/v1/moderation_logs?is_flagged=eq.true&order=created_at.desc&limit=' . $limit . '&offset=' . $offset;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->supabase_key,
            'Authorization: Bearer ' . $this->supabase_key
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            return json_decode($response, true);
        }

        return [];
    }

    public function getLogsByUser($user_id, $limit = 50) {
        $url = $this->supabase_url . '/rest/v1/moderation_logs?user_id=eq.' . intval($user_id) . '&order=created_at.desc&limit=' . $limit;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->supabase_key,
            'Authorization: Bearer ' . $this->supabase_key
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            return json_decode($response, true);
        }

        return [];
    }

    public function getStats() {
        $logs = $this->getAllLogs(1000);

        $total = count($logs);
        $flagged = 0;
        $approved = 0;
        $rejected = 0;

        foreach ($logs as $log) {
            if ($log['is_flagged']) {
                $flagged++;
            }
            if ($log['action_taken'] === 'approved') {
                $approved++;
            }
            if ($log['action_taken'] === 'rejected') {
                $rejected++;
            }
        }

        return [
            'total' => $total,
            'flagged' => $flagged,
            'approved' => $approved,
            'rejected' => $rejected
        ];
    }
}
?>
