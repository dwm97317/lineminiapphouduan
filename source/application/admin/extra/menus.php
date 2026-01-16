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
    'store' => [
        'name' => '集运小程序',
        'icon' => 'icon-shangcheng',
        'submenu' => [
            [
                'name' => '商家列表',
                'index' => 'store/index',
                'uris' => [
                    'store/index',
                    'store/add',
                ]
            ],
            [
                'name' => '回收站',
                'index' => 'store/recycle'
            ],
            [
                'name' => '权限管理',
                'index' => 'store.access/index'
            ]
        ],
    ],
    'tools' => [
        'name' => '全局管理',
        'icon' => 'icon-shangcheng',
        'submenu' => [
            [
                'name' => '更新日志',
                'index' => 'tools.index/index',
                'uris' => [
                    'tools/index/index',
                    'tools/index/add',
                ]
            ],
            [
                'name' => 'API接口',
                'index' => 'tools.apipost/index',
                'uris' => [
                    'tools/index/index',
                    'tools/index/add',
                ]
            ],
        ],
    ],
    'setting' => [
        'name' => '系统设置',
        'icon' => 'icon-shezhi',
        'submenu' => [
            [
                'name' => '清理缓存',
                'index' => 'setting.cache/clear'
            ],
            [
                'name' => '环境检测',
                'index' => 'setting.science/index'
            ],
        ],
    ],
];
