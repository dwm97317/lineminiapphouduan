# Design Document

## Overview

This design document describes the enhancement of the LINE messaging notification configuration system for the LINE Mini App logistics platform. The system extends the existing `line_messaging` configuration structure to support comprehensive message templates with Flex Message format, enabling rich, interactive notifications for seven business scenarios: package warehousing, shipping, payment success, packing complete, payment order generation, warehouse arrival, and outbound application.

The design follows a layered architecture with clear separation between configuration management, service layer, and presentation layer. It maintains backward compatibility with existing configurations while providing a migration path for legacy systems.

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                    Backend Management UI                     │
│  (LineConfig Controller + View - Configuration Interface)   │
└───────────────────┬─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────────────────────────┐
│                   Configuration Layer                        │
│         (Setting Model - Database Storage)                   │
└───────────────────┬─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────────────────────────┐
│                   Message Dispatch Layer                     │
│        (Message Service - Route to WeChat/LINE)             │
└───────────────────┬─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────────────────────────┐
│                LINE Message Service Layer                    │
│  (Basics Base Class + Scenario Classes: Inwarehouse, etc)  │
└───────────────────┬─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────────────────────────┐
│                  LINE Messaging API                          │
│            (External LINE Platform)                          │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

1. **Configuration**: Admin configures templates via web interface → Saved to database
2. **Trigger**: Business event occurs (e.g., package warehoused) → Calls Message::send()
3. **Dispatch**: Message service routes to appropriate LINE scenario class
4. **Render**: Scenario class loads template, replaces variables, builds deep link
5. **Send**: LINE API client sends Flex Message to user
6. **Log**: Result logged to database for monitoring


## Components and Interfaces

### 1. Configuration Model (Setting.php)

**Purpose**: Store and retrieve LINE messaging configuration from database

**Key Methods**:
- `getItem(string $key, int $wxappId)`: Retrieve configuration by key
- `edit(string $key, array $values, int $wxappId)`: Save configuration
- `defaultData()`: Provide default configuration structure

**Configuration Structure**:
```php
[
    'is_enable' => '0|1',           // Global enable switch
    'channel_id' => string,          // LINE Channel ID
    'channel_secret' => string,      // LINE Channel Secret
    'access_token' => string,        // Channel Access Token
    'api_base_url' => string,        // API endpoint
    'timeout' => int,                // Request timeout (seconds)
    'retry_times' => int,            // Retry attempts
    'log_enabled' => '0|1',          // Logging switch
    'liff_id' => string,             // LIFF App ID
    'liff_url' => string,            // LIFF Base URL
    'templates' => [
        'message_type' => [
            'is_enable' => '0|1',
            'name' => string,
            'alt_text' => string,
            'priority' => 'high|normal|low',
            'send_delay' => int,
            'flex_template' => array,  // Flex Message JSON
            'variables' => array       // Variable names
        ]
    ]
]
```

### 2. Message Dispatch Service (Message.php)

**Purpose**: Route message sending requests to appropriate platform (WeChat/LINE)

**Key Methods**:
- `send(string $sceneName, array $param)`: Send to both platforms
- `sendWx(string $sceneName, array $param)`: Send WeChat message
- `sendLine(string $sceneName, array $param)`: Send LINE message

**Scene Mapping**:
```php
[
    'package.inwarehouse' => Inwarehouse::class,
    'package.sendpack' => Sendpack::class,
    'package.payment' => Payment::class,
    'package.dabaosuccess' => Dabaosuccess::class,
    'package.payorder' => Payorder::class,
    'package.toshop' => Toshop::class,
    'package.outapply' => Outapply::class
]
```

### 3. LINE Message Service Base Class (Basics.php)

**Purpose**: Provide common functionality for all LINE message scenarios

