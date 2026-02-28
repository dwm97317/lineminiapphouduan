<?php
/**
 * 后台菜单配置
 *    'home' => [
 *       'name' => '首页',                // 菜单名称
 *       'icon' => 'icon-home',          // 图标 (class)
 *       'index' => 'index/index',         // 链接
 *     ],
 */
return [
    'index' => [
        'name' => '总览',
        'icon' => 'icon-home',
        'index' => 'index/index',
    ],
    'package' => [
      'name' => '包裹管理',
      'icon' => 'icon-miaosha',
      'index' => 'package.index/index',
      'submenu' => [
          [
              'name' => '后台录入',
              'index' => 'package.index/add',
          ],
          [
              'name' => '一票多件录入',
              'index' => 'package.newpack/addpackage',
          ],
          [
              'name' => '代客预报',
              'index' => 'package.index/adminreport',
          ],
          [
              'name' => '扫码入库',
              'index' => 'package.index/scan',
          ],
          [
              'name' => '扫码出库',
              'index' => 'package.index/scanout',
          ],
          [
              'name' => '预报包裹',
              'index' => 'package.report/index',
          ],
           [
              'name' => '待认领',
              'index' => 'package.index/nouser',
          ],
          
          [
              'name' => '全部包裹',
              'index' => 'package.index/index',
          ],
          [
              'name' => '待打包',
              'index' => 'package.index/uninpack',
          ],
          [
              'name' => '预约件',
              'index' => 'package.index/appointment',
          ],
          [
              'name' => '问题件',
              'index' => 'package.index/errors',
          ],
          [
              'name' => '包裹回收站',
              'index' => 'package.index/deletepack',
          ],
         
      ]
   ],
   'tr_order' => [
        'name' => '集运订单',
        'icon' => 'icon-order',
        'index' => 'tr_order/all_list',
        'submenu' => [
            [
                'name' => '全部订单',
                'index' => 'tr_order/all_list',
            ],
            [
                'name' => '待查验',
                'index' => 'tr_order/verify_list',
            ],
            [
                'name' => '待发货',
                'index' => 'tr_order/payed_list',
            ],
            [
                'name' => '已发货',
                'index' => 'tr_order/sending',
            ],
            [
                'name' => '已到货',
                'index' => 'tr_order/sended',
            ],
            [
                'name' => '已完成',
                'index' => 'tr_order/complete',
            ],
            [
                'name' => '未支付',
                'index' => 'tr_order/pay_list',
            ],
            [
                'name' => '问题件',
                'index' => 'tr_order/cancel_list',
            ],
            [
                'name' => '用户评价',
                'index' => 'tr_order/comment',
            ],
            [
                'name' => '快速打包件',
                'index' => 'tr_order/quicklypack',
            ],
           
        ]
    ],
    'user' => [
        'name' => '用户管理',
        'icon' => 'icon-user',
        'index' => 'user/index',
        'submenu' => [
            [
                'name' => '用户列表',
                'index' => 'user/index',
            ],
            [
                'name' => '唛头列表',
                'index' => 'user/marklist',
            ],
            [
                'name' => '用户地址',
                'index' => 'user/address',
                'uris' => [
                            'user/address',
                            'user.address/add',
                            'user.address/edit',
                            'user.address/delete',
                        ]
            ],
            [
                'name' => '会员等级',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '等级管理',
                        'index' => 'user.grade/index',
                        'uris' => [
                            'user.grade/index',
                            'user.grade/add',
                            'user.grade/edit',
                            'user.grade/delete',
                        ]
                    ],
                ]
            ],
            [
                'name' => '余额记录',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '充值记录',
                        'index' => 'user.recharge/order',
                    ],
                    [
                        'name' => '余额明细',
                        'index' => 'user.balance/log',
                    ],
                ]
            ],
            [
                'name' => '折扣列表',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '折扣记录',
                        'index' => 'user/discountlist',
                        'uris' => [
                            'user/discountlist',
                            'user/editdiscount',
                            'user/deletediscount',
                        ]
                    ]
                ]
            ],
        ]
    ],
    'shop' => [
        'name' => '仓库管理',
        'icon' => 'icon-shop',
        'index' => 'shop/index',
        'submenu' => [
            [
                'name' => '仓库管理',

                'index' => 'shop/index',
                'submenu' => [
                    [
                        'name' => '仓库列表',
                        'index' => 'shop/index',
                        'uris' => [
                            'shop/index',
                            'shop/add',
                            'shop/edit',
                        ]
                    ],
                    [
                        'name' => '员工管理',
                        'index' => 'shop.clerk/index',
                        'uris' => [
                            'shop.clerk/index',
                            'shop.clerk/add',
                            'shop.clerk/edit',
                        ]
                    ],
                    [
                        'name' => '自提点管理',
                        'index' => 'shop.address/index'
                    ],
                ]
            ],
            [
                'name' => '加盟管理',
                'index' => 'shop/shelf',
                'active' => true,
                'submenu' => [
                        [
                        'name' => '加盟申请',
                        'index' => 'shop.apply/index',
                            'uris' => [
                                'shop.apply/index',
                                'shop.apply/add',
                                'shop.apply/edit',
                            ]
                        ],
                        [
                        'name' => '提现申请',
                        'index' => 'shop.withdraw/index',
                            'uris' => [
                                'shop.withdraw/index',
                                'shop.withdraw/add',
                                'shop.withdraw/edit',
                            ]
                        ],
                        [
                        'name' => '结算记录',
                        'index' => 'shop.capital/index',
                            'uris' => [
                                'shop.capital/index',
                                'shop.capital/add',
                                'shop.capital/edit',
                            ]
                        ],
                        [
                        'name' => '加盟设置',
                        'index' => 'shop.setting/index',
                            'uris' => [
                                'shop.setting/index',
                                'shop.setting/add',
                                'shop.setting/edit',
                            ]
                        ],
                        [
                        'name' => '路线分成',
                        'index' => 'shop.bonus/index',
                            'uris' => [
                                'shop.bonus/index',
                                'shop.bonus/add',
                                'shop.bonus/edit',
                            ]
                        ],
                        [
                        'name' => '服务分成',
                        'index' => 'shop.servicebonus/index',
                            'uris' => [
                                'shop.servicebonus/index',
                                'shop.servicebonus/add',
                                'shop.servicebonus/edit',
                            ]
                        ],
                      ]
                ],
            [
                'name' => '货架管理',
                'index' => 'shop/shelf',
                'active' => true,
                'submenu' => [
                        [
                            'name' => '货架数据',
                            'index' => 'shop.shelf/index',
                            
                        ],
                        [
                            'name' => '货位数据',
                            'index' => 'shop.shelf/datashelfunit',
                  
                        ],
                      ]
            ]
        ]
    ],
    'batch' => [
        'name' => '批次管理',
        'icon' => 'icon-wenzhang',
        'index' => 'batch/index',
        'submenu' => [
            [
                'name' => '批次列表',
                'active' => true,
                'index' => 'batch/index',
                'submenu' => [
                    [
                        'name' => '待发货',
                        'index' => 'batch/index',
                    ],
                    [
                        'name' => '运送中',
                        'index' => 'batch/moving',
                    ],
                    [
                        'name' => '已到达',
                        'index' => 'batch/reached',
                    ],
                ]
            ],
            [
                'name' => '批次设置',
                'index' => 'batch/setting',
            ],
            [
                'name' => '批次物流模板',
                'index' => 'batch/batchtemplate',
            ],
        ]
    ],
    'content' => [
        'name' => '内容管理',
        'icon' => 'icon-wenzhang',
        'index' => 'content.article/index',
        'submenu' => [
            [
                'name' => '文章管理',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '文章列表',
                        'index' => 'content.article/index',
                        'uris' => [
                            'content.article/index',
                            'content.article/add',
                            'content.article/edit',
                        ]
                    ],
                    [
                        'name' => '文章分类',
                        'index' => 'content.article.category/index',
                        'uris' => [
                            'content.article.category/index',
                            'content.article.category/add',
                            'content.article.category/edit',
                        ]
                    ],
                ]
            ],
            [
                'name' => '文件库管理',
                'submenu' => [
                    [
                        'name' => '文件分组',
                        'index' => 'content.files.group/index',
                        'uris' => [
                            'content.files.group/index',
                            'content.files.group/add',
                            'content.files.group/edit',
                        ]
                    ],
                    [
                        'name' => '文件列表',
                        'index' => 'content.files/index'
                    ],
                    [
                        'name' => '回收站',
                        'index' => 'content.files/recycle',
                    ],
                ]
            ],
        ]
    ],
    'market' => [
        'name' => '营销管理',
        'icon' => 'icon-marketing',
        'index' => 'market.coupon/index',
        'submenu' => [
            [
                'name' => '优惠券',
//                'active' => true,
                'submenu' => [
                    [
                        'name' => '优惠券列表',
                        'index' => 'market.coupon/index',
                        'uris' => [
                            'market.coupon/index',
                            'market.coupon/add',
                            'market.coupon/edit',
                        ]
                    ],
                    [
                        'name' => '领取记录',
                        'index' => 'market.coupon/receive'
                    ],
                ]
            ],
            [
                'name' => '用户充值',
                'submenu' => [
                    [
                        'name' => '充值套餐',
                        'index' => 'market.recharge.plan/index',
                        'uris' => [
                            'market.recharge.plan/index',
                            'market.recharge.plan/add',
                            'market.recharge.plan/edit',
                        ]
                    ],
                    [
                        'name' => '充值设置',
                        'index' => 'market.recharge/setting'
                    ],
                ]
            ],
            [
                'name' => '积分管理',
                'submenu' => [
                    [
                        'name' => '积分设置',
                        'index' => 'market.points/setting'
                    ],
                    [
                        'name' => '积分明细',
                        'index' => 'market.points/log'
                    ],
                ]
            ],
            [
                'name' => '营销设置',
                'index' => 'market.marketing/index',
                'uris' => [
                    'market.marketing/index',
                ]
            ],
            [
                'name' => '消息推送',
                'submenu' => [
                    [
                        'name' => '发送消息',
                        'index' => 'market.push/send',
                    ],
                    [
                        'name' => '活跃用户',
                        'index' => 'market.push/user',
                    ],
                    [
                        'name' => '站内信',
                        'index' => 'market.push/sendsms'
                    ],
                    [
                        'name' => '邮件通知',
                        'index' => 'market.push/senduseremail'
                    ],
                ]
            ],
        ],
    ],
    'statistics' => [
        'name' => '数据统计',
        'icon' => 'icon-qushitu',
        'index' => 'statistics.data/index',
        'submenu' => [
            [
                'name' => '数据统计',
                'index' => 'statistics.data/index',
            ],
            [
                'name' => '国家统计',
                'index' => 'statistics.data/country',
            ],
            [
                'name' => '类目统计',
                'index' => 'statistics.data/category',
            ],
            [
                'name' => '渠道统计',
                'index' => 'statistics.data/ditch',
            ],
            [
                'name' => '数据大屏',
                'index' => 'statistics.data/datascreen',
            ], 
        ]
    ],
    'wxapp' => [
        'name' => '客户端',
        'icon' => 'icon-wxapp',
        'color' => '#36b313',
        'index' => 'wxapp/setting',
        'submenu' => [
            [
                'name' => '小程序设置',
                'index' => 'wxapp/setting',
            ],
            [
                'name' => 'PC端设置',
                'index' => 'wxapp/web',
            ],
            [
                'name' => 'H5端设置',
                'index' => 'wxapp/h5',
            ],
            [
                'name' => '语言设置',
                'index' => 'wxapp/lang',
            ],
            [
                'name' => '页面链接',
                'index' => 'wxapp.page/links'
            ],
            [
                'name' => '订阅消息',
                'index' => 'wxapp.submsg/index',
                'uris' => [
                    'wxapp.submsg/index',
                ]
            ],
            [
                'name' => '帮助中心',
                'index' => 'wxapp.help/index',
                'uris' => [
                    'wxapp.help/index',
                    'wxapp.help/add',
                    'wxapp.help/edit'
                ]
            ],
        ],
    ],
    'apps' => [
        'name' => '应用中心',
        'icon' => 'icon-application',
        'is_svg' => true,   // 多色图标
        'index' => 'apps.dealer.apply/index',
        'submenu' => [
            [
                'name' => '分销中心',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '入驻申请',
                        'index' => 'apps.dealer.apply/index',
                    ],
                    [
                        'name' => '分销商用户',
                        'index' => 'apps.dealer.user/index',
                        'uris' => [
                            'apps.dealer.user/index',
                            'apps.dealer.user/edit',
                            'apps.dealer.user/fans',
                        ]
                    ],
                    [
                        'name' => '分销订单',
                        'index' => 'apps.dealer.order/index',
                    ],
                    [
                        'name' => '提现申请',
                        'index' => 'apps.dealer.withdraw/index',
                    ],
                    [
                        'name' => '分销等级',
                        'index' => 'apps.dealer.rating/index',
                    ],
                    [
                        'name' => '分销设置',
                        'index' => 'apps.dealer.setting/index',
                    ],
                    [
                        'name' => '分销海报',
                        'index' => 'apps.dealer.setting/qrcode',
                    ],
                ]
            ],
            [
                'name' => '拼团管理',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '拼团订单',
                        'index' => 'apps.sharing.order/index',
                        'uris' => [
                            'apps.sharing.order/index',
                            'apps.sharing.order/add',
                            'apps.sharing.order/edit',
                            'apps.sharing.order/changestatus',
                        ]
                    ],
                    [
                        'name' => '团长列表',
                        'index' => 'apps.sharing.verify/list',
                        'uris' => [
                            'apps.sharing.verify/list',
                            'apps.sharing.verify/add',
                            'apps.sharing.verify/edit',
                        ]
                    ],
                    [
                        'name' => '团长审核',
                        'index' => 'apps.sharing.verify/index',
                        'uris' => [
                            'apps.sharing.verify/index',
                            'apps.sharing.verify/add',
                            'apps.sharing.verify/edit',
                        ]
                    ],
                    
                    
                    [
                        'name' => '拼团设置',
                        'index' => 'apps.sharing.setting/basic'
                    ]
                ]
            ],
        ]
    ],
    'store' => [
        'name' => '管理员',
        'icon' => 'icon-shangcheng',
        'index' => 'store.user/index',
        'submenu' => [
            [
                'name' => '管理员列表',
                'index' => 'store.user/index',
                'uris' => [
                    'store.user/index',
                    'store.user/add',
                    'store.user/edit',
                    'store.user/delete',
                ],
            ],
            [
                'name' => '角色管理',
                'index' => 'store.role/index',
                'uris' => [
                    'store.role/index',
                    'store.role/add',
                    'store.role/edit',
                    'store.role/delete',
                ],
            ],
        ]
    ],
    'setting' => [
        'name' => '设置',
        'icon' => 'icon-setting',
        'index' => 'setting/store',
        'submenu' => [
            [
                'name' => '系统设置',
                'index' => 'setting/store',
            ],
            [
                'name' => '自定义中心',
                'index' => 'setting/stylecenter',
                'submenu' => [
                    [
                        'name' => '后台样式设置',
                        'index' => 'setting/stylecenter',
                    ],
                    [
                        'name' => '用户端设置',
                        'index' => 'setting/userclient'
                    ],
                    [
                        'name' => '仓管端设置',
                        'index' => 'setting/keeper'
                    ],
                    [
                        'name' => '电脑端设置',
                        'index' => 'setting/adminstyle'
                    ]
                ]
            ],
            [
                'name' => '支付设置',
                'index' => 'setting/paytype',
            ],
            [
                'name' => 'LINE 配置',
                'index' => 'setting.line_config/index',
                'uris' => [
                    'setting.line_config/index',
                ],
            ],
            [
                'name' => '小程序导航',
                'index' => 'setting.nav/index',
            ],
            [
                'name' => '汇款管理',
                'submenu' => [
                    [
                        'name' => '汇款凭证',
                        'index' => 'setting.certificate/index',
                    ],
                    [
                        'name' => '汇款账号',
                        'index' => 'setting.bank/index'
                    ]
                ]
            ],
            [
              'name' => '集运线路',
              'index' => 'setting.line/index',
              'uris' => [
                  'setting.line/index',
                  'setting.line/add',
                  'setting.line/edit',
              ],
            ],
            [
              'name' => '增值服务',
              'index' => 'setting.addservice/index',
              'uris' => [
                  'setting.addservice/index',
                  'setting.addservice/add',
                  'setting.addservice/edit',
              ],
            ],
            [
                'name' => '渠道中心',
                'index' => 'setting.ditch/index',
                'uris' => [
                    'setting.ditch/index',
                    'setting.ditch/add',
                    'setting.ditch/edit',
                ],
            ],
            [
                'name' => '物流公司',
                'index' => 'setting.express/index',
                'uris' => [
                    'setting.express/index',
                    'setting.express/add',
                    'setting.express/edit',
                ],
            ],
            [
                'name' => '面单配置',
                'index' => 'setting.waybill_config/index',
                'uris' => [
                    'setting.waybill_config/index',
                    'setting.waybill_config/getConfig',
                    'setting.waybill_config/saveConfig',
                    'setting.waybill_config/getFieldList',
                    'setting.waybill_config/resetConfig',
                ],
            ],
            [
                'name' => '快递标签配置',
                'index' => 'setting.express_service/index',
                'uris' => [
                    'setting.express_service/index',
                    'setting.express_service/add',
                    'setting.express_service/edit',
                    'setting.express_service/delete',
                ],
            ],
            [
                'name' => '物流模板',
                'index' => 'setting/notice'
            ],
            [
              'name' => '打包服务',
              'index' => 'setting.package/index',
              'uris' => [
                  'setting.package/index',
                  'setting.package/add',
                  'setting.package/edit',
              ],
            ],
            [
              'name' => '国家支持',
              'index' => 'setting.country/index',
              'uris' => [
                  'setting.country/index',
                  'setting.country/add',
                  'setting.country/edit',
              ],
            ],
            [
              'name' => '类目管理',
              'index' => 'setting.category/index',
              'uris' => [
                  'goods.category/index',
                  'goods.category/add',
                  'goods.category/edit',
              ],
           ],
           [
            'name' => '轮播管理',
            'index' => 'setting.banner/index',
            'uris' => [
                'setting.banner/index',
                'setting.banner/add',
                'setting.banner/edit',
            ],
            ],
            [
                'name' => '短信通知',
                'index' => 'setting/sms',
                'submenu' => [
                    [
                        'name' => '短信设置',
                        'index' => 'setting/sms'
                    ],
                    [
                        'name' => '短信前缀',
                        'index' => 'setting.sms/index',
                        'uris' => [
                            'setting.sms/index',
                            'setting.sms/add',
                            'setting.sms/edit'
                        ]
                    ],
                    
                ]
            ],
            [
                'name' => '智能AI识别',
                'index' => 'setting/aiidentify'
            ],
            [
                'name' => '邮件通知',
                'index' => 'setting/email'
            ],
           
                [
                    'name' => '模板消息',
                    'index' => 'setting/tplmsg',
                    'uris' => [
                        'setting/tplmsg',
                        'setting.help/tplmsg'
            
                    ],
                ],
            [
                'name' => '上传设置',
                'index' => 'setting/storage',
            ],
            [
                'name' => '小票打印机',
                'submenu' => [
                    // [
                    //     'name' => '打印机管理',
                    //     'index' => 'setting.printer/index',
                    //     'uris' => [
                    //         'setting.printer/index',
                    //         'setting.printer/add',
                    //         'setting.printer/edit'
                    //     ]
                    // ],
                    [
                        'name' => '打印设置',
                        'index' => 'setting/printer'
                    ]
                ]
            ],
            [
                'name' => '其他',
                'submenu' => [
                    [
                        'name' => '清理缓存',
                        'index' => 'setting.cache/clear'
                    ]
                ]
            ]
        ],
    ],
  'tools' => [
        'name' => '工具箱',
        'icon' => 'icon-shangcheng',
        'index' => 'tools/index',
        'submenu' => [
            [
                'name' => '常用工具',
                'index' => 'tools/index',
            ],
            [
                'name' => '运费查询',
                'index' => 'tools/seachfree',
            ],
            [
                'name' => '更新日志',
                'index' => 'tools/updatelog',
            ],
            [
                'name' => 'API接口',
                'index' => 'tools/apipost',
            ],
        ]
    ]
];
