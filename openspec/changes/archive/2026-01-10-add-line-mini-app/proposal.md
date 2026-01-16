# Change: Add LINE Mini App Support

## Why
To expand the user base and provide convenient access for users in regions where LINE is popular (e.g., Thailand, Taiwan, Japan), we need to support LINE Mini App integration similar to the existing WeChat Mini Program.

## What Changes
- **Backend API**:
  - Add `app\api\controller\LineApp` for LINE-specific base info.
  - Add `app\api\model\LineApp` for configuration management.
  - Add `loginMpLine` method to `Passport` controller for LINE Login.
  - Add `loginMpLine` method to `Login` service to handle LINE authentication (verify ID Token).
- **Admin Backend**:
  - Add LINE configuration page in store settings (`store/controller/setting/LineConfig.php`)
  - Allow merchants to configure LINE Channel ID, Channel Secret, and LIFF ID.
  - Add **LINE Messaging API** settings for system notifications (入库, 出库, 等).
  - Add **LINE Pay** settings for online payments (Channel ID, Channel Secret).
  - Store LINE config in `yoshop_setting` table (following existing pattern)
- **Database**:
  - Reuse `yoshop_user_oauth` table for LINE openid storage (oauth_type = 'LINE')
  - Store LINE app config in `yoshop_setting` table with key 'line_config'
  - Store LINE messaging/payment config in `yoshop_setting` under 'line_messaging' and 'line_pay'
- **Configuration**:
  - LINE credentials and feature settings stored per merchant (wxapp_id)
  - Configuration accessible via Setting model


## Impact
- **Affected Specs**: 
  - `auth` (New login method)
  - `line-app` (New capability)
  - `admin-settings` (New LINE configuration page)
- **Affected Code**: 
  - `source/application/api/controller/Passport.php`
  - `source/application/api/service/passport/Login.php`
  - `source/application/store/controller/setting/LineConfig.php` (NEW)
  - New files in `source/application/api/controller/LineApp.php` etc.

