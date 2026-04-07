# Facebook Bot Implementation Guide

Quick start guide for implementing Facebook Messenger integration.

---

## 🚀 Quick Summary

Your bot currently:
- ✅ Has **LINE integration** working
- ✅ Has **core bot logic** (order session, tracking codes)
- ❌ **Lacks Facebook webhook handler**

**What to do:** Implement a Facebook webhook receiver to connect your existing bot logic with Facebook Messenger.

---

## Phase 1: Create Facebook Webhook Controller (Required First)

### Step 1.1: Create the Controller File

**File:** `/source/application/api/controller/FacebookBot.php`

```php
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
```

### Step 1.2: Add Configuration

**File:** `/source/application/config.php` (add to existing file)

```php
<?php

return [
    // ... existing config ...
    
    // Facebook configuration
    'facebook' => [
        'app_id'       => env('FACEBOOK_APP_ID', ''),
        'app_secret'   => env('FACEBOOK_APP_SECRET', ''),
        'page_token'   => env('FACEBOOK_PAGE_TOKEN', ''),
        'verify_token' => env('FACEBOOK_VERIFY_TOKEN', 'your_random_verify_token'),
        'api_version'  => 'v18.0',
    ],
];
```

### Step 1.3: Add Routes

**File:** `/source/application/route.php` (add these routes)

```php
<?php
return [
    // ... existing routes ...
    
    // Facebook Bot Webhook
    'api/facebook/webhook' => 'api/FacebookBot/verify|webhook',
];
```

---

## Phase 2: Test Webhook Without Facebook Account

### Test 1: Webhook Verification (GET Request)

```bash
# Test webhook verification
curl -X GET "http://localhost:8000/api/facebook/webhook?hub.mode=subscribe&hub.verify_token=your_random_verify_token&hub.challenge=test123"

# Expected: Should return status 200 and echo the challenge value
```

### Test 2: Webhook Message Reception (POST Request)

```bash
# Test webhook message reception
curl -X POST http://localhost:8000/api/facebook/webhook \
  -H "Content-Type: application/json" \
  -H "X-Hub-Signature: sha1=fake_signature" \
  -d '{
    "object":"page",
    "entry":[{
      "id":"123456",
      "time":1234567890,
      "messaging":[{
        "sender":{"id":"user_123"},
        "recipient":{"id":"page_123"},
        "timestamp":1234567890,
        "message":{
          "mid":"msg_123",
          "text":"Hello bot"
        }
      }]
    }]
  }'

# Expected: Should process and return 200 OK
```

### Test 3: Check Logs

```bash
# View webhook logs
tail -f /path/to/runtime/log/*.log | grep FacebookBot
```

---

## Phase 3: Environment Setup

### Step 3.1: Create .env File (if using env function)

**File:** `/source/.env`

```
FACEBOOK_APP_ID=123456789
FACEBOOK_APP_SECRET=abc123def456xyz
FACEBOOK_PAGE_TOKEN=EAABa1b2c3d4e5f6...
FACEBOOK_VERIFY_TOKEN=my_random_verify_token_here
```

### Step 3.2: Verify ThinkPHP Config Loading

Test that config loads correctly:

```php
// Quick test script
$config = config('facebook');
var_dump($config);
// Should show: array with app_id, app_secret, page_token, verify_token
```

---

## Phase 4: Ready for Facebook Account Setup

Once webhook is working, you can:

### Step 4.1: Create Facebook Developer Account
- Visit: https://developers.facebook.com/
- Sign up and verify email

### Step 4.2: Create App
- Go to My Apps → Create App
- App name: "Order Tracking Bot"
- Category: "Business"

### Step 4.3: Add Messenger Product
- In app dashboard: Add Product → Messenger
- Configure Platform: Facebook

### Step 4.4: Create Test Fanpage
- Go to Facebook
- Create a new page (test purpose is fine)
- Get Page Access Token from app dashboard

### Step 4.5: Configure Webhook in Facebook
- In Messenger settings: Webhooks
- Callback URL: `https://yourdomain.com/api/facebook/webhook`
- Verify Token: Use same token from Step 3.1
- Subscribe Fields: `messages`, `messaging_postbacks`, `messaging_quick_replies`
- Click "Verify and Save"

### Step 4.6: Subscribe Page to App
- In app dashboard: Select your page
- Generate Page Access Token
- Save to .env

---

## Phase 5: Connect Bot Logic

Once webhook is working, integrate with existing bot logic:

### In `handleMessage()` function, add:

```php
private function handleMessage($sender_id, $text)
{
    // 1. Link Facebook sender ID to customer
    $customer = $this->linkFacebookUser($sender_id);
    if (!$customer) {
        $this->sendMessage($sender_id, "Please link your account first");
        return;
    }
    
    // 2. Process message through existing bot logic
    $response = $this->processOrderMessage(
        $customer['id'],
        $customer['wxapp_id'],
        $text
    );
    
    // 3. Send response
    $this->sendMessage($sender_id, $response);
}

private function linkFacebookUser($facebook_id)
{
    // TODO: Check if Facebook ID is linked to customer
    // If yes: return customer data
    // If no: prompt to link account
}

private function processOrderMessage($customer_id, $wxapp_id, $text)
{
    // TODO: Use existing OrderService to handle bot logic
    // This is platform-independent code
}
```

---

## Phase 6: Testing with Facebook

### Test Flow:
1. Message bot from Facebook
2. Bot links your account
3. Send tracking code
4. Bot responds with order status
5. Verify notification is correct

---

## Common Issues During Implementation

| Issue | Solution |
|-------|----------|
| 403 Forbidden | Check verify_token matches |
| 200 but no response | Check if signature verification is failing |
| Logs not showing | Ensure Log::info() calls are executed |
| API timeout | Reduce CURL timeout or use queue |
| User not found | Implement linkFacebookUser() function |

---

## Next: Detailed Integration

See `BOT_FACEBOOK_INTEGRATION_AUDIT.md` for:
- Complete architecture details
- Performance requirements
- Security checklist
- Error handling strategies
- Full implementation roadmap

---

**Ready to start?**

1. ✅ Create FacebookBot.php controller
2. ✅ Add configuration
3. ✅ Add routes
4. ✅ Test webhook locally
5. Then: Set up Facebook account & integrate with bot logic

