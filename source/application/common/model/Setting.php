<?php
namespace app\common\model;

use think\Cache;
use app\common\enum\Setting as SettingEnum;
use app\common\enum\DeliveryType as DeliveryTypeEnum;

/**
 * 系统设置模型
 * Class Setting
 * @package app\common\model
 */
class Setting extends BaseModel
{
    protected $name = 'setting';
    protected $createTime = false;

    /**
     * 获取器: 转义数组格式
     * @param $value
     * @return mixed
     */
    public function getValuesAttr($value)
    {
        return json_decode($value, true);
    }

    /**
     * 修改器: 转义成json格式
     * @param $value
     * @return string
     */
    public function setValuesAttr($value)
    {
        return json_encode($value);
    }

    /**
     * 获取指定项设置
     * @param $key
     * @param $wxapp_id
     * @return array
     */
    public static function getItem($key, $wxapp_id = null)
    {
        $data = self::getAll($wxapp_id);
        // dump($data);die;
        return isset($data[$key]) ? $data[$key]['values'] : [];
    }

    /**
     * 获取设置项信息
     * @param $key
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($key)
    {
        return self::get(compact('key'));
    }

    /**
     * 全局缓存: 系统设置
     * @param null $wxapp_id
     * @return array|mixed
     */
    public static function getAll($wxapp_id = null)
    {
        $static = new static;
   
        is_null($wxapp_id) && $wxapp_id = $static::$wxapp_id;
    //   Cache::tag('cache')->set('setting_' . $wxapp_id, '');
        if (!$data = Cache::get('setting_' . $wxapp_id)) {
            $setting = $static::all(compact('wxapp_id'));
            $data = empty($setting) ? [] : array_column(collection($setting)->toArray(), null, 'key');
     
            Cache::tag('cache')->set('setting_' . $wxapp_id, $data);
        }
        return $static->getMergeData($data);
    }

    /**
     * 合并用户设置与默认数据
     * @param $userData
     * @return array
     */
    private function getMergeData($userData)
    {
       
        $defaultData = $this->defaultData();
        
        // 商城设置：配送方式
        if (isset($userData['store']['values']['delivery_type'])) {
            unset($defaultData['store']['values']['delivery_type']);
        }
        return array_merge_multiple($defaultData, $userData);
    }

