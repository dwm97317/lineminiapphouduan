# admin-settings Specification

## Purpose
TBD - created by archiving change add-line-mini-app. Update Purpose after archive.
## Requirements
### Requirement: LINE Configuration Management
The system SHALL provide an admin interface for merchants to configure LINE Mini App credentials.

#### Scenario: Save LINE Configuration
- **WHEN** an admin submits LINE Channel ID and Channel Secret via the backend
- **THEN** the configuration is saved to `yoshop_setting` table with key 'line_config'
- **AND** the configuration is associated with the current merchant's wxapp_id

#### Scenario: Retrieve LINE Configuration
- **WHEN** the system needs LINE credentials for authentication
- **THEN** it retrieves the configuration from Setting model using key 'line_config'
- **AND** returns the Channel ID for the current merchant

