<?php
namespace app\store\controller\apps\sharing;
use app\store\controller\Controller;
use app\store\model\sharing\Setting as SettingModel;

/**
 * 拼单设置控制器
 * Class Active
 * @package app\store\controller\apps\sharing
 */
class Setting extends Controller
{
    
    public function basic(){
        if (!$this->request->isAjax()) {
            $detail = SettingModel::getItem('sharp');
            
            // 添加默认紧迫感开关
            if (!isset($detail['show_view_count'])) {
                $detail['show_view_count'] = 1;
            }
            if (!isset($detail['show_recent_joins'])) {
                $detail['show_recent_joins'] = 1;
            }
            if (!isset($detail['show_urgency_timer'])) {
                $detail['show_urgency_timer'] = 1;
            }
            
            // 获取线路价格配置统计
            $priceStats = \app\store\model\sharing\LinePriceTier::getConfigStats();
            
            return $this->fetch('basic', [
                'data' => $detail,
                'priceStats' => $priceStats
            ]);
        }
        $model = new SettingModel;
        if ($model->edit($this->postData('share'))) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}