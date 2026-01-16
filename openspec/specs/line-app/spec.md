# line-app Specification

## Purpose
TBD - created by archiving change add-line-mini-app. Update Purpose after archive.
## Requirements
### Requirement: LINE App Configuration
The system SHALL provide an endpoint to retrieve LINE Mini App configuration and base information.

#### Scenario: Get Base Info
- **WHEN** a GET request is made to `/api/line_app/base`
- **THEN** it returns success with empty data (matching Wxapp behavior) or relevant config if needed

### Requirement: LINE Help Center
The system SHALL provide access to help articles for LINE users, reusing the existing help model logic if applicable.

#### Scenario: Get Help List
- **WHEN** a GET request is made to `/api/line_app/help`
- **THEN** it returns a list of help articles

