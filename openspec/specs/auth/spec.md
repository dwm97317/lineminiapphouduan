# auth Specification

## Purpose
TBD - created by archiving change add-line-mini-app. Update Purpose after archive.
## Requirements
### Requirement: LINE Mini App Login
The system SHALL support logging in users via LINE Mini App using their LINE ID Token.

#### Scenario: Successful LINE Login
- **WHEN** a valid `id_token` and `user_info` are submitted to `/api/passport/loginMpLine`
- **THEN** the system returns a valid authentication `token`
- **AND** a new user account is created if one does not exist

#### Scenario: Invalid Token
- **WHEN** an invalid or expired `id_token` is submitted
- **THEN** the system returns an error 401

