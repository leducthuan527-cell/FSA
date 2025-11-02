<?php
class ContentModerator {
    private $openai_api_key;
    private $supabase_url;
    private $supabase_key;

    public function __construct() {
        $this->openai_api_key = getenv('OPENAI_API_KEY');
        $this->supabase_url = getenv('VITE_SUPABASE_URL');
        $this->supabase_key = getenv('VITE_SUPABASE_ANON_KEY');
    }

    public function moderateContent($text, $user_id, $content_type = 'comment', $content_id = null) {
        if (empty($this->openai_api_key)) {
            return [
                'success' => true,
                'is_flagged' => false,
                'message' => 'Moderation service not configured',
                'bypass' => true
            ];
        }

        $moderation_result = $this->callOpenAIModerationAPI($text);

        if (!$moderation_result['success']) {
            return [
                'success' => false,
                'message' => 'Moderation service error. Please try again.',
                'error' => $moderation_result['error']
            ];
        }

        $is_flagged = $moderation_result['data']['results'][0]['flagged'];
        $categories = $moderation_result['data']['results'][0]['categories'];
        $category_scores = $moderation_result['data']['results'][0]['category_scores'];

        $flagged_categories = [];
        if ($is_flagged) {
            foreach ($categories as $category => $flagged) {
                if ($flagged) {
                    $flagged_categories[] = [
                        'category' => $category,
                        'score' => $category_scores[$category]
                    ];
                }
            }
        }

        $action_taken = $is_flagged ? 'rejected' : 'approved';

        $this->logModerationResult(
            $user_id,
            $content_type,
            $content_id,
            $text,
            $is_flagged,
            $flagged_categories,
            $moderation_result['data'],
            $action_taken
        );

        return [
            'success' => true,
            'is_flagged' => $is_flagged,
            'flagged_categories' => $flagged_categories,
            'action_taken' => $action_taken,
            'message' => $is_flagged
                ? 'Your comment may contain inappropriate language and cannot be posted.'
                : 'Content approved'
        ];
    }

    private function callOpenAIModerationAPI($text) {
        $url = 'https://api.openai.com/v1/moderations';

        $data = [
            'input' => $text,
            'model' => 'omni-moderation-latest'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openai_api_key
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            return [
                'success' => false,
                'error' => 'Network error: ' . $curl_error
            ];
        }

        if ($http_code !== 200) {
            return [
                'success' => false,
                'error' => 'API error: HTTP ' . $http_code
            ];
        }

        $decoded = json_decode($response, true);
        if (!$decoded) {
            return [
                'success' => false,
                'error' => 'Failed to parse API response'
            ];
        }

        return [
            'success' => true,
            'data' => $decoded
        ];
    }

    private function logModerationResult($user_id, $content_type, $content_id, $original_text, $is_flagged, $flagged_categories, $moderation_response, $action_taken) {
        $url = $this->supabase_url . '/rest/v1/moderation_logs';

        $log_data = [
            'user_id' => $user_id,
            'content_type' => $content_type,
            'content_id' => $content_id,
            'original_text' => $original_text,
            'is_flagged' => $is_flagged,
            'flagged_categories' => json_encode($flagged_categories),
            'moderation_response' => json_encode($moderation_response),
            'action_taken' => $action_taken
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($log_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $this->supabase_key,
            'Authorization: Bearer ' . $this->supabase_key,
            'Prefer: return=minimal'
        ]);

        curl_exec($ch);
        curl_close($ch);
    }
}
?>
