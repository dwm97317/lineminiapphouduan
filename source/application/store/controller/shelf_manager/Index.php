<?php
namespace app\store\controller\shelf_manager;

use app\store\controller\Controller;
use app\store\model\store\Shop as ShopModel;
use app\store\model\Shelf;
use app\store\model\ShelfUnit;
use app\store\model\ShelfUnitItem;
use app\store\model\Package;

/**
 * 控制器
 * Class StoreUser
 * @package app\store\controller
 */
class Index extends Controller
{
    /**
     * 用户列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){
        $list = (new Shelf())->getList([]);
        foreach ($list as &$value) {
            $value['num'] = (new ShelfUnit())->where(['shelf_id'=>$value['id']])->count();
        }
        return $this->fetch('index', compact('list'));
    }
    
    // 货位数据
    public function dataShelfUnit(){
       $list = (new Shelf())->getList([]);
       $query = [];
       $postData = $this->request->param();
       if (!empty($postData['shelf_id'])){
           $query['shelf_id'] = $postData['shelf_id'];
       }
       if (!empty($postData['express_num'])){
           $query['express_num'] = $postData['express_num'];
       }
       if (!empty($postData['search'])){
           $query['shelf_unit_id'] = $postData['search'];
       }
       if (!isset($query['express_num'])){
           $data = (new ShelfUnit())->getWithShelf($query);
           foreach($data as &$v){
               $_map['shelf_unit_id'] = $v['shelf_unit_id'];
               $shelfunititem = (new ShelfUnitItem())->getItemWithPackage($_map);
               if ($shelfunititem){
                   $v['shelfunititem'] = $shelfunititem;
               }
           }
       }else{
            $data = $_map['express_num'] = $query['express_num'];
            $item = (new ShelfUnitItem())->getItemWithPackage($_map);
            $sheft_map = ['shelf_unit_id'=>$item['0']['shelf_unit_id']];
            $dataShelf = (new ShelfUnit())->getWithShelf($sheft_map);
            foreach($dataShelf as &$v){
               if ($item){
                   $v['shelfunititem'] = $item;
               }
           }
           $data = $dataShelf;
       }
      
       return $this->fetch('datashelfunit', compact('data','list'));
    }
    
    /**
     * 获取货架
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function getShelf(){
        $shop_id = $this->request->param('shop_id');
        $shelf = (new Shelf())->getList(['ware_no'=>$shop_id]);
        $shelfunit = [];
        if(count($shelf)>0){
            $shelfunit = (new ShelfUnit())->getList($shelf[0]['id']);
        }
        return $this->renderSuccess('','',compact('shelf','shelfunit'));
    }
    
      /**
     * 货位列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function getshelf_unit($shelf_id){
 
        $shelfunit = (new ShelfUnit())->getList($shelf_id);
        return $this->renderSuccess('','',compact('shelfunit'));
    }
    
    /**
     * 删除货架 
     */
    public function shelfdelete($id){
         $model = (new Shelf())->details($id);
         if (!$model->setDelete($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功',url('/store/shop.shelf/index'));
    } 
    
    /**
     * 新增货架
     */
    public function add(){
      $shopList = ShopModel::getAllList();
      if (!$this->request->isAjax()) {
        return $this->fetch('add',compact('shopList'));
      }
   
      // 新增记录
      $model = new Shelf();
      if ($model->add($this->postData('shelf'))) {
          return $this->renderSuccess('添加成功',url('/store/shop.shelf/index'));
      }
      return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑货架
     */
    public function edit($id){
        // 模板详情
        $model = (new Shelf())->details($id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('shelf'))) {
            return $this->renderSuccess('更新成功', url('/store/shop.shelf/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
   /**
     * 货位列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function shelf_unit(){
        $shelf_id = $this->request->param('shelf_id');
        $list = (new ShelfUnit())->getList($shelf_id);
        return $this->fetch('shelf_unit', compact('shelf_id','list'));
    }
    
        
 
    
    public function shelfUnitItem($shelf_unit_id){
        $shelf_id = $this->request->param('express_num');
        $where = [];
        if ($shelf_id){
            $where['express_num'] = $shelf_id;
        }
        $list = (new shelfUnitItem())->item($shelf_unit_id,$where);
        return $this->fetch('shelf_unit_item', compact('list'));
    }
    
    /**
     * 生成货位
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function shelf_unit_create($shelf_id){
       if (!$this->request->isAjax()) {
           return $this->fetch('shelf_unit_create');
       }
       $data = $this->postData('shelf_unit');
       $shelf = (new Shelf())->details($shelf_id);
       if (!$shelf){
           return $this->renderError('货架数据错误');
       }
       $data = (new ShelfUnit())->getShelfUnitData($data,$shelf);
       if (!$data){
            return $this->renderError('货架数据生成错误');
       }
       // 删除原有货位
       $shelfUnit = (new ShelfUnit())->remove($shelf_id);
       if (!$shelfUnit){
           return $this->renderError('货位生成失败');
       }
       $res = (new ShelfUnit())->insertAll($data);
       if ($res) {
            return $this->renderSuccess('更新成功');
       }
       return $this->renderError($model->getError() ?: '更新失败');
    }
    
    // 删除货架单元
    public function deleteShelfUnit($id){
         // 店员详情
        $model = (new ShelfUnit())->details($id);
        
        $unit = (new ShelfUnitItem())->where(['shelf_unit_id'=>$id])->find();
        if ($unit){
            return $this->renderError('货位上存在物品,请先下架物品,在删除');
        }
        
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
    
    // 一键全部下架
    public function deleteallshelfunit(){
        $shelf_unit_item = (new ShelfUnitItem())->select();
        $packIds = array_column($shelf_unit_item->toArray(),'pack_id');
        $res = (new ShelfUnitItem())->where(['shelf_unit_id'=>$id])->delete();
        (new Package())->where('id','in',$packIds)->update(['status'=>7]);
        if ($res) {
            return $this->renderSuccess('下架成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    public function deleteShelfUnitItem($id){
        $id = $id['id'];
        $shelf_unit_item = (new ShelfUnitItem())->where(['shelf_unit_id'=>$id])->select();
        $packIds = array_column($shelf_unit_item->toArray(),'pack_id');
        $res = (new ShelfUnitItem())->where(['shelf_unit_id'=>$id])->delete();
        (new Package())->where('id','in',$packIds)->update(['status'=>7]);
        if ($res) {
            return $this->renderSuccess('下架成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    /**
     * 重新生成二维码
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function shelf_updateCode($shelf_unit_id){
        $model = (new ShelfUnit())->details($shelf_unit_id);
        $res = createQrcode($model->shelf_unit_code,'qrcodes');
        if ($res['errcode']!=0){
            return $this->renderError($model->getError() ?: '二维码生成失败');
        }
        $res = (new ShelfUnit())->where('shelf_unit_id',$shelf_unit_id)->update([
            'shelf_unit_qrcode' => $res['data'],
        ]);
        if ($res) {
            return $this->renderSuccess('更新成功', url('shelf_manager.Index/shelf_unit'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
}

