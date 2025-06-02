<?php
if (!defined('ABSPATH')) exit;
require_once AERP_HRM_PATH . 'vendor/autoload.php';

if (isset($_GET['code'])) {
    $client = AERP_Google_Drive_Manager::get_instance()->get_client();
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    update_option('aerp_google_drive_token', $token);
    // Redirect về trang mong muốn sau khi lưu token (tùy chọn)
    if (isset($_GET['page'])) {
        wp_safe_redirect(admin_url('admin.php?page=' . sanitize_text_field($_GET['page'])));
        exit;
    }
}

class AERP_Google_Drive_Manager {
    private static $instance = null;
    private $client;
    private $service;

    private function __construct() {
        $this->init_client();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function init_client() {
        $this->client = new Google_Client();
        $this->client->setAuthConfig(AERP_HRM_PATH . 'credentials.json');
        $this->client->addScope(Google_Service_Drive::DRIVE);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
        // Nạp access token từ database nếu có
        $token = get_option('aerp_google_drive_token');
        if ($token) {
            $this->client->setAccessToken($token);
            if ($this->client->isAccessTokenExpired() && $this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                update_option('aerp_google_drive_token', $this->client->getAccessToken());
            }
        }
        $this->service = new Google_Service_Drive($this->client);
    }

    public function upload_file($file_path, $file_name, $mime_type) {
        try {
            $file_metadata = new Google_Service_Drive_DriveFile([
                'name' => $file_name,
                'mimeType' => $mime_type
            ]);

            $content = file_get_contents($file_path);
            $file = $this->service->files->create($file_metadata, [
                'data' => $content,
                'mimeType' => $mime_type,
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink'
            ]);

            return [
                'file_id' => $file->id,
                'file_url' => $file->webViewLink
            ];
        } catch (Exception $e) {
            error_log('Google Drive upload error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_file($file_id) {
        try {
            $this->service->files->delete($file_id);
            return true;
        } catch (Exception $e) {
            error_log('Google Drive delete error: ' . $e->getMessage());
            return false;
        }
    }

    public function get_file_url($file_id) {
        try {
            $file = $this->service->files->get($file_id, ['fields' => 'webViewLink']);
            return $file->webViewLink;
        } catch (Exception $e) {
            error_log('Google Drive get file error: ' . $e->getMessage());
            return false;
        }
    }

    public function get_client() {
        return $this->client;
    }
} 