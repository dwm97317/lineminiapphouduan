<?php
namespace app\api\controller\sharp;
use app\api\controller\Controller;
use app\api\model\sharing\SharingOrder;
/**
 * 拼团订单控制器
 * Class Article
 * @package app\api\controller
 */
class Order extends Controller
{
    
     // 创建拼团订单
     public function create(){
        $form = $this->request->param();
        // 当前用户信息
        $userInfo = $this->getUser();
        $model = (new SharingOrder());
        $form['member_id'] = $userInfo['user_id'];
        if ($model->created($form)){
              return $this->renderSuccess('拼团已成功发起');
        }
        return $this->renderError($model->getError()??'操作失败');
     }
     
     // 管理列表
     public function managelist(){
        $query = $this->request->param();
        // 当前用户信息
        $userInfo = $this->getUser();
        $query['member_id'] = $userInfo['user_id'];
        $model = (new SharingOrder());
        $query['status'] = $this->mapStatus($query['status']);
        $list = $model->getList($query);
        
        return $this->renderSuccess(compact('list'));
     }
     
     // 映射查询状态
     public function mapStatus($value){
         $query_status = [
            0 => [1,2,3,4,5,6,7,8],
            1 => [1,3,4,5,6],
            2 => [7],
            3 => [8]
         ];
         return $query_status[$value]??0; 
     }
}