    /**
     * 默认配置
     * @param null|string $storeName
     * @return array
     */
    public function defaultData($storeName = null)
    {
        return [
            // 商城设置
            'store' => [
                'key' => 'store',
                'describe' => '系统设置',
                'values' => [
                    // 系统名称
                    'name' => $storeName ?: '小思集运',
                    'title' =>"小思集运",
                    'desc' => "让每个包裹都能安全到达",
                    'is_getphone' => 0, //强制授权手机号
                    'checkphone'=> 10, //10是验证邮箱，20是验证手机号
                    'cover_id' => '',
                    'indextitle'=> 10, //默认10，20显示出标题
                    'indextitle_back'=>"",//背景颜色
                    'indextitle_fontcolor'=>"",//背景颜色
                    // 配送方式
                    'delivery_type' => array_keys(DeliveryTypeEnum::data()),
                    'is_categorysearch' => 0,
                    'is_external'=>0, //是否是国外企业注册的小程序
                    'is_phone' => 1,
                    'client' =>[
                        'mode' =>20, //10 只开启H5，20，小程序+H5，    
                    ],
                    'moren'=>[
                        'send_mode'=> 10, //默认10 拼邮模式  20 直邮模式  代用户打包默认的
                        'pack_in_shop' => 10, //默认10 拼邮模式  20 直邮模式  后台录入的
                        'is_zhiyou_pack'=>0, //默认不生成订单  1生成集运单
                        'pack_in_status' => 3, //默认3 待发货 1 待查验  2 待支付  当选择直邮模式后，包裹入库即默认状态
                        'pack_in_pay'=> 0, //默认0 立即发货  1货到付款  2月结
                        'user_pack_in_pay'=>0,//默认0 立即发货  1货到付款  2月结
                    ],
                    'orderno'=>[
                        'default'=>[20,110],
                        'model'=>[
                            10 =>"时间戳1688197248(动态)",
                            20 =>"年月日20230101(固定)",
                            30 =>"(缩)年月日230101(固定)",
                            40 =>"年月日时分秒20230101213030(动态)",
                            50 =>"用户ID(固定)",
                            60 =>"目的地ID(固定)",
                            70 =>"仓库简称(CTO)(固定)",
                            // 80 =>"城市编号(15)",
                            90 =>"自定义字母(XS)(固定)",
                            100 =>"自定序号(001-100)(固定)",
                            110 =>"随机5位数(10000-99999)(动态)",
                            
                        ]// 10 时间
                    ],
                    'createSn' => 10, // 10 常规  20 时间+用户id+country_id
                    'menu_type' => 10,
                    'is_wechatgzh'=>1,
                    'is_newhand' => 1, //新手区时候展示
                    'is_wechat' =>1,
                    'is_wechathao' =>1,
                    'is_kefuemail' => 1,
                    'newhand_mode'=> 10,//10 列表  20 图文
                    'service_name' =>"小思集运客服",
                    'service_phone' =>"18086328550",
                    'wechat_name' => "微信客服小冯",
                    'wechathao' => "f18995802785",
                    'emailname' =>"客服邮箱",
                    'kefuemail' =>"sllowly@sllowly.com",
                    
                    'is_service' => 0, //查询运费时是否启用增值服务项目
                    'service_othername'=>'增值服务',
                    'service_des' => '运输只能够不包破损，建议选择合适的包装方式，可多选，超特大件另议非要。打包后重量会有所增加，实际重量以出货后置！',
                    
                    "height_banner"=> 180,
                    'is_pinglun' => 1,
                    'is_line' => 1,
                    'is_balance'=>1,
                    'is_sitesms'=>1,
                    'is_line_show'=>1,
                    'is_cuppon'=>1,
                    'is_point'=>1,
                    'is_jiyun'=>1,
                    'is_pintuan'=>1,
                    'is_fenxiao'=>1,
                    'is_email'=>1,
                    'is_daifu'=>1,
                    'is_chayan'=>1,
                    'is_about'=>1,
                    'is_address'=>1,
                    'is_problem'=>1,
                    'is_renling'=>1,
                    'is_warehouse'=>1,
                    'is_navigation'=>1,
                    'is_discount'=>1,
                    'is_packagestation'=>1,
                    'is_hotcategory'=>0, // 仓管端是否开启热门分类
                    'is_tuanzhang'=>1,
                    'is_yaoqing'=>1,
                    'is_fyaoqing'=>1,
                    'is_camera'=> 1,
                    'is_setnickname' => 1, //强制修改昵称
                    'is_sort'=>10,
                    'sort_mode'=>10,
                    'link_mode'=>10, //联系人的模式
                    'address_mode'=>10,//10=纯地址，20=地址+UID:ID 30=地址+ID+室
                    "is_change_uid"=>0, //是否更改UID显示为xxx室
                    'is_auto_free' => 1, //0不自动计算费用，1自动计算费用
                    'retention_day'=> 7, //滞留件时效天数，超过这个天数则再次通知用户领取
                    'is_ren_image' =>1, //0 不开启
                    // 快递100
                    'kuaidi100' => [
                        'customer' => '',
                        'key' => '',
                    ],
                    'track17' =>[
                        'key' => '',
                    ],
                    'usercode_mode'=>[
                        'mode' => '30',
                        'is_show'=> '0',
                        '10'=>[
                            'number'=>'5',
                        ],
                        '20'=>[
                            'char'=>'5',
                        ],
                        '30'=>[
                            'char'=>'JY',
                            'number'=>'5',
                        ],
                    ],
                    'weight_mode'=>[
                        'mode'=> '20',
                        'unit'=>'kg',
                        'unit_name'=>'千克',
                        '10'=>[
                            'number'=>'10',
                            'unit'=>'g',
                        ],
                        '20'=>[
                            'number'=>'20',
                            'unit'=>'kg',
                        ],
                        '30'=>[
                            'number'=>'30',
                            'unit'=>'lb',
                        ],
                    ],
                    'size_mode'=>[
                        'mode'=> '10',
                        'unit'=>'CM',
                        'unit_name'=>'厘米',
                        '10'=>[
                            'number'=>'10',
                            'unit'=>'CM',
                        ],
                        '20'=>[
                            'number'=>'20',
                            'unit'=>'IN',
                        ],
                    ],
                    'price_mode'=>[
                        'mode'=> '10',
                        'unit'=>'¥',
                        'unit_name'=>'元',
                        '10'=>[
                            'number'=>'10',
                            'unit'=>'¥',
                            'unit_name'=>'元',
                        ],
                        '20'=>[
                            'number'=>'20',
                            'unit'=>'$',
                            'unit_name'=>'美元',
                        ],
                        '30'=>[
                            'number'=>'30',
                            'unit'=>'C$',
                            'unit_name'=>'加币',
                        ],
                        '40'=>[
                            'number'=>'40',
                            'unit'=>'€',
                            'unit_name'=>'欧元',
                        ],
                        '50'=>[
                            'number'=>'50',
                            'unit'=>'A$',
                            'unit_name'=>'澳元',
                        ],
                        '60'=>[
                            'number'=>'60',
                            'unit'=>'HK$',
                            'unit_name'=>'港币',
                        ],
                    ],
                    'address_setting'=>[
                        'is_tel_code'=>1, 
                        'is_province'=>1,
                        'is_city'=>1,
                        'is_clearancecode'=> 0,
                        'is_identitycard'=>0,
                        'is_region'=>1,
                        'is_detail'=>1,
                        'is_email'=>1,
                        'is_code'=>1,
                        'is_street'=>1,
                        'is_door'=>1,
                        'is_remark'=>1,
                        'remark'=>'',
                    ],
                    'jumpbox'=>[
                        'mode' => 10, //10默认每次都弹  20 只弹一次  30 不弹    
                    ]
                ],
            ],
            //仓管端设置项目
            'keeper' => [
                'key' => 'keeper',
                'describe' => '仓管端设置',
                'values' => [
                    'fahuocang' => [
                        'is_shop'=>1, //是否显示入库仓库
                        'is_shop_force'=>0,
                        'is_user'=>1, //
                        'is_user_force'=>0,
                        'is_shelf' => 1,
                        'is_shelf_force'=>0,
                        'is_category' => 1,
                        'is_category_force'=>0,
                        'is_hot' => 1,
                        'is_hot_force'=>0,
                        'is_weight' => 1,
                        'is_weight_force' => 0,
                        'is_vol' => 1,
                        'is_vol_force' => 0,
                        'is_remark' => 1,
                        'is_adminremark' => 1,
                        'is_adminremark_force' => 0,
                        'is_photo' => 1,
                        'is_photo_force' => 0,
                    ]
                ]
            ],
            'adminstyle' => [
                'key' => 'adminstyle',
                'describe' => '仓管端设置',
                'values' =>[
                    'is_usermark'=>1, //是否开启唛头
                    'is_force_usermark'=>0,//是否唛头必填
                    'is_country'=>1,
                    'is_force_country'=>0,
                    'is_shop'=>1,
                    'is_force_shop'=>0,
                    'is_express'=>1,
                    'is_force_express'=>0,
                    'is_packinfo'=>1, //包裹长宽高等信息
                    'is_force_packinfo'=>0, //包裹长宽高等信息
                    'is_totalvalue'=>1,
                    'is_force_totalvalue'=>0,
                    'is_category'=>1,
                    'is_force_category'=>0,
                    'is_adminremark'=>1,
                    'is_force_adminremark'=>0,
                    'is_packimage'=>1,
                    'is_force_packimage'=>0,
                    'is_shelf'=>1,
                    'is_force_shelf'=>0,
                    'is_line'=>1,
                    'is_force_line'=>0
                ]
            ],
            // 用户端设置
            'userclient' => [
                'key' => 'userclient',
                'describe' => '用户端设置',
                'values' => [
                    'yubao' => [
                        'is_single'=>1,
                        'is_more'=>1,
                        'is_country' => 1,
                        'is_country_force' => 1,
                        'is_shop' => 1,
                        'is_shop_force' => 1,
                        'is_expressname' => 1,
                        'is_expressname_force' => 1,
                        'is_category' => 1,
                        'is_category_force' => 1,
                        'is_price' => 1,
                        'is_price_force' => 1,
                        'is_remark' => 1,
                        'is_remark_force' => 1,
                        'is_images' => 1,
                        'is_images_force' => 1,
                        'is_xieyi' => 1,
                        'is_xieyi_force' => 1,
                        'is_goodslist'=>1,
                        'is_goodslist_force'=>1,
                    ],
                    'userinfo'=>[
                        'is_identification_card'=>1,
                        'is_identification_card_force'=>1,
                        'identification_card'=>'身份证',
                        'identification_card_image'=>"身份证照片",
                        'is_birthday'=>1, //生日
                        'is_birthday_force'=>0,
                        'is_wechat'=>1, //微信号
                        'is_wechat_force'=>0,
                        'is_email'=>1, //邮箱
                        'is_email_force'=>0,
                        'is_mobile'=>1, //手机号
                        'is_mobile_force'=>0,
                    ],
                    'packit'=>[
                        'is_force' => 0, //是否强制弹出完善用户资料
                        'is_waitreceivedmoney'=>0, //是否展示代收款
                        'is_packagestation'=>1,//开启自提点
                        'is_todoor'=>1,//开启送货上门
                    ],
                    'loginsetting'=>[
                        'is_passwordlogin'=>0, //在小程序或公众号模式中是否开启账号密码登录方式;
                        'is_phone'=>0,
                        'is_wxopen'=>0, //是否开启了微信开放平台
                        'is_merge_user'=>0, //默认不合并用户的
                    ],
                    'newuserprocess'=>[
                        'first_title'=>"第一步：复制仓库地址",
                        'first_remark'=>"复制仓库地址后前往购物平台下单，将仓库地址粘贴到购物平台收货地址中。",
                        'second_title'=>"第二步：预报包裹",
                        'second_remark'=>"下单后可以在购物平台获取到快递单号，将快递单号预报系统中",
                        'third_title'=>"第三步：申请打包",
                        'third_remark'=>"包裹到达仓库后，可向仓库申请打包出库。",
                        'fourth_title'=>"第四步：支付订单费用",
                        'fourth_remark'=>"包裹打包完成后，可支付包裹运费费用，仓库即会快速发货。",
                    ],
                    'guide'=>[
                        'is_default'=>1, //使用系统默认的
                        'first_image'=>"",
                        'first_url'=>'',
                        'first_url_type'=>1, //默认1为站内，2为站外
                        
                        'second_image'=>"",
                        'second_url'=>'',
                        'second_url_type'=>1, //默认1为站内，2为站外
                        
                        'third_image'=>"",
                        'third_url'=>'',
                        'third_url_type'=>1, //默认1为站内，2为站外
                    ]
                ]
            ],
            // 交易设置
            'trade' => [
                'key' => 'trade',
                'describe' => '交易设置',
                'values' => [
                    'order' => [
                        'close_days' => '3',
                        'receive_days' => '10',
                        'refund_days' => '7'
                    ],
                    'freight_rule' => '10',
                ]
            ],
            // 语言设置
            'lang' => [
                'key' => 'lang',
                'describe' => '语言设置',
                'values' => [
                    'default' => 'zhHant',
                    'zhHans' => '1', //简体中文
                    'en' => '0', //英文
                    'zhHant' => '0', //繁体
                    'thai'=>0, //泰语
                    'vietnam'=>0, //越南
                ]
            ],
            // 支付设置
            'paytype' => [
                'key' => 'paytype',
                'describe' => '支付设置',
                'values' => [
                    'wechat' => [
                        'name'=>'微信支付',
                        'is_open' => '1',
                        'value'=>20,
                        'icon'=> base_url()."assets/api/images//dzx_img128.png",
                        'platfrom'=>[
                            'MP-WEIXIN'=>1,
                            'H5-WEIXIN'=>1,
                            'H5'=>1,
                            'APP'=>0,
                            'WEB'=>0
                        ] //小程序，公众号，H5，APP,PC
                    ],
                    'balance' =>[
                        'name'=>'余额支付',
                        'is_open' => '1',
                        'value'=>10,
                        'icon'=> base_url()."assets/api/images//dzx_img130.png",
                        'platfrom'=>[
                            'MP-WEIXIN'=>1,
                            'H5-WEIXIN'=>1,
                            'H5'=>1,
                            'APP'=>0,
                            'WEB'=>0
                        ] //小程序，公众号，H5，APP,PC
                    ],
                    'Hantepay'  =>[
                        'name'=> '汉特支付',
                        'is_open' => '0',
                        'value'=>30,
                        'merchant_no' =>'', //商户号
                        'store_no' =>'',   //门店编号
                        'apikey'=>'',
                        'icon'=>base_url()."assets/api/images//dzx_img130.png",
                        'platfrom'=>[
                            'MP-WEIXIN'=>1,
                            'H5-WEIXIN'=>1,
                            'H5'=>1,
                            'APP'=>1,
                            'WEB'=>0
                        ] //小程序，公众号，H5，APP,PC
                    ],
                    'omipay' =>[
                        'name'=> 'O米支付',
                        'is_open' => '0',
                        'value'=>40,
                        'mid' =>'', //商户号
                        'currency' =>"AUD",  //澳元 or CNY
                        'apikey'=>'',
                        'icon'=>base_url()."assets/api/images//dzx_img130.png",
                        'platfrom'=>[
                            'MP-WEIXIN'=>1,
                            'H5-WEIXIN'=>1,
                            'H5'=>1,
                            'APP'=>1,
                            'WEB'=>0
                        ] //小程序，公众号，H5，APP,PC
                    ],
                ]
            ],
            // 上传设置
            'storage' => [
                'key' => 'storage',
                'describe' => '上传设置',
                'values' => [
                    'default' => 'local',
                    'engine' => [
                        'local' => [
                             'domain' => base_url() . 'uploads'
                        ],
                        'qiniu' => [
                            'bucket' => '',
                            'access_key' => '',
                            'secret_key' => '',
                            'domain' => 'http://'
                        ],
                        'aliyun' => [
                            'bucket' => '',
                            'access_key_id' => '',
                            'access_key_secret' => '',
                            'domain' => 'http://'
                        ],
                        'qcloud' => [
                            'bucket' => '',
                            'region' => '',
                            'secret_id' => '',
                            'secret_key' => '',
                            'domain' => 'http://'
                        ],
                        'cloudflare' => [
                            'bucket' => '',
                            'access_key' => '',
                            'secret_key' => '',
                            'account_id' => '',
                            'domain' => 'http://',
                            'accounts' => [],
                            'active_account_id' => '',
                            'auto_switch' => 0
                        ],
                    ]
                ],
            ],
            // 短信通知
            'sms' => [
                'key' => 'sms',
                'describe' => '短信通知',
                'values' => [
                    'default' => 'aliyun',
                    'engine' => [
                        'aliyun' => [
                            'AccessKeyId' => '',
                            'AccessKeySecret' => '',
                            'sign' => '小思集运',
                            'order_pay' => [
                                'is_enable' => '0',
                                'template_code' => '',
                                'accept_phone' => '',
                            ],
                        ],
                    ],
                ],
            ],
             // 邮件通知
            'email' => [
                'key' => 'email',
                'describe' => '邮件通知',
                'values' => [
                    'is_enable' =>1,
                    'setting' => [
                            'Username' => '1835504221@qq.com',
                            'Password' => 'faumrfbbbymscihb',
                            'replyName' => '小思集运',
                            'replyEmail' => '1835504221@qq.com',
                            
                    ],
                    'template' => [
                        'valide' => [
                            'theme' => '邮箱绑定验证',
                            'value' => '您的邮箱验证码是${code}，请注意查收！',
                        ],
                        'status' => [
                            'theme' => '物流变更通知',
                            'value' => '您的包裹${code}物流状态已变更为${message}，更多信息可通过用户端查询，感谢您的支持，祝您开心每一天！',
                        ],
                    ],
                ],
            ],
            // 模板消息
                'tplMsg' => [
                    'key' => 'tplMsg',
                    'describe' => '模板消息',
                    'values' => [
                        'is_oldtps'=> 1,
                        //包裹入库通知
                        'payment' => [
                            'is_enable' => '0',
                            'template_id' => '',
                            'keywords' => ['first', 'keyword1', 'keyword2', 'keyword3','remark'],
                        ],
                        //运单状态更新通知
                        'delivery' => [
                            'is_enable' => '0',
                            'template_id' => '',
                            'keywords' => ['first', 'keyword1', 'keyword2', 'keyword3','remark'],
                        ],
                        // 包裹打包申请通知
                        'packageit' => [
                            'is_enable' => '0',
                            'template_id' => '',
                            'keywords' => ['first', 'keyword1', 'keyword2', 'keyword3', 'keyword4','keyword5','remark'],
                        ],
                        //订单支付成功通知
                        'paymessage' => [
                            'is_enable' => '0',
                            'template_id' => '',
                            'keywords' => ['first', 'keyword1', 'keyword2', 'keyword3','remark'],
                        ],
                        //类目消息
                        //包裹出库提醒
                        'outwarehouse' => [
                            'is_enable' => '0',
                            'template_id' => '',
                            'keywords' => ['first', 'keyword1', 'keyword2', 'keyword3','keyword4','keyword5','remark'],
                        ],
                        //订单支付成功提醒
                        'paysuccess' => [
                            'is_enable' => '0',
                            'template_id' => '',
                            'keywords' => ['first', 'keyword1', 'keyword2', 'keyword3','keyword4','keyword5','remark'],
                        ],
                        //包裹入库提醒  √
                        'inwarehouse' => [
                            'is_enable' => '0',
                            'template_id' => '',
                            'keywords' => ['thing8', 'character_string2', 'time6','thing7','thing9'],
                        ],
                        //订单打包完成通知 √
                        'dabaosuccess' => [
                            'is_enable' => '0',
                            'template_id' => '',
                            'keywords' => ['character_string2', 'thing1', 'character_string3', 'character_string4'],
                        ],
                        //出库申请提醒 √
                        'outapply' => [
                            'is_enable' => '0',
                            'template_id' => '',
                            'keywords' => ['character_string2', 'thing3', 'thing5', 'thing8','time6'],
                        ],
                        //货物到仓通知 √
                        'toshop' => [
                            'is_enable' => '0',
                            'template_id' => '',
                            'keywords' => ['character_string7', 'thing3', 'time2'],
                        ],
                        //发货通知 √
                        'sendpack' => [
                            'is_enable' => '0',
                            'template_id' => '',
                            'keywords' => ['character_string6', 'character_string38', 'character_string14','thing15','time5'],
                        ],
                        //付款单生成提醒 √
                        'payorder' => [
                            'is_enable' => '0',
                            'template_id' => '',
                            'keywords' => ['character_string4', 'character_string8', 'character_string5','amount2','time3'],
                        ],
                    ],
                ],
            // 小票打印机设置
            'printer' => [
                'key' => 'printer',
                'describe' => '小票打印机设置',
                'values' => [
                    'is_open' => '0',   // 是否开启打印
                    'printer_id' => '', // 打印机id
                    'order_status' => [], // 订单类型 10下单打印 20付款打印 30确认收货打印
                    'printsite'=> [], //1=后台录入包裹时打印  2=仓管扫码入库时
                ],
            ],
            // 物流信息设置
            'notice' => [
                'key' => 'notice',
                'describe' => '物流模板',
                'values' => [
                    //入库通知
                    'enter'=>[
                        'is_enable' => 0,
                        'describe' => "包裹已入库，可提交打包",
                    ],
                    //提交打包
                    'packageit'=>[
                        'is_enable' => 0,
                        'describe' => "包裹已申请打包，请等待查验",
                    ],
                    //查验完成
                    'check'=>[
                        'is_enable' => 0,
                        'describe' => "包裹查验完成，等待支付",
                    ],
                    //支付完成
                    'ispay'=>[
                        'is_enable' => 0,
                        'describe' => "包裹已支付，即将安排发货",
                    ],
                    //发货操作
                    'dosend'=>[
                        'is_enable' => 0,
                        'describe' => "包裹已发货，国际单号：{code}",
                    ],
                    //已到货
                    'reach'=>[
                        'is_enable' => 0,
                        'describe' => "包裹已到货，请注意查收",
                    ],
                    //已收货
                    'take'=>[
                        'is_enable' => 0,
                        'describe' => "包裹已收货，感谢你的支持",
                    ],
                    //问题件
                    'problem'=>[
                        'is_enable' => 0,
                        'describe' => "包裹有误，请联系客服处理",
                    ],
                    //是否查询包裹信息；
                    'is_package'=>[
                        'is_enable' => 1,
                    ],
                    //是否查询集运单信息；
                    'is_inpack'=>[
                        'is_enable' => 1,
                    ],
                    //是否向17track注册预报的包裹；
                    'is_track_yubao'=>[
                        'is_enable' => 1,
                    ],
                    //是否向17track注册发货的包裹；
                    'is_track_fahuo'=>[
                        'is_enable' => 1,
                    ],
                    //是否向17track注册转单的包裹；
                    'is_track_zhuandan'=>[
                        'is_enable' => 1,
                    ],
                ],
            ],
              //智能AI
            'aiidentify'=>[
                'key' => 'aiidentify',
                'describe' => '智能识别',
                // 百度标准版
                'values' => [
                    'is_enable' =>0,
                    'apikey' => '',
                    'apisecret'=>'',
                    'keyword1'=>'',
                    'keyword2'=>'',
                    'is_cangguanduan'=>0, //仓管端
                    'is_houtaiduan'=>0,  //后台端
                    'is_dianzicheng'=>0, //电子秤
                ],
            ],
            // 充值凭证
            'bank' => [
                'key' => 'bank',
                'describe' => '温馨提示',
                'values' => [
                    'setting' => '',
                    'is_liushui' => 1,
                    'is_yinhang' => 1,
                    'is_jine' => 1,
                    'is_pingzhengimage' => 1,
                    'is_bizhong' => 1,
                    'is_pingzhengimage' => 1,
                    'is_chongzhidate' => 1,
                ],
            ],
            // 批次设置
            'batch' => [
                'key' => 'batch',
                'describe' => '批次规则',
                'values' => [
                    'firstword_mode' =>0,//0开启 1不开启
                    'firstword' => 'XS',  //首字母
                    'ftime_mode'=> 0, //0开启 1不开启
                    'ftime' => '',  //时间模式
                    'random'=> 0, //0 随机模式 顺序生成
                    'is_autolog'=>0, // 不开启自动更新，1开启
                ],
            ],
            // 满额包邮设置
            'full_free' => [
                'key' => 'full_free',
                'describe' => '满额包邮设置',
                'values' => [
                    'is_open' => '0',   // 是否开启满额包邮
                    'money' => '',      // 单笔订单额度
                    'notin_region' => [ // 不参与包邮的地区
                        'province' => [],
                        'citys' => [],
                        'treeData' => [],
                    ],
                    'notin_goods' => [],  // 不参与包邮的商品   (商品id集)
                ],
            ],
            // 用户充值设置
            'recharge' => [
                'key' => 'recharge',
                'describe' => '用户充值设置',
                'values' => [
                    'is_entrance' => '1',   // 是否允许用户充值
                    'is_custom' => '1',   // 是否允许自定义金额
                    'is_match_plan' => '1',   // 自定义金额是否自动匹配合适的套餐
                    'describe' => "1. 账户充值仅限微信在线方式支付，充值金额实时到账；\n" .
                        "2. 账户充值套餐赠送的金额即时到账；\n" .
                        "3. 账户余额有效期：自充值日起至用完即止；\n" .
                        "4. 若有其它疑问，可拨打客服电话",     // 充值说明
                ],
            ],
            // 积分设置
            SettingEnum::POINTS => [
                'key' => SettingEnum::POINTS,
                'describe' => SettingEnum::data()[SettingEnum::POINTS]['describe'],
                'values' => [
                    'points_name' => '积分',         // 积分名称自定义
                    'is_shopping_gift' => '0',      // 是否开启购物送积分
                    'gift_ratio' => '100',            // 是否开启购物送积分
                    'is_shopping_discount' => '0',    // 是否允许下单使用积分抵扣
                    'discount' => [     // 积分抵扣
                        'discount_ratio' => '0.01',       // 积分抵扣比例
                        'full_order_price' => '100.00',       // 订单满[?]元
                        'max_money_ratio' => '10',             // 最高可抵扣订单额百分比
                    ],
                    // 充值说明
                    'describe' => "a) 积分不可兑现、不可转让,仅可在本平台使用;\n" .
                        "b) 您在本平台参加特定活动也可使用积分,详细使用规则以具体活动时的规则为准;\n" .
                        "c) 积分的数值精确到个位(小数点后全部舍弃,不进行四舍五入)\n" .
                        "d) 买家在完成该笔交易(订单状态为“已完成”)后才能得到此笔交易的相应积分",
                ],
            ],
            // 订阅消息设置
            SettingEnum::SUBMSG => [
                'key' => SettingEnum::SUBMSG,
                'describe' => SettingEnum::data()[SettingEnum::SUBMSG]['describe'],
                'values' => [
                    // 订单消息
                    'order' => [
                        // 包裹入库通知
                        'enter' => [
                            'template_id' => '',
                            'is_enable' =>0,
                            'keywords' => ['number1', 'character_string2', 'thing6', 'time3','thing4'],
                            'title' => '包裹入库通知',
                        ],
                        // 包裹发货通知
                        'delivery' => [
                            'template_id' => '',
                            'is_enable' =>0,
                            'keywords' => ['character_string2', 'date3', 'thing4', 'character_string5'],
                            'title' => '订单发货通知',
                        ],
                        // 包裹发货通知
                        'pay' => [
                            'template_id' => '',
                            'is_enable' =>0,
                            'keywords' => ['character_string1', 'amount2', 'phrase4', 'date3'],
                            'title' => '订单待支付通知',
                        ],
                        // 物流状态通知
                        'logistics' => [
                            'template_id' => '',
                            'is_enable' =>0,
                            'keywords' => ['phrase1', 'thing6', 'character_string2', 'date3', 'thing4'],
                            'title' => '售后状态通知',
                        ],
                        
                    ],
                    // 拼团消息
                    'sharing' => [
                        // 拼团进度通知
                        'active_status' => [
                            'template_id' => '',
                            'is_enable' =>0,
                            'keywords' => ['thing1', 'amount5', 'number7', 'thing3', 'thing6'],
                            'title' => '拼团进度通知',
                        ],
                    ],
                    // 分销商消息
                    'dealer' => [
                        // 分销商入驻审核通知
                        'apply' => [
                            'template_id' => '',
                            'is_enable' =>0,
                            'keywords' => ['date4', 'thing17', 'date3', 'thing7'],
                            'title' => '审核结果通知',
                        ],
                        // 提现成功通知
                        'withdraw_01' => [
                            'template_id' => '',
                            'is_enable' =>0,
                            'keywords' => ['amount1', 'phrase3', 'thing4'],
                            'title' => '提现成功通知',
                        ],
                        // 提现失败通知
                        'withdraw_02' => [
                            'template_id' => '',
                            'is_enable' =>0,
                            'keywords' => ['amount2', 'date3', 'thing4'],
                            'title' => '佣金提现失败通知',
                        ],
                    ],
                ],
            ],
            // LINE小程序设置
            'line_config' => [
                'key' => 'line_config',
                'describe' => 'LINE小程序设置',
                'values' => [
                    'is_enable' => '0',
                    'channel_id' => '',
                    'channel_secret' => '',
                    'liff_id' => '',
                    'endpoint_url' => '',
                    'liff_size' => 'full',
                    'scopes' => ['openid', 'profile'],
                    'google_maps_key' => '',
                ]
            ],

            // LINE 消息通知
            'line_messaging' => [
                'key' => 'line_messaging',
                'describe' => 'LINE消息通知',
                'values' => [
                    // Channel 配置
                    'is_enable' => '0',
                    'channel_id' => '',
                    'channel_secret' => '',
                    'access_token' => '',
                    
                    // API 设置
                    'api_base_url' => 'https://api.line.me/v2/bot',
                    'timeout' => 30,
                    'retry_times' => 3,
                    'log_enabled' => '1',
                    
                    // LIFF 配置
                    'liff_id' => '',
                    'liff_url' => '',
                    
                    // 消息模板配置
                    'templates' => [
                        // 入库通知
                        'inwarehouse' => [
                            'is_enable' => '0',
                            'name' => '包裹入库通知',
                            'alt_text' => '📦 包裹入库通知',
                            'priority' => 'high',
                            'send_delay' => 0,
                            'flex_template' => json_encode([
                                'type' => 'bubble',
                                'header' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        [
                                            'type' => 'text',
                                            'text' => '📦 包裹入库通知',
                                            'weight' => 'bold',
                                            'size' => 'lg',
                                            'color' => '#1DB446'
                                        ]
                                    ],
                                    'backgroundColor' => '#F0FFF0'
                                ],
                                'body' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '仓库：{{shop_name}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '快递单号：{{express_num}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '入库时间：{{entering_warehouse_time}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '重量：{{weight}}kg', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'separator', 'margin' => 'md'],
                                        ['type' => 'text', 'text' => '{{remark}}', 'size' => 'sm', 'color' => '#888888', 'margin' => 'md', 'wrap' => true]
                                    ],
                                    'spacing' => 'sm'
                                ],
                                'footer' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        [
                                            'type' => 'button',
                                            'action' => ['type' => 'uri', 'label' => '查看详情', 'uri' => '{{detail_url}}'],
                                            'style' => 'primary',
                                            'color' => '#1DB446'
                                        ]
                                    ]
                                ]
                            ]),
                            'variables' => ['shop_name', 'express_num', 'entering_warehouse_time', 'weight', 'remark', 'detail_url']
                        ],
                        
