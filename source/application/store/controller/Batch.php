<?php
namespace app\store\controller;

use app\store\model\Batch as BatchModel;
use app\store\model\BatchTemplate as BatchTemplateModel;
use app\store\model\BatchTemplateItem;
use app\store\model\Setting;
use app\store\model\store\Shop;
use app\api\model\Setting as SettingModel;
use app\store\model\Express as ExpressModel;
use app\store\model\Ditch as DitchModel;
use app\store\model\Inpack;
use app\store\model\Package;
use app\common\model\Logistics;
/**
 * 批次管理
 * Class Forwarder
 * @package app\store\controller
 */
class Batch extends Controller
{
    /**
     * 后台批次首页
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function index()
    {
        // 当前用户菜单url
        $model = new BatchModel;
        $BatchTemplateModel = new BatchTemplateModel;
        $templatelist = $BatchTemplateModel->getAllList();
        $param = $this->request->param();
        $param['status'] = 0;
        $list = $model->getList($param);
        $type = 0;
        return $this->fetch('index', compact('list','type','templatelist'));
    }
    
    /**
     * 获取未发货的批次
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function batchlist()
    {
        // 当前用户菜单url
        $model = new BatchModel;
        $list = $model->getAllwaitList();
        $type = 0;
        return $this->fetch('index', compact('list','type'));
    }
    
    /**
     * 批次物流模板
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function batchtemplate(){
        $model = new BatchTemplateModel;
        $list = $model->getList();
        return $this->fetch('template', compact('list'));
    }
    
    
    
    /**
     * 获取批次内
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function batchvsinpack($id){
         $Inpack = new Inpack;
         $set = Setting::detail('store')['values']['address_setting'];
         $map = ['batch_id'=>$id,'limitnum'=>100];
         $list = $Inpack->getList('all',$map);
         return $this->fetch('orderlist', compact('list','set'));
    }
    
    /**
     * 获取批次内
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function batchvspack($id){
         $Package = new Package;
         $set = Setting::detail('store')['values'];
         $map = ['batch_id'=>$id,'limitnum'=>100];
         $list = $Package->getList($map);
         $type = 'all';
         $countweight = $Package->getListSum($map);
         return $this->fetch('packlist', compact('list','set','type','countweight'));
    }
    
    /**
     * 获取批次内物流节点
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function templateitem($template_id){
         $BatchTemplateItem = new BatchTemplateItem;
         $list = $BatchTemplateItem->getList(['template_id'=>$template_id]);
         return $this->fetch('batch/template/item', compact('list','template_id'));
    }
    
    /**
     * 添加批次物流模板节点
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function addtemplateitem($template_id)
    {
        $model = new BatchTemplateItem;
        if (!$this->request->isAjax()) {
            return $this->fetch('batch/templateitem/add');
        }
        // 新增记录
        if ($model->add($this->postData('batch'),$template_id)) {
            return $this->renderSuccess('添加成功', url('/store/batch/templateitem'.'/template_id/'.$template_id));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
    public function edittemplateitem($id){
        $detail = BatchTemplateItem::detail($id);
        if (!$this->request->isAjax()) {
            return $this->fetch('batch/templateitem/edit',compact('detail'));
        }
        // 新增记录
        if ($detail->edit($this->postData('batch'))) {
            return $this->renderSuccess('添加成功', url('/store/batch/templateitem'.'/template_id/'.$detail['template_id']));
        }
        return $this->renderError($detail->getError() ?: '添加失败');
    }
    
    
    /**
     * 移除订单的批次号
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function yichu(){
        $param = $this->request->param();
        $Inpack = new Inpack;
        $detial = $Inpack->details($param['id']);
        $res = $detial->save(['batch_id'=>0]);
        return $this->renderSuccess('移除成功');
    }
    
    
    /**
     * 移除订单的批次号
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function yichupack(){
        $param = $this->request->param();
        $Package = new Package;
        $detial = $Package->detail($param['id']);
        $res = $detial->save(['batch_id'=>0,'status'=>2]);
        return $this->renderSuccess('移除成功');
    }
    
     /**
     * 更新批次内的所有订单的物流轨迹
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function logistics(){
        $param = $this->request->param();
        $Inpack = new Inpack;
        $Package = new Package;
        $list = $Inpack->getList('all',['batch_id'=>$param['batch_id'],'limitnum'=>300]);
        $packlist = $Package->getList('all',['batch_id'=>$param['batch_id'],'limitnum'=>300]);
        if(count($list)==0){
            return $this->renderError('该批次下暂无订单');
        }
        if(empty($param['logistics_describe'])){
            return $this->renderError('请输入物流轨迹内容');
        }
        if(!empty($list)){
            foreach($list as $key =>$val){
                Logistics::addInpackLogsPlus($val['order_sn'],$param['logistics_describe'],$param['created_time']);
            }
        }
        
        if(!empty($packlist)){
           foreach($packlist as $key =>$val){
                Logistics::add($val['id'],$param['logistics_describe']);
            } 
        }
        return $this->renderSuccess('添加成功');
    }
    
    /**
     * 批量将集运订单加入到批次中
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function addtobatch(){
        $model = new BatchModel;
        $Inpack = new Inpack;
        $param = $this->request->param();
        $arr = explode(',',$param['selectIds']);
        if(isset($param['batch_id']) && empty($param['batch_id'])){
            return $this->renderError('请选择批次');
        }
        foreach ($arr as $key =>$val){
            $Inpack->where('id',$val)->update(['batch_id'=>$param['batch_id']]);
        }
        return $this->renderSuccess('加入批次成功');
    }

    /**
     * 批量将包裹加入到批次中
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function addpacktobatch(){
        $model = new BatchModel;
        $Package = new Package;
        $param = $this->request->param();
        $arr = explode(',',$param['selectIds']);

        if(isset($param['batch_id']) && empty($param['batch_id'])){
            return $this->renderError('请选择批次');
        }
        foreach ($arr as $key =>$val){
            $Package->where('id',$val)->update(['batch_id'=>$param['batch_id'],'status'=>7]);
        }
        return $this->renderSuccess('加入批次成功');
    }
    
     /**
     * 运送中
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function moving(){
        $model = new BatchModel;
        $list = $model->getList(['status'=>1]);
        $type = 1;
        return $this->fetch('index', compact('list','type'));
    }
    
    /**
     * 已达到
     * @return mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function reached(){
        $model = new BatchModel;
        $list = $model->getList(['status'=>2]);
        $type = 2;
        return $this->fetch('index', compact('list','type'));
    }
    
    /**
     * 添加批次
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function addbatch()
    {
        $model = new Shop;
        $Batch = new BatchModel;
        $list = $model->getAllList();
        $ExpressModel = new ExpressModel();
        $DitchModel = new DitchModel();
        $track = $ExpressModel->getTypeList($type = 1);
        $ditchlist = $DitchModel->getAll();
        $set = SettingModel::getItem('store',$this->getWxappId());
        //选择物流模板
        $BatchTemplateModel = new BatchTemplateModel;
        $templatelist = $BatchTemplateModel->getAllList();
        if (!$this->request->isAjax()) {
            return $this->fetch('add',compact('list','set','track','ditchlist','templatelist'));
        }
        // 新增记录
        if ($Batch->addbatch($this->postData('batch'))) {
            return $this->renderSuccess('添加成功', url('index'));
        }
        return $this->renderError($Batch->getError() ?: '添加失败');
    }
    
    /**
     * 添加批次物流模板
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function addbatchTemplate()
    {
        $model = new BatchTemplateModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('batch/template/add');
        }
        // 新增记录
        if ($model->add($this->postData('batch'))) {
            return $this->renderSuccess('添加成功', url('batch/batchtemplate'));
        }
        return $this->renderError($Batch->getError() ?: '添加失败');
    }
    
    
    /**
     * 编辑批次物流模板
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function editbatchTemplate($template_id)
    {
        $model = new BatchTemplateModel;
        $detail = $model::detail($template_id);
        if (!$this->request->isAjax()) {
             return $this->fetch('batch/template/edit',compact('detail'));
        }
        // 新增记录
        if ($detail->edit($this->postData('batch'))) {
            return $this->renderSuccess('修改成功', url('batch/batchtemplate'));
        }
        return $this->renderError($Batch->getError() ?: '修改失败');
    }
    
    /**
     * 删除批次物流模板
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function deletebatchTemplate($template_id){
      $model = BatchTemplateModel::detail($template_id);
      if (!$model->delete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
    
    
    
    /**
     * 删除批次物流模板
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function deletetemplateitem($id){
      $model = BatchTemplateItem::detail($id);
      if (!$model->delete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
    
    
    /**
     * 编辑批次
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function editbatch($batch_id)
    {
        $model = new Shop;
        $Inpack = new Inpack;
        $Batch = new BatchModel;
        $package = new Package;
        $list = $model->getAllList();
        $set = SettingModel::getItem('store',$this->getWxappId());
        $ExpressModel = new ExpressModel();
        $DitchModel = new DitchModel();
        $track = $ExpressModel->getTypeList($type = 1);
        $ditchlist = $DitchModel->getAll();
        $detail = $Batch::detail($batch_id);
        $BatchTemplateModel = new BatchTemplateModel;
        $templatelist = $BatchTemplateModel->getAllList();
        if (!$this->request->isAjax()) {
             return $this->fetch('edit',compact('list','detail','set','track','ditchlist','templatelist'));
        }
        $param = $this->postData('batch');
        // dump($batch_id);die;
        //将集运单和包裹都设置为已发货状态
        if($detail['status']==1 && $param['status']==1){
            $Inpack->where('batch_id',$batch_id)->update(['status'=>6]);
            $package->where('batch_id',$batch_id)->update(['status'=>9]);
            unset($param['status']);
        }
        //将集运单和包裹都设置为已到货状态
        if($param['status']==2){
            $Inpack->where('batch_id',$batch_id)->update(['status'=>7]);
            $package->where('batch_id',$batch_id)->update(['status'=>11]);
        }
        // 新增记录
        if ($detail->editbatch($param)){
            return $this->renderSuccess('修改成功', url('index'));
        }
        return $this->renderError($Batch->getError() ?: '修改失败');
    }
    
    /**
     * 删除批次
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function deletebatch($batch_id){
      $model = BatchModel::detail($batch_id);
      if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
    
    /**
     * 生成批次号
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function createbatchname()
    {
        $settingDate = SettingModel::getItem('forwarder_setting',$this->getWxappId());
        $a = $b = $c = '';
        if($settingDate['firstword_mode']==1){
            $a = $settingDate['firstword'];
        }
        if($settingDate['ftime_mode']==1){
            $b = date("Ymd",time());
        }
        //0随机 1顺序
        if($settingDate['random']==0){
            $c = rand(10000,99999);
        }else{
            $model = new BatchModel;
            $result = $model->order('batch_id DESC')->find();
            if(empty($result)){
                $result['batch_id'] = 10001;
            }
            $c = $result['batch_id'] + 1;
        }
        $batch = $a.$b.'-'.$c;
        return $this->renderSuccess('获取成功','',$batch);
    }
    
    /**
     * 批次设置
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function setting(){
        $model = new setting();
        if($this->request->isAjax()){
          $postData = $this->postData('batchrule');
          $model->edit('batch',$postData);
          return $this->renderSuccess('修改成功');
        }
        $values = setting::getItem('batch',$this->getWxappId());
       
        return $this->fetch('batch/setting', compact('values'));
    }
    
}