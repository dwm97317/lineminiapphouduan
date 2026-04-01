# Bot System Test Plan & Test Cases

This document provides a comprehensive test plan and all functional testing scenarios for the Bot system, covering account linking, order sessions, barcode management, anti-collision, multi-tenant isolation, and exception handling.

## 1. Functional Testing

### 1.1 Account Linking
| TC ID | Test Case | Steps | Expected Result | Status |
|---|---|---|---|---|
| TC_FUNC_01 | Valid Customer ID Linking | 1. Open Bot.<br>2. Click 'Link Account'.<br>3. Input valid Customer ID. | Bot responds "Linked successfully", displays customer name, and unlocks order features. | [ ] |
| TC_FUNC_02 | Invalid Customer ID Linking | 1. Open Bot.<br>2. Click 'Link Account'.<br>3. Input invalid/non-existent ID. | Bot replies "Invalid ID or ID does not exist". Prompts to re-enter. Prevents ordering while unlinked. | [ ] |

### 1.2 Order Session Creation
| TC ID | Test Case | Steps | Expected Result | Status |
|---|---|---|---|---|
| TC_FUNC_03 | Create via Facebook | 1. Send an order message via FB Messenger.<br>2. Trigger order flow. | System initializes a new Order Session, source is logged as 'FB'. | [ ] |
| TC_FUNC_04 | Create via Instagram | 1. Send an order message via IG Direct.<br>2. Trigger order flow. | System initializes a new Order Session, source is logged as 'IG'. | [ ] |

### 1.3 Info Supplement Flow
| TC ID | Test Case | Steps | Expected Result | Status |
|---|---|---|---|---|
| TC_FUNC_05 | Add Order Details | 1. Access pending session.<br>2. Send: Shop name, date, amount, barcode. | Bot correctly extracts details, updates the current session, and replies with a confirmation. | [ ] |

### 1.4 Barcode Linking
| TC ID | Test Case | Steps | Expected Result | Status |
|---|---|---|---|---|
| TC_FUNC_06 | Normal Barcode Input | 1. Send 1 valid barcode not yet in DB. | Bot replies "Barcode added successfully". | [ ] |
| TC_FUNC_07 | Duplicate Barcode Input | 1. Send Barcode A (already linked in DB). | Bot throws an error: "Barcode already exists" and refuses insertion. | [ ] |
| TC_FUNC_08 | Invalid Barcode Format | 1. Send a barcode containing special characters. | Bot throws an error: "Invalid barcode format". | [ ] |

### 1.5 Multi-session & Keywords
| TC ID | Test Case | Steps | Expected Result | Status |
|---|---|---|---|---|
| TC_FUNC_09 | Keyword Activation | 1. Send keywords like "order", "buy", "create order". | Bot detects keywords and activates a new pending order session. | [ ] |
| TC_FUNC_10 | Concurrent Order Sessions | 1. Create order 1 (unfinished).<br>2. Trigger order 2 creation. | System suspends order 1 and asks: "You have an unfinished order. Skip it or continue previous?". | [ ] |

---

## 2. Anti-collision Testing

| TC ID | Test Case | Steps | Expected Result | Status |
|---|---|---|---|---|
| TC_ANTI_01 | 24h Timeout Auto-Renewal | 1. Keep a session open for >24h.<br>2. Send new details. | System closes old session, auto-initializes a brand new session. | [ ] |
| TC_ANTI_02 | Update Same Seller | 1. Open unfinished session with Shop A.<br>2. Send message to Shop A. | Message appended to open Session of Shop A. No new session created. | [ ] |
| TC_ANTI_03 | Different Seller Confirmation | 1. Open unfinished session with Shop A.<br>2. Message Shop B. | Bot detects mismatch, asks: "Confirm creating new order for Shop B?". | [ ] |
| TC_ANTI_04 | User Selects [New Order] | 1. Click button [New Order]. | Bot pauses Shop A session, exclusively opens session for Shop B. | [ ] |

---

## 3. Multi-tenant Isolation Testing

| TC ID | Test Case | Steps | Expected Result | Status |
|---|---|---|---|---|
| TC_TENANT_01 | wxapp_id Data Isolation | 1. Create order under wxapp_id=1.<br>2. Use wxapp_id=2 account to search. | wxapp_id=2 cannot view or search wxapp_id=1 data. | [ ] |
| TC_TENANT_02 | Cross-Tenant API Call | 1. Call API for wxapp_id=1 data using auth token from wxapp_id=2. | API instantly returns HTTP 403 Forbidden or 401 Unauthorized. | [ ] |

---

## 4. Exception Handling

| TC ID | Test Case | Steps | Expected Result | Status |
|---|---|---|---|---|
| TC_EXC_01 | Network Timeout & Retry | 1. Send API call and simulate drop. | System applies auto-retry (e.g. 3 attempts). Replies "Network slow" if all fail. App/Bot doesn't crash. | [ ] |
| TC_EXC_02 | Unlinked Access Guard | 1. Call order API without linking account. | Middleware intercepts request, Bot replies "Please link account to use this feature". | [ ] |

---

## 5. Test Report Template

- **Tester:** ...........................................
- **Date:** ...../...../202...
- **Environment:** (e.g. Staging, Production)

**Summary Overview:**
- Total Test Cases (TC): 16
- Passed: 0
- Failed: 0
- Skipped: 0
- Pass Rate: 0%

---

## 6. Bug List Template

| Bug ID | Severity | Environment | Bug Summary | Steps to Reproduce | Actual Result | Ticket Status |
|---|---|---|---|---|---|---|
| BUG-001 | High | Staging - FB Bot | Duplicate barcode not flagged | Input "112233" twice continuously | No error notification. Backend saves two identical rows in DB. | Open |
| BUG-002 | Critical | Staging - Bot App | Cross-tenant API fetching possible | Replace `wxapp_id=2` with `1` keeping token | API returns Tenant 1 customer data | Open |
