<?php
namespace app\store\controller\shop;

use app\store\controller\Controller;

use app\store\model\User;
use app\store\model\store\Shop as ShopModel;
use app\store\model\store\shop\Clerk as ClerkModel;
use app\store\model\Shelf as ShelfModel;
use app\store\model\ShelfUnit;
use app\store\model\ShelfUnitItem;
use app\common\library\FileZip;
/**
 * 门店店员控制器
 * Class Clerk
 * @package app\store\controller\shop
 */
class Shelf extends Controller
{
    public function index(){
        $map['ware_no'] = $this->store['user']['shop_id'];
        $list = (new ShelfModel())->getList($map);
        foreach ($list as &$value) {
            $value['num'] = (new ShelfUnit())->where(['shelf_id'=>$value['id']])->count();
        }
        return $this->fetch('index', compact('list'));
    }

    // 货位数据
    public function dataShelfUnit(){
       $map['ware_no'] = $this->store['user']['shop_id'];
       $list = (new ShelfModel())->getAllList($map);
       $shelf = [];
       if(count($list)>0){
           foreach ($list as $key => $item){
               $shelf[$key] = $item['id'];
           }
       }
    //   dump($shelf);die;
       $data=[];$_map=[];
       $postData = $this->request->param();
           
      if (empty($postData['express_num'])){
           empty($postData['shelf_id']) && $postData['shelf_ids'] = $shelf;
           $data = (new ShelfUnit())->getWithShelf($postData);
           foreach($data as &$v){
               $_map['shelf_unit_id'] = $v['shelf_unit_id'];
               $shelfunititem = (new ShelfUnitItem())->getItemWithPackage($_map);
               if ($shelfunititem){
                   $v['shelfunititem'] = $shelfunititem;
               }
           }
      }else{
           !empty($postData['express_num']) && $_map['express_num'] = $postData['express_num'];
            $item = (new ShelfUnitItem())->getItemWithPackage($_map);
            // dump($item);die;
            if(!empty($item)){
              $sheft_map = ['shelf_unit_id'=>$item['0']['shelf_unit_id']];
                $dataShelf = (new ShelfUnit())->getWithShelf($sheft_map);
                foreach($dataShelf as &$v){
                  if ($item){
                      $v['shelfunititem'] = $item;
                 }
               }  
            }
            $data = $dataShelf;
      }
       return $this->fetch('datashelfunit', compact('data','list'));
    }
    
    public function reset(){
         $ids = $this->postData('selectId');
         $model = (new ShelfUnit()); 
         $res = $model->resetCode($ids);
         if ($res){
             return $this->renderSuccess('生成成功');
         }
         return $this->renderError('生成失败');
    }
    
    public function download(){
         $ids = $this->postData('selectId');
         $model = (new ShelfUnit()); 
         $list = $model->where('shelf_unit_id','in',$ids)->select();
         $res = FileZip::init($list);
         return $this->renderSuccess($res);
    }
}