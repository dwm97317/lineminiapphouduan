<?php
return [
  [    
      'name'=>'数据中心',
      'parent' => 'page',
      'icon'=>'mdi-gauge',
      'url_' => '/web/page/data',
      'url' => urlCreate('/web/page/data'),
  ],
  [
      'name'=>'我的包裹',
      'parent' => 'package',
      'icon'=>'mdi mdi-grid-large',
      'url' => '',
      'url_' => '',
      'child'=>[
        //  [
        //     'name' =>'申请预报',
        //     'url_' => '/web/package/yubao',
        //     'url' => urlCreate('/web/package/yubao'),
        //     'icon' => ''
        //  ],
         [
            'name' =>'申请预报',
            'url_' => '/web/package/packreport',
            'url' => urlCreate('/web/package/packreport'),
            'icon' => ''
         ],
         [
            'name' =>'我的包裹',
            'url_' => '/web/package/mypackage',
            'url' => urlCreate('/web/package/mypackage'),
            'icon' => ''
         ],
        //  [
        //     'name' =>'未入库',
        //     'url_' => '/web/package/index',
        //     'url' => urlCreate('/web/package/index'),
        //     'icon' => ''
        //  ],
        //  [
        //     'name' =>'已入库',
        //     'url_' => '/web/package/inpackage',
        //     'url' => urlCreate('/web/package/inpackage'),
        //     'icon' => ''
        //  ],
         [
            'name' =>'包裹认领',
            'url_' => '/web/package/waitrl',
            'url' => urlCreate('/web/package/waitrl'),
            'icon' => ''
         ]
       ],
  ],
  [
      'name'=>'我的订单',
      'parent' => 'order',
      'icon'=>'mdi-vector-arrange-above',
      'url' => '',
      'url_' => '',
      'child'=>[
          [
            'name' =>'新建订单',
            'url_' => '/web/package/gotocreateorder',
            'url' => urlCreate('/web/package/gotocreateorder'),
            'icon' => ''
         ],
         [
            'name' =>'草稿订单',
            'url_' => '/web/package/draft',
            'url' => urlCreate('/web/package/draft'),
            'icon' => ''
         ],
         [
            'name' =>'待发货单',
            'url_' => '/web/package/orderno',
            'url' => urlCreate('/web/package/orderno'),
            'icon' => ''
         ],
         [
            'name' =>'已发货单',
            'url_' => '/web/package/orderyes',
            'url' => urlCreate('/web/package/orderyes'),
            'icon' => ''
         ],
         [
            'name' =>'已到货单',
            'url_' => '/web/package/sended',
            'url' => urlCreate('/web/package/sended'),
            'icon' => ''
         ],
         [
            'name' =>'已完成单',
            'url_' => '/web/package/complete',
            'url' => urlCreate('/web/package/complete'),
            'icon' => ''
         ],
         [
            'name' =>'全部订单',
            'url_' => '/web/package/allorder',
            'url' => urlCreate('/web/package/allorder'),
            'icon' => ''
         ]
       ],
  ],
  [
      'name'=>'个人信息',
      'parent' => 'user',
      'icon'=>'mdi mdi-gauge',
      'url_' => '',
      'url' => urlCreate('/web/user/person'),
      'child'=>[
         [
            'name' =>'个人资料',
            'url_' => '/web/user/person',
            'url' => urlCreate('/web/user/person'),
         ],
         [
            'name' =>'会员等级',
            'url_' => '/web/user/grade',
            'url' => urlCreate('/web/user/grade'),
         ],
         [
            'name' =>'充值记录',
            'url_' => '/web/user/recharge',
            'url' => urlCreate('/web/user/recharge'),
         ],
         [
            'name' =>'余额明细',
            'url_' => '/web/user/balance',
            'url' => urlCreate('/web/user/balance'),
         ],
         [
            'name' =>'修改密码',
            'url_' => '/web/user/forget',
            'url' => urlCreate('/web/user/forget'),
         ],
         [
            'name' =>'收件地址',
            'url_' => '/web/user/address',
            'url' => urlCreate('/web/user/address'),
         ],
         [
            'name' =>'寄件地址',
            'url_' => '/web/user/jaddress',
            'url' => urlCreate('/web/user/jaddress'),
         ]
       ],
  ],
  [
      'name'=>'使用指南',
      'parent' => 'op',
      'icon'=>'mdi mdi-compass-outline',
      'url_' => '',
      'url' => '',
      'child'=>[
         [
            'name' =>'仓库列表',
            'url_' => '/web/shop/lists',
            'url' => urlCreate('/web/shop/lists'),
            'icon' => ''
         ],
         [
            'name' =>'自提网点',
            'url_' => '/web/shop/pickuppoint',
            'url' => urlCreate('/web/shop/pickuppoint'),
            'icon' => ''
         ],
         [
            'name' =>'优惠券',
            'url_' => '/web/user/usercoupon',
            'url' => urlCreate('/web/user/usercoupon'),
            'icon' => ''
         ],
         [
            'name' =>'价格查询',
            'url_' => '/web/package/price',
            'url' => urlCreate('/web/package/price'),
            'icon' => ''
         ],
         [
            'name' =>'轨迹查询',
            'url_' => '/web/package/trajectory',
            'url' => urlCreate('/web/package/trajectory'),
            'icon' => ''
         ],
         [
            'name' =>'常见问题',
            'url_' => '/web/page/problem',
            'url' => urlCreate('/web/page/problem'),
            'icon' => ''
         ],
         [
            'name' =>'站内消息',
            'url_' => '/web/page/message',
            'url' => urlCreate('/web/page/message'),
            'icon' => ''
         ],
         [
            'name' =>'联系我们',
            'url_' => '/web/page/contact',
            'url' => urlCreate('/web/page/contact'),
            'icon' => ''
         ]
       ],
  ]
];
?>