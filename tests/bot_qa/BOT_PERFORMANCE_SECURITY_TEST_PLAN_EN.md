# Bot Performance & Security Test Plan

This document defines the Non-Functional Testing scenarios for the Bot system, covering: Performance, Infrastructure Exception Handling, and Security, based on the project's technical documentation.

---

## 1. Performance Testing

| TC ID | Scenario & Technical Requirement | Steps to Execute (Using Tools) | Expected Result (SLA) | Status |
|---|---|---|---|---|
| TC_PERF_01 | **Handle Concurrent Messages**<br>Simulate 100 concurrent users. | Use JMeter/K6 to fire 100 concurrent webhooks/messages against the messaging API endpoint. | - 0% Error rate (no dropped requests).<br>- Average response time < 2s. | [ ] |
| TC_PERF_02 | **Redis Cache Hit Ratio**<br>Optimize DB load. | Send 1000 config-read or barcode query requests continuously. Monitor via Redis Monitor/Grafana. | - Cache Hit Rate > 80%.<br>- Minimal direct SQL queries for static data. | [ ] |
| TC_PERF_03 | **Database Connection Pool**<br>Prevent DB connection limits. | Simulate 200 concurrent Order Creation (Insert DB) requests into the API. | - MySQL does NOT throw "Too many connections" error.<br>- DB queue distributes load safely. | [ ] |
| TC_PERF_04 | **Webhook Backlog Recovery**<br>Process stalled messages when returning online. | 1. Terminate Bot Worker process.<br>2. Spam 500 FB messages.<br>3. Restart Worker process. | - System auto-forwards 500 old messages into Queue.<br>- Sequential, smooth consumption. Zero messages lost. | [ ] |

---

## 2. Infrastructure Exception Handling

| TC ID | Scenario & Technical Requirement | Steps to Execute (Using Tools) | Expected Result (SLA) | Status |
|---|---|---|---|---|
| TC_EXC_01 | **Meta API Timeout**<br>Lost connection to Facebook/Instagram. | Configure firewall to drop outgoing packets to Meta IP, or mock API with >10s delay. | - Bot logs timeout, queues message into internal Retry Queue.<br>- Doesn't block processing for other users. | [ ] |
| TC_EXC_02 | **Logistics API Fallback**<br>3rd-party delivery service (e.g. GHN/GHTK) is down. | Send barcode query, but mock 3rd-party API to return HTTP 500. | - Fallback logic activated.<br>- Friendly bot reply: "Logistics system maintenance in progress, please check later". | [ ] |
| TC_EXC_03 | **Recover from Database connection loss**<br>Avoid cascading failure. | 1. Stop MySQL service.<br>2. Execute chat query on Bot.<br>3. Restart MySQL and test. | - During downtime: Bot replies "System busy".<br>- Upon restart: Auto-reconnects, strictly resumes Read/Write without restart required. | [ ] |
| TC_EXC_04 | **Recover from Redis connection loss**<br>Temporary caching outage. | 1. Stop Redis Cache service.<br>2. Access Bot configurations. | - Fallback to Read-Through directly from MySQL.<br>- System logs High severity Alert internally. | [ ] |

---

## 3. Security Testing

| TC ID | Scenario & Technical Requirement | Steps to Execute (Using Tools) | Expected Result (SLA) | Status |
|---|---|---|---|---|
| TC_SEC_01 | **Bypass API Key Auth**<br>Protect from unauthorized endpoints. | Use Postman to trigger an Order Create endpoint without a valid `x-api-key` header. | - Blocked instantly by Middleware.<br>- Precise HTTP 401 Unauthorized return. | [ ] |
| TC_SEC_02 | **SQL Injection Prevention**<br>Stop database theft/drop. | Input a malicious payload into the 'Barcode' field: `' OR 1=1; DROP TABLE users;--` | - Input is sanitized to standard string.<br>- Failed syntax blocked, throws 400 Bad Request. DB inherently intact. | [ ] |
| TC_SEC_03 | **Forge wxapp_id (Tenant)**<br>Prevent viewing adversary competitor shops. | Send Auth Token of a user in `wxapp_id = 1`, but modify payload requesting data for `wxapp_id = 2`. | - Token to parameter binding verification mismatch.<br>- Emits strict HTTP 403 Forbidden. | [ ] |
| TC_SEC_04 | **Rate Limit (100 req/min)**<br>DDOS Mitigation. | Script 110 identical simultaneous requests within 60 seconds against a specific API. | - System increments hits to 100.<br>- From request 101 within that minute: HTTP 429 Too Many Requests. | [ ] |

---

## 4. Deliverables Report Template

### 4.1 Performance Benchmark Data
- **Load Test Tool:** ...................................... (e.g., Apache Jmeter 5.6)
- **Environment Details:** ................................................. (e.g., Staging Server, 4 Core CPU, 8GB RAM)
- **Executor:** ......................................
- **Results:**
  - Maximum concurrent limits tested: ......... req/s (RPS)
  - Output message queue logged: ......... messages
  - Total HTTP 50x errors yielded under stress: .........
  - Monitored Redis Cache Hit Ratio: ......... %
- **Conclusion/Tuning required:** ......................................

### 4.2 Security Checklist Report
- **Identified Vulnerabilities Check:**
  - [ ] Susceptible to Cross-Tenant parameter tampering. (Details: ......)
  - [ ] API Endpoints lack Middleware Key validation. (Details: ......)
  - [ ] Missing global IP or User Rate Limiting. (Details: ......)
- **SQL Injection Safety:** [ PASS ✅ ] Libraries properly sanitize and parameterize all input strings.
- **JWT / Auth Validity:** [ PASS ✅ ] Blanket block on all anonymous or spoofed identifiers.
