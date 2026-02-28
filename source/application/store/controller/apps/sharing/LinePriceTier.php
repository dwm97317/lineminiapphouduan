<?php

namespace app\store\controller\apps\sharing;

use app\store\controller\Controller;
use app\store\model\sharing\LinePriceTier as LinePriceTierModel;
use app\common\model\Line;

/**
 * 线路价格阶梯控制器
 */
class LinePriceTier extends Controller
{
    /**
     * 线路价格管理页面
     */
    public function index()
    {
        if (!$this->request->isAjax()) {
            // 获取所有线路
            $lines = Line::where('wxapp_id', '=', $this->getWxappId())
                ->where('status', '=', 1)
                ->field('id, name')
                ->select();
            
            return $this->fetch('index', [
                'lines' => $lines
            ]);
        }
    }
    
    /**
     * 获取指定线路的价格阶梯
     */
    public function getTiers()
    {
        $lineId = $this->request->param('line_id');
        
        if (empty($lineId)) {
            return $this->renderError('请选择线路');
        }
        
        $tiers = LinePriceTierModel::getByLineId($lineId, $this->getWxappId());
        
        return $this->renderSuccess([
            'list' => $tiers
        ]);
    }
    
    /**
     * 保存价格阶梯
     */
    public function saveTiers()
    {
        $lineId = $this->request->post('line_id');
        $tiers = $this->request->post('tiers/a', []);
        
        if (empty($lineId)) {
            return $this->renderError('请选择线路');
        }
        
        if (empty($tiers)) {
            return $this->renderError('请至少添加一个价格阶梯');
        }
        
        // 验证数据
        foreach ($tiers as $tier) {
            if (!isset($tier['min_weight']) || !isset($tier['price_per_kg'])) {
                return $this->renderError('价格阶梯数据不完整');
            }
            
            if ($tier['min_weight'] < 0 || $tier['price_per_kg'] <= 0) {
                return $this->renderError('重量和价格必须为正数');
            }
        }
        
        // 按重量排序
        usort($tiers, function($a, $b) {
            return $a['min_weight'] - $b['min_weight'];
        });
        
        $model = new LinePriceTierModel();
        if ($model->saveBatch($lineId, $tiers)) {
            return $this->renderSuccess('保存成功');
        }
        
        return $this->renderError($model->getError() ?: '保存失败');
    }
    
    /**
     * 删除价格阶梯
     */
    public function deleteTier()
    {
        $id = $this->request->post('id');
        
        if (empty($id)) {
            return $this->renderError('参数错误');
        }
        
        $model = LinePriceTierModel::get($id);
        if (!$model || $model['wxapp_id'] != $this->getWxappId()) {
            return $this->renderError('记录不存在');
        }
        
        if ($model->delete()) {
            return $this->renderSuccess('删除成功');
        }
        
        return $this->renderError('删除失败');
    }
}