**Key Methods**:
- `send(array $param)`: Abstract method implemented by subclasses
- `sendLineFlexMsg(int $wxappId, string $userId, string $messageType, array $data)`: Core sending logic
- `renderTemplate(array $template, array $data)`: Replace template variables
- `buildLiffUrl(string $path, array $params, int $wxappId)`: Construct deep links
- `getLineUserIdByUserId(int $userId)`: Retrieve LINE User ID
- `logMessageSend(int $wxappId, string $userId, string $messageType, bool $result)`: Log sending result

**Template Rendering Algorithm**:
```
1. Load template configuration by message type
2. Check if global and template-specific enable flags are true
3. Convert template array to JSON string
4. For each variable in data:
   - Replace {{variable_name}} with actual value
5. Convert JSON string back to array
6. Return rendered template
```

### 4. Scenario Service Classes

**Purpose**: Implement specific message sending logic for each business scenario

**Classes**:
- `Inwarehouse`: Package warehousing notification
- `Sendpack`: Shipping notification
- `Payment`: Payment success notification
- `Dabaosuccess`: Packing complete notification
- `Payorder`: Payment order generation notification
- `Toshop`: Warehouse arrival notification
- `Outapply`: Outbound application notification

**Common Pattern**:
```php
class Inwarehouse extends Basics {
    public function send($param) {
        // 1. Extract order info
        // 2. Get LINE User ID
        // 3. Build deep link URL
        // 4. Prepare template data
        // 5. Call sendLineFlexMsg()
    }
}
```

### 5. Backend Controller (LineConfig.php)

**Purpose**: Handle admin configuration requests

**Key Methods**:
- `index()`: Display configuration page and handle saves
- `testMessage()`: Send test message to specified user
- `previewTemplate()`: Return template structure for preview

**Request Flow**:
```
GET /store/setting.line_config/index
  → Load configurations
  → Render view with form

POST /store/setting.line_config/index
  → Validate input
  → Save to database
  → Return success/error

POST /store/setting.line_config/testMessage
  → Validate LINE User ID
  → Build test data
  → Send message
  → Return result
```

### 6. LINE API Client (LineMessage.php)

**Purpose**: Communicate with LINE Messaging API

**Key Methods**:
- `__construct(string $channelId, string $channelSecret, string $accessToken)`: Initialize client
- `sendFlexMessage(string $userId, string $altText, array $contents)`: Send Flex Message
- `sendTextMessage(string $userId, string $text)`: Send simple text message

**API Integration**:
```
POST https://api.line.me/v2/bot/message/push
Headers:
  Authorization: Bearer {access_token}
  Content-Type: application/json
Body:
  {
    "to": "{line_user_id}",
    "messages": [{
      "type": "flex",
      "altText": "{alt_text}",
      "contents": {flex_template}
    }]
  }
```


## Data Models

### Configuration Data Model

**line_messaging Configuration**:
```php
[
    'key' => 'line_messaging',
    'describe' => 'LINE消息通知',
    'values' => [
        // Channel Configuration
        'is_enable' => '0',              // '0' = disabled, '1' = enabled
        'channel_id' => '',              // LINE Channel ID (numeric string)
        'channel_secret' => '',          // Channel Secret (alphanumeric)
        'access_token' => '',            // Long-lived Channel Access Token
        
        // API Settings
        'api_base_url' => 'https://api.line.me/v2/bot',
        'timeout' => 30,                 // Request timeout in seconds
        'retry_times' => 3,              // Number of retry attempts
        'log_enabled' => '1',            // '0' = no logs, '1' = enable logs
        
        // LIFF Configuration
        'liff_id' => '',                 // LIFF App ID
        'liff_url' => '',                // Full LIFF URL (https://liff.line.me/...)
        
        // Message Templates
        'templates' => [
            'inwarehouse' => [
                'is_enable' => '0',
                'name' => '包裹入库通知',
                'alt_text' => '📦 包裹入库通知',
                'priority' => 'high',
                'send_delay' => 0,
                'flex_template' => [...],  // Flex Message JSON structure
                'variables' => ['shop_name', 'express_num', 'entering_warehouse_time', 'weight', 'remark', 'detail_url']
            ],
            // ... other templates (sendpack, payment, dabaosuccess, payorder, toshop, outapply)
        ]
    ]
]
```

