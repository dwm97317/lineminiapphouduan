<?php

// 设限制URL兼容模式
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
    
    // Account management endpoints (Multi-account support)
    'api/v1/account/bind' => ['api.Account/bind', ['method' => 'POST']],
    'api/v1/account/list' => ['api.Account/list', ['method' => 'GET']],
    'api/v1/account/unbind' => ['api.Account/unbind', ['method' => 'POST']],
    'api/v1/account/verify-customer' => ['api.Account/verifyCustomer', ['method' => 'POST']],
    
    // Bot account manager endpoints
    'api/bot/account/list-linked' => ['api.bot.AccountManager/listLinked', ['method' => 'GET']],
    'api/bot/package/waiting-list' => ['api.bot.AccountManager/waitingList', ['method' => 'GET']],
    'api/bot/order/history' => ['api.bot.AccountManager/orderHistory', ['method' => 'GET']],

];

