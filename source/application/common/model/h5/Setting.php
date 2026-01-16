<?php
declare (strict_types=1);

namespace app\common\model\h5;
use app\common\model\BaseModel;
use think\Cache;
use app\common\library\helper;

/**
 * H5设置模型
 * Class Setting
 * @package app\common\model\h5
 */
class Setting extends BaseModel
{
    // 定义表名
    protected $name = 'h5_setting';

    protected $createTime = false;

    /**
     * 获取器: 转义数组格式
     * @param $value
     * @return array
     */
    public function getValuesAttr($value): array
    {
        return helper::jsonDecode($value);
    }

    /**
     * 修改器: 转义成json格式
     * @param $value
     * @return string
     */
    public function setValuesAttr($value): string
    {
        return helper::jsonEncode($value);
    }

    /**
     * 获取指定项设置
     * @param string $key
     * @param int|null $storeId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getItem(string $key, ?int $wxapp_id = null): array
    {
        $data = static::getAll($wxapp_id);
        return isset($data[$key]) ? $data[$key]['values'] : [];
    }

    /**
     * 获取H5访问url
     * @param int|null $storeId
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getH5Url(?int $wxapp_id = null)
    {
        return static::getItem('basic', $wxapp_id)['baseUrl'];
    }

    /**
     * 获取全部设置
     * @param int|null $storeId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getAll(int $wxapp_id=null): array
    {
        $model = new static;
        is_null($wxapp_id) && $wxapp_id = static::$wxapp_id;
        if (!$data = Cache::get("h5_setting_{$wxapp_id}")) {
            // 获取全部设置
            $setting = $model->getList($wxapp_id);
            $data = $setting->isEmpty() ? [] : helper::arrayColumn2Key($setting->toArray(), 'key');
            // 写入缓存中
            Cache::tag('cache')->set("h5_setting_{$wxapp_id}", $data);
        }
        // 合并默认设置
        return array_merge_multiple($model->defaultData(), $data);
    }

    /**
     * 获取商城设置列表
     * @param int $storeId
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getList($wxapp_id): \think\Collection
    {
        return $this->where('wxapp_id', '=', $wxapp_id)->select();
    }

    /**
     * 获取设置项信息
     * @param string $key
     * @return array|\think\Model|null
     */
    public static function detail(string $key)
    {
        return static::get(compact('key'));
    }

    /**
     * 默认配置
     * @return array
     */
    public function defaultData(): array
    {
        return [
            'basic' => [
                'key' => 'basic',
                'describe' => '基础设置',
                'values' => [
                    // 是否启用h5端访问  0=>关闭  1=>开启
                    'enabled' => 1,
                    // h5站点url [默认是当前访问的域名]
                    'baseUrl' => base_url(),
                ]
            ]
        ];
    }
}