### Flex Message Template Structure

**Standard Template Format**:
```json
{
  "type": "bubble",
  "header": {
    "type": "box",
    "layout": "vertical",
    "contents": [
      {
        "type": "text",
        "text": "📦 Title",
        "weight": "bold",
        "size": "lg",
        "color": "#1DB446"
      }
    ],
    "backgroundColor": "#F0FFF0"
  },
  "body": {
    "type": "box",
    "layout": "vertical",
    "contents": [
      {"type": "text", "text": "Field: {{variable}}", "size": "sm", "wrap": true}
    ],
    "spacing": "sm"
  },
  "footer": {
    "type": "box",
    "layout": "vertical",
    "contents": [
      {
        "type": "button",
        "action": {"type": "uri", "label": "Action", "uri": "{{url}}"},
        "style": "primary",
        "color": "#1DB446"
      }
    ]
  }
}
```

### Message Sending Parameters

**Common Parameters for All Message Types**:
```php
[
    'wxapp_id' => int,        // Mini app ID
    'member_id' => int,       // User ID in system
    // ... scenario-specific fields
]
```

**Inwarehouse Parameters**:
```php
[
    'wxapp_id' => int,
    'member_id' => int,
    'id' => int,                           // Package ID
    'shop_name' => string,                 // Warehouse name
    'express_num' => string,               // Tracking number
    'entering_warehouse_time' => string,   // Timestamp
    'weight' => float,                     // Package weight (kg)
    'remark' => string                     // Additional notes
]
```

**Sendpack Parameters**:
```php
[
    'wxapp_id' => int,
    'member_id' => int,
    'order_sn' => string,      // Order number
    't_order_sn' => string,    // International tracking number
    'weight' => float,         // Total weight
    't_name' => string,        // Shipping method name
    'send_time' => string      // Shipping timestamp
]
```

**Payment Parameters**:
```php
[
    'wxapp_id' => int,
    'member_id' => int,
    'order_sn' => string,      // Order number
    'total_free' => float,     // Payment amount
    'pay_time' => string,      // Payment timestamp
    'remark' => string         // Payment notes
]
```

### Deep Link URL Structure

**Format**: `{liff_url}{path}?{query_string}`

**Examples**:
```
Package Detail:
https://liff.line.me/1234567890-abcdefgh/package/detail?id=456&rtype=10&from=notification

Tracking:
https://liff.line.me/1234567890-abcdefgh/tracking?order_sn=ORD20260114001&from=notification

Payment:
https://liff.line.me/1234567890-abcdefgh/payment?order_id=789&from=notification
```

### Log Entry Structure

**Message Send Log**:
```php
[
    'describe' => 'LINE消息发送',
    'wxapp_id' => int,
    'line_user_id' => string,
    'message_type' => string,  // 'inwarehouse', 'sendpack', etc.
    'result' => string,        // 'success' or 'failed'
    'time' => string,          // Y-m-d H:i:s
    'error' => string          // Error message if failed (optional)
]
```


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Configuration Structure Completeness

*For any* line_messaging configuration retrieved from the database, it should contain all required top-level fields (is_enable, channel_id, channel_secret, access_token, api_base_url, timeout, retry_times, log_enabled, liff_id, liff_url, templates) and each template should contain all required fields (is_enable, name, alt_text, priority, send_delay, flex_template, variables).

**Validates: Requirements 1.1, 1.3, 2.4, 10.1**

### Property 2: Default Template Availability

*For any* wxapp_id, when loading line_messaging configuration, all seven template types (inwarehouse, sendpack, payment, dabaosuccess, payorder, toshop, outapply) should be present with valid default structures.

