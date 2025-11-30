<?php

class CloudinaryService {
    private $cloudName;
    private $apiKey;
    private $apiSecret;

    public function __construct() {
        $config = require dirname(__DIR__, 2) . '/config/cloudinary.php';
        $this->cloudName = $config['cloud_name'];
        $this->apiKey = $config['api_key'];
        $this->apiSecret = $config['api_secret'];
    }

    public function upload($filePath) {
        if (empty($this->cloudName) || empty($this->apiKey) || empty($this->apiSecret) || 
            $this->cloudName === 'YOUR_CLOUD_NAME') {
            throw new Exception("Cloudinary credentials are not configured.");
        }

        $timestamp = time();
        $params = [
            'timestamp' => $timestamp,
            // 'upload_preset' => 'ml_default' // Optional: if using unsigned uploads, but we use signed here
        ];

        // Generate signature
        // Signature is a hex digest of the string: "key=value&key=value...&secret"
        // Parameters must be sorted alphabetically
        ksort($params);
        $stringToSign = "";
        foreach ($params as $key => $value) {
            $stringToSign .= "$key=$value&";
        }
        // Remove trailing & and append secret
        $stringToSign = rtrim($stringToSign, "&");
        $stringToSign .= $this->apiSecret;
        
        $signature = sha1($stringToSign);
        
        $params['api_key'] = $this->apiKey;
        $params['signature'] = $signature;
        $params['file'] = new CURLFile($filePath);

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Disable SSL verification for development if needed, but better to keep it enabled
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Cloudinary upload failed (cURL error): " . $error);
        }
        
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $msg = isset($data['error']['message']) ? $data['error']['message'] : "Unknown error";
            throw new Exception("Cloudinary upload failed: " . $msg);
        }

        return $data['secure_url'];
    }
}
