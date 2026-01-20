<?php
namespace app\api\controller\sharing_origin;

use app\api\controller\Controller;
use app\api\model\sharing\Active as ActiveModel;
use app\api\service\sharing\LogisticsPrice;

/**
 * 集运拼团广场API
 */
class Logistics extends Controller
{
    /**
     * 获取拼团列表
     */
    public function square()
    {
        $model = new ActiveModel;
        $list = $model->getLogisticsList($this->request->param());
        return $this->renderSuccess(compact('list'));
    }
    
    /**
     * 获取拼团详情
     */
    public function detail()
    {
        $activeId = $this->request->param('active_id');
        $detail = ActiveModel::detailWithLogistics($activeId);
        if (!$detail) {
            return $this->renderError('拼团不存在');
        }
        return $this->renderSuccess(compact('detail'));
    }
    
    /**
     * 发起拼团
     */
    public function create()
    {
        return $this->renderSuccess([], '功能开发中');
    }
    
    /**
     * 加入拼团
     */
    public function join()
    {
        return $this->renderSuccess([], '功能开发中');
    }
    
    /**
     * 团长关闭拼团
     */
    public function close()
    {
        return $this->renderSuccess([], '功能开发中');
    }
    
    /**
     * 退出拼团
     */
    public function quit()
    {
        return $this->renderSuccess([], '功能开发中');
    }
}
