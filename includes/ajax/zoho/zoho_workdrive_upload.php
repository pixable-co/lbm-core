<?php
namespace LBMCore;

if (!defined('ABSPATH')) exit;

class ZohoWorkdriveUpload
{
    private $client_id     = '1000.L3VVIRY7H7RJUFQPJBSV1XD8KRV3IY';
    private $client_secret = 'c74ef031a3971bf563cd06a78a5a2a9cdba5981087';
    private $refresh_token = '1000.3495cb8e75490430c45707c0dc3f6f06.74c88dbc1b261d9e0c75e453fcb74264';
    private $folder_id     = 'jimrt41cb91618ccd4eb98e8fd8bf80b98047';

    public static function init()
    {
        $self = new self();
        add_action('wp_ajax_lbm/zoho_check_connection', [$self, 'check_connection']);
        add_action('wp_ajax_nopriv_lbm/zoho_check_connection', [$self, 'check_connection']);
        add_action('wp_ajax_lbm/zoho_workdrive_upload', [$self, 'zoho_workdrive_upload']);
        add_action('wp_ajax_nopriv_lbm/zoho_workdrive_upload', [$self, 'zoho_workdrive_upload']);
    }

    public function check_connection()
    {
        check_ajax_referer('lbm_nonce');
        $access_token = $this->get_access_token();
        if (!$access_token) {
            wp_send_json_error(['message' => 'Zoho not connected']);
        }
        wp_send_json_success(['message' => 'Zoho is connected']);
    }

    public function zoho_workdrive_upload()
    {
        check_ajax_referer('lbm_nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'You must be logged in']);
        }

        if (!isset($_FILES['file'])) {
            wp_send_json_error(['message' => 'No file uploaded']);
        }

        $access_token = $this->get_access_token();
        if (!$access_token) {
            wp_send_json_error(['message' => 'Failed to retrieve Zoho access token']);
        }

        $file = $_FILES['file'];
        $response = $this->upload_to_workdrive(
            $file['tmp_name'],
            $file['name'],
            $file['type'],
            $access_token
        );

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message(), 'debug' => $response->get_error_data()]);
        }

        if (isset($response['errors'])) {
            wp_send_json_error(['message' => 'Zoho upload error.', 'response' => $response]);
        }

        wp_send_json_success([
            'message' => 'File uploaded successfully',
            'response' => $response,
        ]);
    }

    private function get_access_token()
    {
        $user_id = get_current_user_id();
        if (!$user_id) return null;

        $cached_token = get_user_meta($user_id, '_zoho_access_token', true);
        $token_expiry = (int) get_user_meta($user_id, '_zoho_access_token_expires', true);

        if ($cached_token && time() < $token_expiry) {
            return $cached_token;
        }

        $response = wp_remote_post('https://accounts.zoho.eu/oauth/v2/token', [
            'body' => [
                'refresh_token' => $this->refresh_token,
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type'    => 'refresh_token',
            ],
        ]);

        if (is_wp_error($response)) return null;

        $body = json_decode(wp_remote_retrieve_body($response), true);
        error_log('🔐 Access token (per-user): ' . print_r($body, true));

        if (!empty($body['access_token'])) {
            update_user_meta($user_id, '_zoho_access_token', $body['access_token']);
            update_user_meta($user_id, '_zoho_access_token_expires', time() + (55 * MINUTE_IN_SECONDS));
            return $body['access_token'];
        }

        return null;
    }

    private function upload_to_workdrive($file_path, $file_name, $mime_type, $access_token)
    {
        $url = 'https://workdrive.zoho.eu/api/v1/upload';

        $curl = curl_init();
        $file = new \CURLFile($file_path, $mime_type, $file_name);

        $fields = [
            'content' => $file,
            'parent_id' => $this->folder_id
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Zoho-oauthtoken ' . $access_token,
                'Accept: application/vnd.api+json',
            ],
            CURLOPT_POSTFIELDS => $fields,
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return new \WP_Error('curl_error', $error);
        }

        $body = json_decode($response, true);
        error_log('📁 Upload response: ' . print_r($body, true));

        return $body;
    }
}
