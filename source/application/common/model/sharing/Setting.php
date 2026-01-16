<?php
namespace app\common\model\sharing;
use app\common\model\BaseModel;
use think\Cache;

/**
 * 拼团设置模型
 * */
class Setting extends BaseModel{
    
    protected $name = 'sharing_tr_setting';
    
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
        $data = static::getAll($wxapp_id);
        return isset($data[$key]) ? $data[$key]['values'] : [];
    }

    /**
     * 获取全部设置
     * @param null $wxapp_id
     * @return array|mixed
     */
    public static function getAll($wxapp_id = null)
    {
        $self = new static;
        is_null($wxapp_id) && $wxapp_id = $self::$wxapp_id;
        $cacheKey = "sharp_setting_{$wxapp_id}";
        if (!$data = Cache::get($cacheKey)) {
            $data = array_column(collection($self::all())->toArray(), null, 'key');
            Cache::tag('cache')->set($cacheKey, $data);
        }
        return array_merge_multiple($self->defaultData(), $data);
    }

    /**
     * 获取设置项信息
     * @param $key
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($key)
    {
        return static::get(compact('key'));
    }

    /**
     * 默认配置
     * @return array
     */
    public function defaultData()
    {
        return [
            'sharp' => [
                'key' => 'sharp',
                'describe' => '基础设置',
                'values' => [
                    // 是否开启拼团
                    'is_open' => '0',
                    'is_own_join' => 1, // 是否允许自己参团
                    'is_verify' => 0, // 拼团活动 是否需要后台审核
                    'is_shenhe' => 1, //用户申请加入拼团是否需要审核
                    'describe' =>"当满足拼团的包裹上限，拼团会自动进行锁团，无法再加入团员，但可找团长进行协商；",
                    'error' => [
                        'exceed_num' => '已超拼团人数上限',
                        'no_start' => '拼团活动暂未开启，请等待',
                        'end' => '拼团活动已结束'
                    ],
                ]
            ]
        ];
    }
    
} 