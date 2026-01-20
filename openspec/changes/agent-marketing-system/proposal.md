# Proposal: Agent Marketing System & Upsell Interactions

## 1. Summary
Implement a comprehensive Marketing System for the LINE MiniApp backend, featuring Upsell on the Fly (Warehouse recommended services), Agent White-labeling, and a Commission System. The implementation must strictly adhere to a 300-line file limit and modular view structure.

## 2. Problem Statement
*   **Missed Revenue**: Warehouse staff identify sales opportunities (e.g., fragile items needing crating) but lack a tool to recommend them to users immediately.
*   **Agent Identity**: Distributors/Agents want to show their own brand (Logo/Color) to their sub-customers to build loyalty.
*   **Incentive**: Agents lack financial motivation to push value-added services.

## 3. Proposed Solution

### 3.1 Marketing Center (Backend)
Create a new `Marketing` controller and modular views (`upsell`, `agent`, `commission`) to manage:
*   **Upsell Config**: Trigger keywords, timeout settings.
*   **White Label**: Global switch, permission levels.
*   **Commission**: Dynamic whitelist of services eligible for agent commission.

### 3.2 Warehouse Upsell Workflow
*   **Scan & Detect**: Warehouse staff scans a package.
*   **Recommendation**: UI shows a "Recommend" button (auto-highlighted by Smart Triggers like "Glass").
*   **Execution**: Staff takes a photo, selects a service, and submits.
*   **Notification**: User receives a LINE Flex Message to Confirm/Ignore.

### 3.3 Technical Architecture
*   **Multi-tenancy**: All DB reads/writes MUST use `wxapp_id`.
*   **Database**: New table `yoshop_marketing_setting` (Key-Value store).
*   **Optimization**: 300-line limit per file; Views split into Partials.

## 4. Impact
*   **Merchant**: Increased revenue from value-added services.
*   **Agent**: Earn commission, stronger brand presence.
*   **Warehouse**: Streamlined workflow for reporting issues.
