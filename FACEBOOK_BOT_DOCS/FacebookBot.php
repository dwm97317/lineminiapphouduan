<?php

namespace app\api\controller;

use think\facade\Log;

/**
 * Facebook Messenger Bot Webhook Handler
 * 
 * Handles webhook events from Facebook Messenger platform
 * Routes messages through bot logic
 * 
 * @package app\api\controller
 */
class FacebookBot extends Controller
{
    /**
     * GET /api/facebook/webhook
     * 
     * Facebook webhook verification
     * Called when setting up webhook in Facebook developer console
     */
    public function verify()
    {
        $mode = $this->request->param('hub_mode');
        $token = $this->request->param('hub_verify_token');
        $challenge = $this->request->param('hub_challenge');
        
        // Get verify token from config
        $verify_token = config('facebook.verify_token');
        
        Log::info('[FacebookBot] Webhook verification attempt', [
            'mode' => $mode,
            'token_match' => ($token === $verify_token)
        ]);
        
        // Verify the token
        if ($mode === 'subscribe' && $token === $verify_token) {
            http_response_code(200);
            echo $challenge;
            exit;
        } else {
            http_response_code(403);
            exit('Forbidden');
        }
    }
    
    /**
     * POST /api/facebook/webhook
     * 
     * Receive webhook events from Facebook
     * Processes messages and calls appropriate handlers
     */
    public function webhook()
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        
        Log::info('[FacebookBot] Webhook received', ['data' => $data]);
        
        // Verify signature
        if (!$this->verifySignature($body)) {
            Log::error('[FacebookBot] Invalid signature');
            http_response_code(403);
            return $this->renderError('Invalid signature');
        }
        
        // Process webhook
        if (!empty($data['object']) && $data['object'] === 'page') {
            if (!empty($data['entry'])) {
                foreach ($data['entry'] as $entry) {
                    if (!empty($entry['messaging'])) {
                        foreach ($entry['messaging'] as $event) {
                            $this->handleEvent($event);
                        }
                    }
                }
            }
        }
        
        http_response_code(200);
        return $this->renderSuccess(['status' => 'ok']);
    }
    
    /**
     * Verify Facebook webhook signature
     * 
     * @param string $body Raw request body
     * @return bool
     */
    private function verifySignature($body)
    {
        $app_secret = config('facebook.app_secret');
        $header_signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
        
        // If no app secret configured, allow (for testing)
        if (empty($app_secret)) {
            return true;
        }
        
        // If no signature header, reject
        if (empty($header_signature)) {
            return false;
        }
        
        $signature = hash_hmac('sha1', $body, $app_secret);
        $expected = 'sha1=' . $signature;
        
        return hash_equals($header_signature, $expected);
    }
    
    /**
     * Route event to appropriate handler
     * 
     * @param array $event Facebook event data
     */
    private function handleEvent($event)
    {
        $sender_id = $event['sender']['id'] ?? null;
        $timestamp = $event['timestamp'] ?? null;
        
        if (!$sender_id) {
            return;
        }
        
        // Text message
        if (!empty($event['message']['text'])) {
            $this->handleMessage($sender_id, $event['message']['text']);
        }
        
        // Quick reply
        if (!empty($event['message']['quick_reply'])) {
            $this->handleQuickReply($sender_id, $event['message']['quick_reply']);
        }
        
        // Postback (button click)
        if (!empty($event['postback'])) {
            $this->handlePostback($sender_id, $event['postback']);
        }
    }
    
    /**
     * Handle text message
     * 
     * @param string $sender_id Facebook user ID
     * @param string $text Message text
     */
    private function handleMessage($sender_id, $text)
    {
        Log::info('[FacebookBot] Message', [
            'sender' => $sender_id,
            'text' => $text
        ]);
        
        // TODO: Route to existing bot logic
        // - Link sender to customer account
        // - Process order session
        // - Handle tracking codes
        // - Send response
        
        $this->sendMessage($sender_id, "Bot received: " . $text);
    }
    
    /**
     * Handle quick reply selection
     * 
     * @param string $sender_id Facebook user ID
     * @param array $payload Quick reply payload
     */
    private function handleQuickReply($sender_id, $payload)
    {
        Log::info('[FacebookBot] Quick reply', [
            'sender' => $sender_id,
            'payload' => $payload
        ]);
        
        $payload_text = $payload['payload'] ?? null;
        
        // TODO: Process quick reply
        $this->sendMessage($sender_id, "Quick reply received: " . $payload_text);
    }
    
    /**
     * Handle postback (button click, menu selection)
     * 
     * @param string $sender_id Facebook user ID
     * @param array $postback Postback data
     */
    private function handlePostback($sender_id, $postback)
    {
        Log::info('[FacebookBot] Postback', [
            'sender' => $sender_id,
            'postback' => $postback
        ]);
        
        $payload = $postback['payload'] ?? null;
        
        // TODO: Process postback
        $this->sendMessage($sender_id, "Postback received: " . $payload);
    }
    
    /**
     * Send message to user via Facebook API
     * 
     * @param string $recipient_id Facebook user ID
     * @param string $message Message text
     * @return array Response from Facebook API
     */
    private function sendMessage($recipient_id, $message)
    {
        $page_token = config('facebook.page_token');
        $url = 'https://graph.instagram.com/v18.0/me/messages?access_token=' . $page_token;
        
        $data = [
            'recipient' => ['id' => $recipient_id],
            'message' => ['text' => $message]
        ];
        
        return $this->makeRequest($url, $data);
    }
    
    /**
     * Make HTTP request to Facebook API
     * 
     * @param string $url API endpoint
     * @param array $data Request data
     * @return array Response
     */
    private function makeRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        Log::info('[FacebookBot] API Response', [
            'http_code' => $info['http_code'],
            'response' => $response,
            'error' => $error
        ]);
        
        return [
            'code' => $info['http_code'],
            'body' => json_decode($response, true),
            'error' => $error
        ];
    }
}