**Validates: Requirements 1.2**

### Property 3: Disabled Template Skipping

*For any* message type where is_enable is '0' (either globally or at template level), calling sendLineFlexMsg should return false without making LINE API calls.

**Validates: Requirements 1.4, 3.2**

### Property 4: Template Variable Rendering

*For any* template with variables and any data map, renderTemplate should replace all {{variable_name}} occurrences with corresponding data values, leave placeholders unchanged for missing variables, and return a valid array structure.

**Validates: Requirements 2.5, 5.2, 5.3, 5.5**

### Property 5: Required Field Validation

*For any* template configuration, if any required field (name, alt_text, flex_template, variables) is missing or empty, validation should fail and prevent saving.

**Validates: Requirements 2.3**

### Property 6: Deep Link URL Construction

*For any* LIFF base URL, page path, and query parameters, buildLiffUrl should produce a properly formatted URL with encoded query string in the format: {liff_url}{path}?{encoded_params}.

**Validates: Requirements 4.2, 4.5**

### Property 7: LINE User ID Retrieval

*For any* valid user_id in the system, getLineUserIdByUserId should return the corresponding line_user_id from the database or null if not found.

**Validates: Requirements 3.4**

### Property 8: Message Send Logging

*For any* message sending attempt (successful or failed), a log entry should be created containing wxapp_id, line_user_id, message_type, result status, and timestamp.

**Validates: Requirements 3.5, 9.1**

### Property 9: Error Handling Without Exceptions

*For any* LINE API failure or invalid configuration, the send methods should return false and log the error without throwing exceptions, allowing the application to continue.

**Validates: Requirements 3.6, 9.3**

### Property 10: Scene Name Routing

*For any* valid scene name in the scene list, Message::send() should instantiate the correct service class; for invalid scene names, it should return false without errors.

**Validates: Requirements 7.3, 7.4**

### Property 11: Dual Platform Dispatch

*For any* scene name and parameters, Message::send() should attempt both sendWx() and sendLine(), returning true if either succeeds.

**Validates: Requirements 7.2, 7.5**

### Property 12: Configuration Migration Preservation

*For any* old configuration format with existing channel_id, channel_secret, and access_token values, migration should preserve these values in the new structure and add all default templates.

**Validates: Requirements 8.2, 8.3**

### Property 13: Migration Idempotency

*For any* configuration already in new format, running migration should detect it and return without making changes.

**Validates: Requirements 8.5**

### Property 14: Special Character Encoding

*For any* template variable containing special characters (Thai, Chinese, emojis, quotes), renderTemplate should correctly encode them using JSON_UNESCAPED_UNICODE without corruption.

**Validates: Requirements 5.4**

### Property 15: Configuration Field Persistence

*For any* configuration save operation, all provided fields (including channel_id, channel_secret, access_token, template settings) should be persisted to database and retrievable in subsequent loads.

**Validates: Requirements 6.6**

### Property 16: API Configuration Usage

*For any* LINE API request, the system should use the configured api_base_url, timeout, and retry_times values from the line_messaging configuration.

**Validates: Requirements 10.2, 10.3, 10.4**

### Property 17: Conditional Logging

*For any* API interaction, detailed logs should be written when log_enabled is '1' and no logs should be written when log_enabled is '0'.

**Validates: Requirements 10.5**

### Property 18: Template Type Conversion

*For any* template input, if it's an array, renderTemplate should convert it to JSON string for processing; if it's already a string, it should use it directly.

**Validates: Requirements 5.1**

### Property 19: Error Detail Logging

*For any* LINE API error response, the system should log the error details including error code, message, and context for troubleshooting.

**Validates: Requirements 9.2**

### Property 20: Invalid Configuration Handling

*For any* configuration with missing or invalid required fields (channel_id, access_token), the system should log a warning and skip message sending without attempting API calls.

**Validates: Requirements 9.4**


## Error Handling

### Configuration Errors