                        // 发货通知
                        'sendpack' => [
                            'is_enable' => '0',
                            'name' => '发货通知',
                            'alt_text' => '🚚 发货通知',
                            'priority' => 'high',
                            'send_delay' => 0,
                            'flex_template' => json_encode([
                                'type' => 'bubble',
                                'header' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '🚚 发货通知', 'weight' => 'bold', 'size' => 'lg', 'color' => '#0066CC']
                                    ],
                                    'backgroundColor' => '#E6F3FF'
                                ],
                                'body' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '订单号：{{order_sn}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '国际单号：{{t_order_sn}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '重量：{{weight}}kg', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '线路：{{t_name}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '发货时间：{{send_time}}', 'size' => 'sm', 'wrap' => true]
                                    ],
                                    'spacing' => 'sm'
                                ],
                                'footer' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        [
                                            'type' => 'button',
                                            'action' => ['type' => 'uri', 'label' => '查看物流', 'uri' => '{{tracking_url}}'],
                                            'style' => 'primary',
                                            'color' => '#0066CC'
                                        ]
                                    ]
                                ]
                            ]),
                            'variables' => ['order_sn', 't_order_sn', 'weight', 't_name', 'send_time', 'tracking_url']
                        ],
                        
                        // 支付成功通知
                        'payment' => [
                            'is_enable' => '0',
                            'name' => '支付成功通知',
                            'alt_text' => '✅ 支付成功',
                            'priority' => 'high',
                            'send_delay' => 0,
                            'flex_template' => json_encode([
                                'type' => 'bubble',
                                'header' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '✅ 支付成功', 'weight' => 'bold', 'size' => 'lg', 'color' => '#FF6B00']
                                    ],
                                    'backgroundColor' => '#FFF5E6'
                                ],
                                'body' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '订单号：{{order_sn}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '支付金额：¥{{total_free}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '支付时间：{{pay_time}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'separator', 'margin' => 'md'],
                                        ['type' => 'text', 'text' => '{{remark}}', 'size' => 'sm', 'color' => '#888888', 'margin' => 'md', 'wrap' => true]
                                    ],
                                    'spacing' => 'sm'
                                ]
                            ]),
                            'variables' => ['order_sn', 'total_free', 'pay_time', 'remark']
                        ],
                        
                        // 打包完成通知
                        'dabaosuccess' => [
                            'is_enable' => '0',
                            'name' => '打包完成通知',
                            'alt_text' => '📋 打包完成',
                            'priority' => 'normal',
                            'send_delay' => 0,
                            'flex_template' => json_encode([
                                'type' => 'bubble',
                                'header' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '📋 打包完成', 'weight' => 'bold', 'size' => 'lg', 'color' => '#9933FF']
                                    ],
                                    'backgroundColor' => '#F5E6FF'
                                ],
                                'body' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '订单号：{{order_sn}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '包裹数量：{{pack_count}}件', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '总重量：{{weight}}kg', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '体积：{{volume}}cm³', 'size' => 'sm', 'wrap' => true]
                                    ],
                                    'spacing' => 'sm'
                                ],
                                'footer' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        [
                                            'type' => 'button',
                                            'action' => ['type' => 'uri', 'label' => '去支付', 'uri' => '{{pay_url}}'],
                                            'style' => 'primary',
                                            'color' => '#9933FF'
                                        ]
                                    ]
                                ]
                            ]),
                            'variables' => ['order_sn', 'pack_count', 'weight', 'volume', 'pay_url']
                        ],
                        
                        // 付款单生成通知
                        'payorder' => [
                            'is_enable' => '0',
                            'name' => '付款单生成通知',
                            'alt_text' => '💰 付款单生成',
                            'priority' => 'normal',
                            'send_delay' => 0,
                            'flex_template' => json_encode([
                                'type' => 'bubble',
                                'header' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '💰 付款单生成', 'weight' => 'bold', 'size' => 'lg', 'color' => '#FF3366']
                                    ],
                                    'backgroundColor' => '#FFE6F0'
                                ],
                                'body' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '订单号：{{order_sn}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '应付金额：¥{{amount}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '生成时间：{{create_time}}', 'size' => 'sm', 'wrap' => true]
                                    ],
                                    'spacing' => 'sm'
                                ],
                                'footer' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        [
                                            'type' => 'button',
                                            'action' => ['type' => 'uri', 'label' => '立即支付', 'uri' => '{{pay_url}}'],
                                            'style' => 'primary',
                                            'color' => '#FF3366'
                                        ]
                                    ]
                                ]
                            ]),
                            'variables' => ['order_sn', 'amount', 'create_time', 'pay_url']
                        ],
                        
                        // 到仓通知
                        'toshop' => [
                            'is_enable' => '0',
                            'name' => '到仓通知',
                            'alt_text' => '🏪 到仓通知',
                            'priority' => 'normal',
                            'send_delay' => 0,
                            'flex_template' => json_encode([
                                'type' => 'bubble',
                                'header' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '🏪 到仓通知', 'weight' => 'bold', 'size' => 'lg', 'color' => '#00CC99']
                                    ],
                                    'backgroundColor' => '#E6FFF5'
                                ],
                                'body' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '仓库：{{shop_name}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '快递单号：{{express_num}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '到仓时间：{{arrival_time}}', 'size' => 'sm', 'wrap' => true]
                                    ],
                                    'spacing' => 'sm'
                                ]
                            ]),
                            'variables' => ['shop_name', 'express_num', 'arrival_time']
                        ],
                        
                        // 出库申请通知
                        'outapply' => [
                            'is_enable' => '0',
                            'name' => '出库申请通知',
                            'alt_text' => '📤 出库申请',
                            'priority' => 'normal',
                            'send_delay' => 0,
                            'flex_template' => json_encode([
                                'type' => 'bubble',
                                'header' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '📤 出库申请', 'weight' => 'bold', 'size' => 'lg', 'color' => '#FF9900']
                                    ],
                                    'backgroundColor' => '#FFF5E6'
                                ],
                                'body' => [
                                    'type' => 'box',
                                    'layout' => 'vertical',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '申请单号：{{apply_sn}}', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '包裹数量：{{package_count}}件', 'size' => 'sm', 'wrap' => true],
                                        ['type' => 'text', 'text' => '申请时间：{{apply_time}}', 'size' => 'sm', 'wrap' => true]
                                    ],
                                    'spacing' => 'sm'
                                ]
                            ]),
                            'variables' => ['apply_sn', 'package_count', 'apply_time']
                        ]
                    ]
                ]
            ],
            // LINE Pay 支付设置
            'line_pay' => [
                'key' => 'line_pay',
                'describe' => 'LINE Pay支付设置',
                'values' => [
                    'is_enable' => '0',
                    'channel_id' => '',
                    'channel_secret' => '',
                    'is_test' => '1',
                ]
            ],
            // 电子面单配置
            'waybill' => [
                'key' => 'waybill',
                'describe' => '电子面单配置',
                'values' => [
                    'zhongtong' => [
                        'api_url' => '',
                        'api_key' => '',
                        'api_secret' => '',
                        'company_code' => 'ZTO',
                    ],
                    'shunfeng' => [
                        'api_url' => '',
                        'api_key' => '', // PartnerID
                        'api_secret' => '', // Checkword
                        'custid' => '', // 月结卡号
                        'pay_method' => 1, // 默认寄方付
                        'company_code' => 'SF',
                    ],
                ]
            ],
            // 营销设置
            'marketing' => [
                'key' => 'marketing',
                'describe' => '营销设置',
                'values' => [
                    // 增值服务推荐
                    'upsell' => [
                        'timeout' => 24, // 默认推荐锁定24小时
                        'require_proof' => 0, // 默认不强制上传凭证
                    ],
                    // 代理商白牌化
                    'agent' => [
                        'white_label_enable' => 0, // 默认关闭白牌化
                        'min_level' => 0, // 最低可用等级
                    ],
                ]
            ],
        ];
    }



}
