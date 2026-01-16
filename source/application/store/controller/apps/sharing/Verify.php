<?php
namespace app\store\controller\apps\sharing;
use app\store\controller\Controller;
use app\store\model\sharing\SharingUser;

class Verify extends Controller{
    
    public function index(){
        $list = (new SharingUser())->getList();
        return $this->fetch('index',compact('list'));
    }
    
    
    public function list(){
        $list = (new SharingUser())->getListX();
        return $this->fetch('list',compact('list'));
    }
 
    public function update(){
       $ids = $this->postData('selectIds')[0];
       $form = $this->postData('verify');
       $idsArr = explode(',',$ids);  
       $model = (new SharingUser());
       if($model->modifyStatus($idsArr,$form)){
          return $this->renderSuccess('审核操作成功'); 
       }
       return $this->renderError($model->getError()??'审核操作失败');
    }
}
?>