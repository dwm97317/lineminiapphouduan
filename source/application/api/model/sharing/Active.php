<?php
namespace app\api\model\sharing;

use app\common\model\sharing\Active as ActiveModel;

/**
 * 拼团活动模型
 */
class Active extends ActiveModel
{
    /**
     * 获取集运拼团列表
     */
    public function getLogisticsList($params)
    {
        // 筛选条件
        $this->where('active_type', '=', 20); // 集运拼团
        
        if (isset($params['storage_id']) && $params['storage_id'] > 0) {
            $this->where('storage_id', '=', $params['storage_id']);
        }
        
        if (isset($params['target_region_id']) && $params['target_region_id'] > 0) {
            $this->where('target_region_id', '=', $params['target_region_id']);
        }
        
        // 只显示拼团中的
        $this->where('status', '=', 10);
        
        // 排序
        $sort = $params['sort'] ?? 'latest';
        switch ($sort) {
            case 'deadline':
                $this->order(['end_time' => 'asc']);
                break;
            case 'price':
                $this->order(['price_per_kg' => 'asc']);
                break;
            case 'weight':
                $this->order(['current_weight' => 'desc']);
                break;
            default:
                $this->order(['create_time' => 'desc']);
        }
        
        // 关联查询
        return $this->with(['creator', 'storage', 'targetRegion', 'line'])
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }

    public function creator() {
        return $this->belongsTo('app\common\model\User', 'creator_id');
    }

    public function storage()
    {
        // 假设仓库关联的是地区或者特定的仓库表，暂时关联地区
        return $this->belongsTo('app\common\model\Region', 'storage_id');
    }

    public function targetRegion() {
        return $this->belongsTo('app\common\model\Region', 'target_region_id');
    }

    public function line() {
        return $this->belongsTo('app\common\model\Line', 'line_id');
    }
    
    /**
     * 集运拼团详情扩展
     */
    public static function detailWithLogistics($active_id)
    {
        return self::detail($active_id, ['storage', 'targetRegion', 'line']);
    }
}
