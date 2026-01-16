# Requirements Document

## Introduction

This specification defines the requirements for enhancing the LINE messaging notification configuration system. The enhancement extends the existing `/store/setting.line_config/index` configuration structure to support comprehensive message templates with Flex Message format, covering all business scenarios including package warehousing, shipping, payment, and more.

## Glossary

- **LINE_Messaging_API**: LINE's official API for sending messages to users
- **Flex_Message**: LINE's rich message format supporting custom layouts and interactive elements
- **LIFF**: LINE Front-end Framework for building web apps within LINE
- **Channel_Access_Token**: Long-term authentication token for LINE Messaging API
- **Deep_Link**: URL that opens LIFF app to a specific page with parameters
- **Template_Variable**: Placeholder in message template replaced with actual data (e.g., {{shop_name}})
- **Message_Priority**: Classification of message urgency (high, normal, low)
- **Alt_Text**: Fallback text displayed when Flex Message cannot be rendered

## Requirements

### Requirement 1: Configuration Structure Enhancement

**User Story:** As a system administrator, I want to manage all LINE message templates through a unified configuration structure, so that I can easily enable/disable and customize different notification types.

#### Acceptance Criteria

1. THE System SHALL extend the existing `line_messaging` configuration in Setting.php to include API settings, LIFF configuration, and template definitions
2. WHEN the configuration is loaded, THE System SHALL provide default values for all template types (inwarehouse, sendpack, payment, dabaosuccess, payorder, toshop, outapply)
3. THE System SHALL store each template's Flex Message JSON structure, variables list, priority, and enable status
4. WHEN a template is disabled, THE System SHALL skip sending that message type
5. THE System SHALL maintain backward compatibility with existing configuration structure

### Requirement 2: Message Template Management

**User Story:** As a system administrator, I want to configure individual message templates with custom content and settings, so that I can control what notifications users receive.

#### Acceptance Criteria

1. WHEN configuring a template, THE System SHALL allow setting enable/disable status, alt_text, priority level, and send delay
2. THE System SHALL support seven message types: inwarehouse (package warehousing), sendpack (shipping), payment (payment success), dabaosuccess (packing complete), payorder (payment order), toshop (warehouse arrival), outapply (outbound application)
3. WHEN a template is saved, THE System SHALL validate that all required fields (name, alt_text, flex_template, variables) are present
4. THE System SHALL store Flex Message templates as JSON structures with header, body, and footer sections
5. WHEN rendering a template, THE System SHALL replace all template variables ({{variable_name}}) with actual data values

### Requirement 3: LINE Message Service Layer

**User Story:** As a developer, I want a service layer that handles LINE message sending, so that business logic can easily trigger notifications without knowing LINE API details.

#### Acceptance Criteria

1. THE System SHALL provide a base service class (Basics) with common message sending functionality
2. WHEN sending a message, THE System SHALL check if LINE messaging is enabled globally and for the specific message type
3. THE System SHALL create individual service classes for each message type (Inwarehouse, Sendpack, Payment, etc.)
4. WHEN a service sends a message, THE System SHALL retrieve the user's LINE User ID from the database
5. THE System SHALL log all message sending attempts with timestamp, user ID, message type, and result status
6. WHEN message sending fails, THE System SHALL return false and log the error without throwing exceptions

### Requirement 4: Deep Link URL Construction

**User Story:** As a user, I want to click buttons in LINE messages and be taken directly to relevant pages in the app, so that I can quickly access detailed information.

#### Acceptance Criteria

1. THE System SHALL provide a buildLiffUrl() method that constructs deep link URLs with LIFF base URL, page path, and query parameters
2. WHEN constructing a deep link, THE System SHALL use the LIFF URL from configuration and append the page path and encoded query string
3. THE System SHALL support deep links for package detail, tracking, payment, and order detail pages
4. WHEN a user clicks a message button, THE System SHALL open the LIFF app and navigate to the specified page with parameters
5. THE System SHALL include source tracking parameters (e.g., from=notification) in deep links

### Requirement 5: Template Variable Rendering

**User Story:** As a developer, I want the system to automatically replace template variables with actual data, so that messages contain personalized information for each user.

#### Acceptance Criteria

1. WHEN rendering a template, THE System SHALL convert the template to JSON string if it's an array
2. THE System SHALL replace all occurrences of {{variable_name}} with corresponding values from the data array
3. WHEN a variable is missing from data, THE System SHALL leave the placeholder unchanged
4. THE System SHALL handle special characters in variable values by using JSON_UNESCAPED_UNICODE encoding
5. THE System SHALL return the rendered template as an array structure ready for LINE API

### Requirement 6: Backend Management Interface

**User Story:** As a system administrator, I want a web interface to configure LINE message templates, so that I can manage notifications without editing code.

#### Acceptance Criteria

1. THE System SHALL display all seven message templates in the LINE config page with visual indicators (icons and colors)
2. WHEN viewing a template, THE System SHALL show its enable status, alt_text, priority, send delay, and available variables
3. THE System SHALL provide a "Test Message" button that sends a test notification to a specified LINE User ID
4. THE System SHALL provide a "Preview Template" button that displays the Flex Message structure
5. WHEN saving configuration, THE System SHALL validate all fields and display success or error messages
6. THE System SHALL preserve existing Channel ID, Channel Secret, and Access Token fields

### Requirement 7: Message Dispatch Service

**User Story:** As a developer, I want a unified message dispatch service, so that I can send notifications without knowing whether to use WeChat or LINE.

#### Acceptance Criteria

1. THE System SHALL provide a Message::send() method that accepts scene name and parameters
2. WHEN Message::send() is called, THE System SHALL attempt to send both WeChat and LINE messages
3. THE System SHALL map scene names (e.g., 'package.inwarehouse') to corresponding service classes
4. WHEN a scene name is not registered, THE System SHALL return false without errors
5. THE System SHALL return true if either WeChat or LINE message sending succeeds

### Requirement 8: Configuration Migration

**User Story:** As a system administrator, I want to migrate from old configuration format to new format, so that existing installations can use enhanced features.

#### Acceptance Criteria

1. THE System SHALL provide a migration service that detects old configuration format
2. WHEN old configuration is detected, THE System SHALL convert it to new structure with default templates
3. THE System SHALL preserve existing channel_id, channel_secret, and access_token values during migration
4. WHEN migration is complete, THE System SHALL save the new configuration to database
5. THE System SHALL skip migration if configuration is already in new format

### Requirement 9: Error Handling and Logging

**User Story:** As a system administrator, I want detailed logs of message sending, so that I can troubleshoot issues and monitor system health.

#### Acceptance Criteria

1. WHEN a message is sent, THE System SHALL log the wxapp_id, line_user_id, message_type, result, and timestamp
2. WHEN LINE API returns an error, THE System SHALL log the error details
3. THE System SHALL continue operation even if message sending fails
4. WHEN configuration is invalid, THE System SHALL log a warning and skip message sending
5. THE System SHALL provide log entries that can be filtered by message type and result status

### Requirement 10: API Integration Settings

**User Story:** As a system administrator, I want to configure LINE API connection settings, so that I can control timeout, retry behavior, and logging.

#### Acceptance Criteria

1. THE System SHALL provide configuration fields for api_base_url, timeout, retry_times, and log_enabled
2. WHEN sending messages, THE System SHALL use the configured API base URL (default: https://api.line.me/v2/bot)
3. THE System SHALL apply the configured timeout value to API requests
4. WHEN an API request fails, THE System SHALL retry up to the configured retry_times
5. WHEN log_enabled is set to '1', THE System SHALL write detailed logs for all API interactions
