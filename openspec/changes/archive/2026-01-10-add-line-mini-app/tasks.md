## 1. Implementation
- [x] 1.1 Create `app\api\model\LineApp` model class
- [x] 1.2 Create `app\api\controller\LineApp` controller with `base` method (returns LIFF config)
- [x] 1.3 Add `loginMpLine` to `app\api\controller\Passport`
- [x] 1.4 Add `loginMpLine` to `app\api\service\passport\Login`
- [x] 1.5 implement LINE ID Token verification (using cURL or library)
- [x] 1.6 Update `app\common\service\Basics` or relevant User models if schema changes needed
- [x] 1.7 Create `app\store\controller\setting\LineConfig` for admin configuration
- [x] 1.8 Create comprehensive view file for LINE configuration page (LIFF ID, Scopes, etc.)
- [x] 1.9 Update `getLineChannelId()` and `Setting` model to handle full LINE config
- [x] 1.10 Add `LINE_CONFIG` to `Setting` enum and default data
- [x] 1.11 Implement **LINE Messaging API** settings in backend and admin view
- [x] 1.12 Implement **LINE Pay** configuration settings in backend and admin view
- [x] 1.13 Add Bot Link (Official Account) feature configuration





## 2. Validation
- [x] 2.1 Verify `GET /api/line_app/base` returns configuration
- [x] 2.2 Verify `POST /api/passport/loginMpLine` creates/logs in user
- [x] 2.3 Verify admin can save all LINE LIFF settings in backend
- [x] 2.4 Verify LINE config is correctly retrieved during login



