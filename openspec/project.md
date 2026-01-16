# Project Context

## Purpose
This is the backend for the **Zalo Consolidation System (集运系统 SAAS)**. It manages cross-border logistics, package consolidation, order processing, and user management for a logistics service. The system supports multiple user ends including a Web Portal, H5 Mobile App, and WeChat Mini Programs.

## Tech Stack
-   **Backend Framework**: ThinkPHP 5.0 (PHP >= 5.4.0)
-   **Database**: MySQL (`zalo_zhuanyun`, prefix `yoshop_`)
-   **Frontend (H5)**: Vue.js (Compiled in `web/html5`)
-   **Mini Program**: WeChat Mini Program (Backend support in `api/controller/Wxapp`)
-   **Server**: Nginx/Apache (Standard PHP environment)

## Project Conventions

### Code Style
-   **Naming**:
    -   Classes: PascalCase (e.g., `OrderModel`)
    -   Methods: camelCase (e.g., `getUserInfo`)
    -   Variables/Properties: camelCase or snake_case
    -   Database Tables/Fields: snake_case (e.g., `yoshop_order`, `user_id`)
-   **Formatting**: Follows standard PHP PSR conventions compatible with ThinkPHP 5.
-   **Controller/Model**:
    -   Controllers inherit from `Controller` base class.
    -   Models inherit from `think\Model` or custom base models.
    -   Namespace: `app\{module}\controller` or `app\{module}\model`.

### Architecture Patterns
-   **Pattern**: MVC (Model-View-Controller).
-   **Modules**:
    -   `admin`: Backend management system.
    -   `api`: RESTful API for frontend (Mini Program/H5).
    -   `store`: Warehouse and merchant management.
    -   `web`: PC Web portal.
-   **API Design**:
    -   Response: JSON.
    -   Success: `renderSuccess($data, $msg)`.
    -   Error: `renderError($msg)`.
    -   Authentication: Token-based (Custom implementation, valid for 30 days).

### Testing Strategy
-   **Environment**: `app_debug` is currently set to `true`.
-   **Logging**: Custom logging via `log_write` to `source/runtime/log/`.

### Git Workflow
-   (Standard Git workflow implies)

## Domain Context
-   **Core Entities**:
    -   **Consolidation Order (集运订单)**: Type 30 usually.
    -   **Package (包裹)**: Items received at the warehouse.
    -   **Line (路线)**: Logistics routes with specific pricing rules.
-   **Key IDs**:
    -   `wxapp_id`: Default `10001` (Tenant/App ID).
-   **Order Number Generation**: Complex rules supporting timestamps, user ID, warehouse codes, etc.

## Important Constraints
-   **Framework Version**: STRICTLY ThinkPHP 5.0.*. Do not use syntax or features from newer Laravel/Symfony versions unless polyfilled.
-   **Database**: Do not modify `database.php` config unless migrating environments.
-   **Security**: Use `yoshop_hash` for passwords and internal `encrypt` functions for data.
-   **Mini Program**:
    -   Authentication requires handling `code` -> `openid` exchange.
    -   Specific logic for forced mobile binding (`isForceBindMpweixin`).

## External Dependencies
-   **Storage**: Qiniu Kodo, Aliyun OSS, Tencent Cloud COS.
-   **Messaging**: PHPMailer.
-   **Utils**: PHP Barcode Generator, Grafika (Image processing).
