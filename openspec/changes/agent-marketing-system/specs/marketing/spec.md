# Backend Marketing Center Spec

## ADDED Requirements

### Scenario: Admin configures Upsell Settings
Given the admin is on the "Marketing Center" page
When they switch to the "Upsell" tab
Then they should see a switch to enable "Upsell on the Fly"
And they can configure "Timeout" (hours) and "Require Proof" (switch)
And they can input "Smart Trigger Keywords" (comma separated)

### Scenario: Admin configures White Labeling
Given the admin is on the "White Label" tab
When they toggle "Enable White Label" on
Then they can select the minimum "User Level" required for agents

### Scenario: Admin configures Commission Rules
Given the admin is on the "Commission" tab
When they click "Add Commission Rule"
Then they can select a service (e.g., "Crating") from a dropdown
And enter a "Commission Percent" (e.g., 10%)
And the rule is saved to the whitelist

### Scenario: Multi-tenant Data Isolation
Given two different merchants (wxapp_id = 10001 and 10002)
When merchant 10001 saves a configuration
Then merchant 10002 SHOULD NOT see or be affected by this configuration

## MODIFIED Requirements

### Scenario: Warehouse Upsell Interaction
Given a warehouse staff scans a package with `scan.php`
When the package matches a "Smart Trigger" keyword (e.g. "Glass")
Then the "Recommend Service" button should be highlighted
And clicking it opens a modal to upload a photo and select a service
