<?php
namespace LBMCore;

if (!defined('ABSPATH')) exit;

class ZohoWorkdriveUpload
{
    private $client_id     = '1000.ZDSET1BTZH3EOM50JCC6I58FH1GLSZ';
    private $client_secret = '767bd3f7cae94b13cfb959e2ccb3dfdb1ef79dc1f8';
    private $folder_id     = '6wqe2ac49aee129914b04a6d0b95560e5f71d';
    private $redirect_uri  = 'http://localhost:10028';

    public static function init()
    {
        $self = new self();
        add_action('init', [$self, 'maybe_store_token_on_redirect']);
        add_action('wp_ajax_lbm/zoho_check_connection', [$self, 'check_connection']);
        add_action('wp_ajax_nopriv_lbm/zoho_check_connection', [$self, 'check_connection']);
        add_action('wp_ajax_lbm/zoho_workdrive_upload', [$self, 'zoho_workdrive_upload']);
        add_action('wp_ajax_nopriv_lbm/zoho_workdrive_upload', [$self, 'zoho_workdrive_upload']);
    }

    public function maybe_store_token_on_redirect()
    {
        if (!is_user_logged_in() || !isset($_GET['code'])) return;

        $user_id = get_current_user_id();
        $code = sanitize_text_field($_GET['code']);

        $response = wp_remote_post('https://accounts.zoho.eu/oauth/v2/token', [
            'body' => [
                'grant_type'    => 'authorization_code',
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri'  => $this->redirect_uri,
                'code'          => $code,
            ],
        ]);

        if (is_wp_error($response)) {
            error_log('❌ Token request error: ' . $response->get_error_message());
            return;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        error_log('🔁 Token response: ' . print_r($body, true));

        if (!empty($body['refresh_token'])) {
            update_user_meta($user_id, 'zoho_refresh_token', sanitize_text_field($body['refresh_token']));
            error_log("✅ Refresh token saved for user $user_id");
        } else {
            error_log('⚠️ No refresh token returned');
        }

        wp_safe_redirect(remove_query_arg('code'));
        exit;
    }

    public function check_connection()
    {
        check_ajax_referer('lbm_nonce');
        $user_id = get_current_user_id();

        if (!$user_id || !is_user_logged_in()) {
            wp_send_json_error(['message' => 'User not logged in']);
        }

        $token = get_user_meta($user_id, 'zoho_refresh_token', true);
        if (!$token) {
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

        $user_id = get_current_user_id();
        $refresh_token = get_user_meta($user_id, 'zoho_refresh_token', true);

        if (!$refresh_token) {
            wp_send_json_error(['message' => 'No refresh token found for user']);
        }

        if (!isset($_FILES['file'])) {
            wp_send_json_error(['message' => 'No file uploaded']);
        }

        $access_token = $this->get_access_token($refresh_token);
        if (!$access_token) {
            wp_send_json_error(['message' => 'Failed to retrieve Zoho access token']);
        }

        $file = $_FILES['file'];
        $response = $this->upload_simple_to_workdrive(
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

    private function get_access_token($refresh_token)
    {
        $response = wp_remote_post('https://accounts.zoho.eu/oauth/v2/token', [
            'body' => [
                'refresh_token' => $refresh_token,
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type'    => 'refresh_token',
            ],
        ]);

        if (is_wp_error($response)) return null;

        $body = json_decode(wp_remote_retrieve_body($response), true);
        error_log('🎫 Access token body: ' . print_r($body, true));
        return $body['access_token'] ?? null;
    }

    private function upload_simple_to_workdrive($file_path, $file_name, $mime_type, $access_token)
    {
        $url = 'https://workdrive.zoho.eu/api/v1/upload?parent_id=' . $this->folder_id;

        $curl = curl_init();
        $file = new \CURLFile($file_path, $mime_type, $file_name);

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Zoho-oauthtoken ' . $access_token,
            ],
            CURLOPT_POSTFIELDS => [
                'file' => $file
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return new \WP_Error('curl_error', $error);
        }

        $body = json_decode($response, true);
        error_log('📁 cURL upload response: ' . print_r($body, true));

        return $body;
    }
}
