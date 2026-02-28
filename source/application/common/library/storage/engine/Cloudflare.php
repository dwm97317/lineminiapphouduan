<?php

namespace app\common\library\storage\engine;

/**
 * Cloudflare R2 Storage Engine (S3 Compatible)
 * Class Cloudflare
 * @package app\common\library\storage\engine
 */
class Cloudflare extends Server
{
    private $config;
    private $region = 'auto';

    /**
     * Constructor
     * @param $config
     */
    public function __construct($config)
    {
        parent::__construct();
        $this->config = $config;
    }

    /**
     * Execution Upload
     * @return bool
     */
    public function upload()
    {
        try {
            $key = $this->fileName;
            $path = $this->getRealPath();
            
            if (!file_exists($path)) {
                throw new \Exception('File not found: ' . $path);
            }
            
            $content = file_get_contents($path);
            $mimeType = $this->getMimeType($path);
            
            $this->s3Request('PUT', $key, $content, $mimeType);
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Delete File
     * @param $fileName
     * @return bool
     */
    public function delete($fileName)
    {
        try {
            $this->s3Request('DELETE', $fileName);
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Return File Name
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Get Mime Type of a file
     * @param string $path
     * @return string
     */
    private function getMimeType($path)
    {
        // 1. Try fileinfo extension if available
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $path);
            finfo_close($finfo);
            if ($mimeType) {
                return $mimeType;
            }
        }
        
        // 2. Try mime_content_type if available (and not namespaced incorrectly previously)
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($path);
            if ($mimeType) {
                return $mimeType;
            }
        }

        // 3. Fallback to mapping by extension
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp',
            'txt' => 'text/plain',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
        ];

        return isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'application/octet-stream';
    }

    /**
     * Make Authenticated Request to R2 (S3 API)
     * @param string $method
     * @param string $key
     * @param string $content
     * @param string|null $contentType
     * @return mixed
     * @throws \Exception
     */
    private function s3Request($method, $key, $content = '', $contentType = null)
    {
        // Configuration
        $accessKey = $this->config['access_key'];
        $secretKey = $this->config['secret_key'];
        $accountId = $this->config['account_id'];
        $bucket = $this->config['bucket'];

        if (empty($accessKey) || empty($secretKey) || empty($accountId) || empty($bucket)) {
            throw new \Exception('Cloudflare R2 configuration is incomplete');
        }

        // Host and Endpoint
        // R2 URL format: https://<accountid>.r2.cloudflarestorage.com/<bucket>/<key>
        $host = "{$accountId}.r2.cloudflarestorage.com";
        $endpoint = "https://{$host}/{$bucket}/{$key}";
        
        // AWS Signature V4 Requirements
        $service = 's3';
        $region = $this->region;
        $algorithm = 'AWS4-HMAC-SHA256';
        
        // Time
        $now = time();
        $amzDate = gmdate('Ymd\THis\Z', $now);
        $dateStamp = gmdate('Ymd', $now);

        // Prepare Headers
        $headers = [
            'host' => $host,
            'x-amz-date' => $amzDate,
            'x-amz-content-sha256' => hash('sha256', $content)
        ];

        if ($method === 'PUT' && $contentType) {
            $headers['content-type'] = $contentType;
            // Content-Length is implicitly handled by curl but good to have for signature if included
            // But usually Host, Date, Content-Sha256 are enough for canonical headers
        }

        // 1. Canonical Request
        $canonicalUri = "/{$bucket}/{$key}";
        $canonicalQueryString = '';
        
        // Sort headers
        ksort($headers);
        $canonicalHeaders = "";
        $signedHeaders = "";
        
        foreach ($headers as $k => $v) {
            $keyLower = strtolower($k);
            $canonicalHeaders .= $keyLower . ':' . trim($v) . "\n";
            $signedHeaders .= $keyLower . ';';
        }
        $signedHeaders = rtrim($signedHeaders, ';');

        $payloadHash = $headers['x-amz-content-sha256'];
        $canonicalRequest = "$method\n$canonicalUri\n$canonicalQueryString\n$canonicalHeaders\n$signedHeaders\n$payloadHash";

        // 2. String to Sign
        $credentialScope = "$dateStamp/$region/$service/aws4_request";
        $stringToSign = "$algorithm\n$amzDate\n$credentialScope\n" . hash('sha256', $canonicalRequest);

        // 3. Signing Key
        $kSecret = 'AWS4' . $secretKey;
        $kDate = hash_hmac('sha256', $dateStamp, $kSecret, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        // 4. Signature
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        // 5. Authorization Header
        $authorization = "$algorithm Credential=$accessKey/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature";
        
        $headers['Authorization'] = $authorization;

        // Perform Request
        $ch = curl_init($endpoint);
        
        $curlHeaders = [];
        foreach ($headers as $k => $v) {
            // Note: Curl handles Content-Length and Content-Type for body automatically if passed in POSTFIELDS
            // But we must send the exact headers we signed.
            // Except Content-Length might be auto-added by Curl, so we signed what we intended.
            $curlHeaders[] = "$k: $v";
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Consider verifying in prod
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 400) {
            // Try to parse XML error
            $msg = $response;
            if (empty($msg) && !empty($curlError)) {
                $msg = $curlError;
            }
            throw new \Exception("R2 API Error [{$httpCode}]: {$msg}");
        }

        return $response;
    }
}