**Missing Required Fields**:
- **Detection**: Check for empty channel_id, channel_secret, or access_token
- **Action**: Log warning, return false from send methods
- **User Impact**: Messages not sent, admin notified via logs

**Invalid Template Structure**:
- **Detection**: Validate flex_template has required sections (type, header/body/footer)
- **Action**: Log error, skip template rendering
- **User Impact**: Specific message type fails, others continue

**Database Connection Failure**:
- **Detection**: Catch database exceptions when loading configuration
- **Action**: Log error, use cached configuration if available
- **User Impact**: Temporary degradation, retry on next attempt

### API Errors

**Authentication Failure (401)**:
- **Detection**: LINE API returns 401 status
- **Action**: Log error with "Invalid access token", return false
- **User Impact**: All messages fail until token updated

**Rate Limiting (429)**:
- **Detection**: LINE API returns 429 status
- **Action**: Log warning, implement exponential backoff, retry
- **User Impact**: Delayed message delivery

**Network Timeout**:
- **Detection**: Request exceeds configured timeout
- **Action**: Log timeout error, retry up to retry_times
- **User Impact**: Delayed or failed message delivery

**Invalid User ID**:
- **Detection**: LINE API returns 400 with "Invalid user ID"
- **Action**: Log error with user details, return false
- **User Impact**: Specific user doesn't receive message

### Template Rendering Errors

**Missing Variable Data**:
- **Detection**: Variable in template not found in data array
- **Action**: Leave {{variable}} placeholder unchanged, log warning
- **User Impact**: Message sent with placeholder visible

**JSON Encoding Failure**:
- **Detection**: json_encode() returns false
- **Action**: Log error with data that failed, return false
- **User Impact**: Message not sent

**Malformed Flex Message**:
- **Detection**: LINE API returns 400 with "Invalid flex message"
- **Action**: Log error with template structure, return false
- **User Impact**: Message not sent, template needs fixing

### User Data Errors

**LINE User ID Not Found**:
- **Detection**: Database query returns null for user_id
- **Action**: Log info "User not linked to LINE", return false
- **User Impact**: User doesn't receive LINE notification (may receive WeChat)

**Invalid Member ID**:
- **Detection**: member_id not found in database
- **Action**: Log error, return false
- **User Impact**: No notification sent

### Error Recovery Strategies

**Retry Logic**:
```php
for ($i = 0; $i < $retry_times; $i++) {
    try {
        $result = $lineApi->send($message);
        if ($result) return true;
    } catch (Exception $e) {
        if ($i == $retry_times - 1) {
            log_error($e);
            return false;
        }
        sleep(pow(2, $i)); // Exponential backoff
    }
}
```

**Graceful Degradation**:
- If LINE messaging fails, WeChat messaging still attempted
- If template rendering fails, skip that message type but continue others
- If configuration load fails, use cached values from previous load

**Error Notification**:
- Critical errors (auth failure, invalid config) logged with high priority
- Transient errors (network timeout, rate limit) logged with normal priority
- Info messages (user not found) logged with low priority


## Testing Strategy

### Dual Testing Approach

This feature will be tested using both unit tests and property-based tests:

- **Unit tests**: Verify specific examples, edge cases, and error conditions
- **Property tests**: Verify universal properties across all inputs

Both testing approaches are complementary and necessary for comprehensive coverage. Unit tests catch concrete bugs in specific scenarios, while property tests verify general correctness across a wide range of inputs.

### Property-Based Testing

