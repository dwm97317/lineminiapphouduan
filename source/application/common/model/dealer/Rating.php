<?php

namespace app\common\model\dealer;

use app\common\model\BaseModel;

/**
 * 分销商等级
 * Class Apply
 * @package app\common\model\dealer
 */
class Rating extends BaseModel
{
    protected $name = 'dealer_rating';

    /**
     * 获取器：升级条件
     * @param $json
     * @return mixed
     */
    public function getUpgradeAttr($json)
    {
        return json_decode($json, true);
    }
    
    /**
     * 获取器：升级条件
     * @param $json
     * @return mixed
     */
    public function getSettingAttr($json)
    {
        return json_decode($json, true);
    }
    
    /**
     * 会员等级详情
     * @param $grade_id
     * @param array $with
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($rating_id, $with = [])
    {
        return static::get($rating_id, $with);
    }
    
    /**
     * 获取可用的会员等级列表
     * @param null $wxappId
     * @param array $order 排序规则
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getUsableList($wxappId = null, $order = ['weight' => 'asc'])
    {
        $model = new static;
        $wxappId = $wxappId ? $wxappId : $model::$wxapp_id;
        return $model->where('status', '=', '1')
            ->where('is_delete', '=', '0')
            ->where('wxapp_id', '=', $wxappId)
            ->order($order)
            ->select();
    }
    
    /**
     * 验证等级权重是否存在
     * @param int $weight 验证的权重
     * @param int $gradeId 自身的等级ID
     * @return bool
     */
    public static function checkExistByWeight($weight, $gradeId = 0)
    {
        $model = new static;
        $gradeId > 0 && $model->where('rating_id', '<>', (int)$gradeId);
        return $model->where('weight', '=', (int)$weight)
            ->value('rating_id');
    }
    

}