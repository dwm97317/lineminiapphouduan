<?php
namespace app\store\controller\package;
use app\store\model\Countries;
use app\store\model\Package;
use app\store\controller\Controller;
use app\store\model\PackageItem;
use app\store\model\store\Shop as ShopModel;
use app\store\model\User as UserModel;
use app\common\model\Logistics;
use app\store\model\Inpack;
use app\store\model\ShelfUnitItem;
use app\common\model\setting;
/**
 * 包裹预报控制器
 * Class StoreUser
 * @package app\store\controller
 */
class Report extends Controller
{
    /**
     * 预报列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {   
        $list = [];
        $packageModel = new Package();
        $map1 = ['status' =>1];
        $map2 = \request()->param();
        $map = array_merge($map1,$map2);
        $list = $packageModel->getList($map);
        // dump($list->toArray());die;
        $shopList = ShopModel::getAllList();
         
        $status = [1=>'未入库',2=>'已入库',3=>'已拣货上架',4=>'待打包',5=>'待支付',6=>'已支付',7=>'已分拣下架',8=>'已打包',9=>'已发货',10=>'已收货',11=>'已完成'];
        $statusTotal = [];
        foreach($status as $key => $val){
            $map_where['status'] = $key;
            $statusTotal[$key] = $packageModel->where($map_where)->count();
        } 
        $set = Setting::detail('store')['values'];
        return $this->fetch('index', compact('list','shopList','statusTotal','set'));
    }
    //预报包裹入库功能
    public function depot(){
        $id = input('id');
        $packageModel = new Package();
        if(empty($id)){
            return $this->renderError($packageModel->getError() ?: '入库失败');
        }
        if ($packageModel->setStatu($id)) {
            return $this->renderSuccess('入库成功');
        }
        return $this->renderError($packageModel->getError() ?: '入库失败');
    }

    //预报包裹编辑功能
    public function edit($id)
    {
        if(empty($id)){
            return;
        }
        $list1 = [];
        $model = new Package();
        $list1 = $model->getOne($id);
        $list = $list1[0];
        $shopList = ShopModel::getListName();

        return $this->fetch('edit', compact('list','shopList'));

    }
    //包裹删除功能
    public function delete($id)
    {
        $model = new Package();
        if (!$model->setDelete($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');

    }

    //保存修改的值
    public function  save(){
        $param = input();
        $list = [
          "id" => $param['id'],
          "stroage_id" => $param['store_id'],
          "price" => $param['price'],
          "remark" => $param['remark'],
          "status" => $param['status']
       ];
       if (!$param['country_id']){
           $list['country_id'] = $param['country_id'];
       }
       $model = new Package();
       // 新增记录
       if ($model->setSave($list)) {
            return $this->renderSuccess('保存成功', url('package.report/index'));
       }
       return $this->renderError($model->getError() ?: '保存失败');
    }
    
    // 设置用户
    public function upuser(){
         $ids = $this->postData('selectIds')[0];
        $user_id = $this->postData('package')['user_id'];
        $idsArr = explode(',',$ids);
        $res = (new Package())->whereIn("id",$idsArr)->update(['member_id'=>$user_id,'is_take'=>2,'updated_time'=>getTime()]);
        if (!$res){
            return $this->renderError('修改提交失败');
        }
        return $this->renderSuccess('修改提交成功');
    }
    
    //批量更新包裹入库状态
    public function upsatatus(){
       $ids = $this->postData('selectIds')[0];
       $status = $this->postData('pack')['status'];
       $idsArr = explode(',',$ids);
       $model = new Package();
       foreach ($idsArr as $v){
           $_up = [
             'status' => $status,
             'entering_warehouse_time' => $status==1?'':getTime(),
             'updated_time' => getTime()
           ];     
            Logistics::add($v,"包裹入库");
           $model->where(['id'=>$v])->update($_up);
       }    
       return $this->renderSuccess('更新成功');
    }
    
    public function shelfDown(){
        $ids = $this->postData('id')[0];
    }
    
    //获取国家
    public function getCountry(){
        $key = request()->param('k');
        if (!$key){
            return $this->renderSuccess([]);
        }
        $model = new Countries();
        $list = $model->getList($key);
        return $this->renderSuccess($list);
    }


     /**
     * 后台【包裹管理】【详情】
     * @param $id
     * @return list
     * @throws \think\Exception
     */
    public function item(){
        $id = input('id');
        $post = input();
        $map = ['id'=>$id];
        if (isset($post['search'])){
            $map['search'] = $post['search'];
        }
        $model = new PackageItem();
        $packageModel = new Package();
        $detail = $packageModel->detail($id);
        //获取到国家信息
        $detail['country'] = (new Countries())->where('id',$detail['country_id'])->find();
        //获取到用户信息
        $detail['user'] = (new UserModel())->where('user_id',$detail['member_id'])->find();
        //获取到仓库信息
        $detail['storage'] = (new ShopModel())->where('shop_id',$detail['storage_id'])->find();
        //获取仓库日志
        $detail['log'] = (new Logistics())->where('order_sn',$detail['order_sn'])->select();
        //获取集运信息
        $set = Setting::detail('store')['values'];
        // dump($detail->toArray());die;
        // $detail['inpack'] = (new Inpack())->where('pack_ids','in',$id)->select();
        //获取货架信息
        // $data = (new ShelfUnitItem())->where('pack_id',$id)->find();
        
        $list = $model->getList($map);
        return $this->fetch('item', compact('list','detail','set'));
    }
}