**Framework**: PHPUnit with [php-quickcheck](https://github.com/steos/php-quickcheck) or similar PBT library

**Configuration**: Each property test should run minimum 100 iterations to ensure comprehensive input coverage

**Test Tagging**: Each property-based test must include a comment referencing the design property:
```php
/**
 * Feature: line-config-enhancement, Property 1: Configuration Structure Completeness
 */
public function testConfigurationStructureCompleteness() {
    // Property test implementation
}
```

### Unit Testing Strategy

**Test Organization**:
```
tests/
  ├── Unit/
  │   ├── Service/
  │   │   ├── Message/
  │   │   │   ├── Line/
  │   │   │   │   ├── BasicsTest.php
  │   │   │   │   ├── InwarehouseTest.php
  │   │   │   │   ├── SendpackTest.php
  │   │   │   │   └── ...
  │   │   │   └── MessageTest.php
  │   │   └── LineConfigMigrationTest.php
  │   └── Model/
  │       └── SettingTest.php
  └── Integration/
      ├── LineMessageFlowTest.php
      └── ConfigurationManagementTest.php
```

**Unit Test Coverage**:

1. **Configuration Model Tests**:
   - Test loading default configuration
   - Test saving and retrieving configuration
   - Test configuration validation
   - Test handling missing fields

2. **Template Rendering Tests**:
   - Test variable replacement with various data types
   - Test handling missing variables
   - Test special character encoding (Thai, Chinese, emojis)
   - Test JSON conversion (array to string and back)
   - Test malformed template handling

3. **Deep Link Construction Tests**:
   - Test URL building with various paths and parameters
   - Test query string encoding
   - Test handling empty parameters
   - Test handling special characters in parameters

4. **Message Sending Tests**:
   - Test successful message send
   - Test handling disabled global flag
   - Test handling disabled template flag
   - Test handling missing LINE User ID
   - Test handling API errors (401, 429, timeout)
   - Test retry logic

5. **Migration Tests**:
   - Test detecting old configuration format
   - Test converting old to new format
   - Test preserving existing values
   - Test skipping already-migrated configurations

6. **Error Handling Tests**:
   - Test exception catching and logging
   - Test graceful degradation
   - Test error message formatting

### Integration Testing

**Test Scenarios**:

1. **End-to-End Message Flow**:
   ```php
   // Simulate package warehousing event
   $result = Message::send('package.inwarehouse', [
       'wxapp_id' => 10001,
       'member_id' => 123,
       'shop_name' => 'Test Warehouse',
       // ... other fields
   ]);
   
   // Verify:
   // - Configuration loaded correctly
   // - Template rendered with data
   // - Deep link constructed
   // - LINE API called with correct payload
   // - Log entry created
   ```

2. **Configuration Management Flow**:
   ```php
   // Save configuration via controller
   $response = $this->post('/store/setting.line_config/index', [
       'line_messaging' => [
           'is_enable' => '1',
           'templates' => [
               'inwarehouse' => ['is_enable' => '1', ...]
           ]
       ]
   ]);
   
   // Verify:
   // - Configuration saved to database
   // - Can be retrieved correctly
   // - Validation errors caught
   ```

3. **Test Message Sending**:
   ```php
   // Send test message via controller
   $response = $this->post('/store/setting.line_config/testMessage', [
       'message_type' => 'inwarehouse',
       'line_user_id' => 'U1234567890abcdef'
   ]);
   
   // Verify:
   // - Test data generated correctly
   // - Message sent to LINE API
   // - Response indicates success/failure
   ```

### Property-Based Test Examples

**Property 1: Configuration Structure Completeness**:
```php
/**
 * Feature: line-config-enhancement, Property 1: Configuration Structure Completeness
 */
public function testConfigurationStructureCompleteness() {
    $this->forAll(
        Generator::wxappId(),
        function($wxappId) {
            $config = SettingModel::getItem('line_messaging', $wxappId);
            
            // Assert all required top-level fields exist
            $this->assertArrayHasKey('is_enable', $config);
            $this->assertArrayHasKey('channel_id', $config);
            $this->assertArrayHasKey('templates', $config);
            
            // Assert all templates have required fields
            foreach ($config['templates'] as $template) {
                $this->assertArrayHasKey('is_enable', $template);
                $this->assertArrayHasKey('flex_template', $template);
                $this->assertArrayHasKey('variables', $template);
            }
            
            return true;
        }
    );
}
```

**Property 4: Template Variable Rendering**:
```php
/**
 * Feature: line-config-enhancement, Property 4: Template Variable Rendering
 */
public function testTemplateVariableRendering() {
    $this->forAll(
        Generator::flexTemplate(),
        Generator::variableData(),
        function($template, $data) {
            $service = new Basics();
            $rendered = $service->renderTemplate($template, $data);
            
            // Assert all variables in data are replaced
            foreach ($data as $key => $value) {
                $this->assertStringNotContainsString(
                    "{{" . $key . "}}",
                    json_encode($rendered)
                );
            }
            
            // Assert result is valid array
            $this->assertIsArray($rendered);
            
            return true;
        }
    );
}
```

**Property 6: Deep Link URL Construction**:
```php
/**
 * Feature: line-config-enhancement, Property 6: Deep Link URL Construction
 */
public function testDeepLinkUrlConstruction() {
    $this->forAll(
        Generator::liffUrl(),
        Generator::pagePath(),
        Generator::queryParams(),
        function($liffUrl, $path, $params) {
            $service = new Basics();
            $url = $service->buildLiffUrl($path, $params, null);
            
            // Assert URL starts with LIFF base
            $this->assertStringStartsWith($liffUrl, $url);
            
            // Assert path is included
            $this->assertStringContainsString($path, $url);
            
            // Assert all params are in query string
            foreach ($params as $key => $value) {
                $this->assertStringContainsString(
                    urlencode($key) . '=' . urlencode($value),
                    $url
                );
            }
            
            return true;
        }
    );
}
```

### Test Data Generators

**Custom Generators for Property Tests**:
```php
class Generator {
    public static function wxappId() {
        return Gen::choose(10000, 99999);
    }
    
    public static function flexTemplate() {
        return Gen::map(
            function($vars) {
                return [
                    'type' => 'bubble',
                    'body' => [
                        'type' => 'box',
                        'contents' => array_map(
                            fn($v) => ['type' => 'text', 'text' => "{{" . $v . "}}"],
                            $vars
                        )
                    ]
                ];
            },
            Gen::listOf(Gen::alphaString())
        );
    }
    
    public static function variableData() {
        return Gen::associative([
            'shop_name' => Gen::alphaString(),
            'express_num' => Gen::alphaNumString(),
            'weight' => Gen::choose(0.1, 100.0),
            'remark' => Gen::unicodeString() // Test special characters
        ]);
    }
    
    public static function liffUrl() {
        return Gen::constant('https://liff.line.me/1234567890-abcdefgh');
    }
    
    public static function pagePath() {
        return Gen::elements([
            '/package/detail',
            '/tracking',
            '/payment',
            '/order/detail'
        ]);
    }
    
    public static function queryParams() {
        return Gen::associative([
            'id' => Gen::choose(1, 10000),
            'from' => Gen::constant('notification')
        ]);
    }
}
```

### Testing Best Practices

1. **Isolation**: Each test should be independent and not rely on external state
2. **Mocking**: Mock LINE API calls to avoid external dependencies
3. **Fixtures**: Use realistic test data based on actual business scenarios
4. **Coverage**: Aim for >80% code coverage, 100% for critical paths
5. **Performance**: Property tests should complete in reasonable time (<5 seconds per property)
6. **Documentation**: Each test should have clear comments explaining what it verifies

### Manual Testing Checklist

- [ ] Configure LINE messaging in backend interface
- [ ] Save configuration and verify persistence
- [ ] Send test message for each template type
- [ ] Verify messages appear correctly in LINE app
- [ ] Click deep link buttons and verify navigation
- [ ] Test with disabled templates (should not send)
- [ ] Test with invalid configuration (should log errors)
- [ ] Test migration from old configuration format
- [ ] Verify logs are created for all sends
- [ ] Test error handling (invalid token, network failure)
