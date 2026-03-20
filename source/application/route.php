<?php

// 设限制 URL 兼容模式
\think\Url::root('index.php?s=');

// Route::rule('html5/:any', function () {
//   return view(\think\Url::root . 'html5/index.html');
// });

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

    // ============================================
    // Bot API Routes (FB/IG Bot Integration)
    // ============================================
    
    // Customer verification endpoints
    'api/bot/customer/verify' => ['api.bot.Customer/verify', ['method' => 'GET|POST']],
    'api/bot/customer/info' => ['api.bot.Customer/info', ['method' => 'GET']],
    
    // Package management endpoints
    'api/bot/package/create' => ['api.bot.Package/create', ['method' => 'POST']],
    'api/bot/package/status' => ['api.bot.Package/status', ['method' => 'GET']],
    'api/bot/package/list' => ['api.bot.Package/lists', ['method' => 'GET']],
    
    // Account binding endpoint (from previous implementation)
    'api/v1/account/bind' => ['api.Account/bind', ['method' => 'POST']],
    'api/v1/account/bindings' => ['api.Account/bindings', ['method' => 'GET']],
    'api/v1/account/unbind' => ['api.Account/unbind', ['method' => 'POST']],
    'api/v1/account/verify-customer' => ['api.Account/verifyCustomer', ['method' => 'POST']],

];

