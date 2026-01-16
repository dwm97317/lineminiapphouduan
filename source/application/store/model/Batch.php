<?php

namespace app\store\model;

use app\common\model\Batch as BatchModel;
use app\store\model\BatchTemplateItem;
use think\Session;
/**
 * 商家门店模型
 * Class Shop
 * @package app\store\model\store
 */
class Batch extends BatchModel
{
    /**
     * 获取列表数据
     * @param array $param
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($param = [])
    {
        // 查询列表数据
        // dump($param);die;
        return $this->setListQueryWhere($param)
            ->with(['shop','template'])
            ->where('is_delete',0)
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
    
    /**
     * 获取待发货批次列表数据
     * @param array $param
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getAllwaitList($param = [])
    {
        // 查询列表数据
        // dump(\request()->request());die;
        return $this->setListQueryWhere($param)
             ->with(['shop','template'])
            ->where('is_delete',0)
            ->where('status',0)
            ->select();
    }
    
    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function addbatch($data)
    { 
        if (!$this->validateForm($data)) {
            return false;
        }
        return $this->allowField(true)->save($this->createData($data));
    }
    
    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function editbatch($data)
    { 
        $BatchTemplateItem = new BatchTemplateItem;
        if (!$this->validateForm($data)) {
            return false;
        }
  
        if(isset($data['status']) && $data['status']==1){
            $list = $BatchTemplateItem->getList(['template_id'=>$data['template_id']]);
            $data['last_time'] =  time() + $list[0]['wait_time'] * 3600;
        }
        return $this->allowField(true)->save($this->createData($data));
    }
    
    /**
     * 创建数据
     * @param array $data
     * @return array
     */
    private function createData($data)
    {
        if($data['transfer']==1){
            $data['express'] = $data['tt_number'];
            $data['express_no'] = $data['t_order_sn'];
        }else{
            $data['express'] = $data['t_number'];
        }
        $data['wxapp_id'] = Session::get('yoshop_store')['wxapp']['wxapp_id'];
         
        return $data;
    }
    
    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function validateForm($data)
    {
        if (!isset($data['batch_name']) || empty($data['batch_name'])) {
            $this->error = "批次号不能为空";
            return false;
        }
        if (!isset($data['batch_no']) || empty($data['batch_no'])) {
            $this->error = "装箱单号不能为空";
            return false;
        }
        return true;
    }

    /**
     * 设置列表查询条件
     * @param array $param
     * @return $this
     */
    private function setListQueryWhere($param = [])
    {
        // 查询参数
        $param = array_merge(['is_check' => '', 'search' => '', 'status' => null,], $param);
        is_numeric($param['is_check']) && $param['is_check'] > -1 && $this->where('is_check', '=', (int)$param['is_check']);
        !empty($param['search']) && $this->where('shop_name|linkman|phone', 'like', "%{$param['search']}%");
        !empty($param['shop_id']) && $this->where('shop_id', '=', $param['shop_id']);
        !empty($param['batch_id']) && $this->where('batch_id', '=', $param['batch_id']);
        is_numeric($param['status']) && $this->where('status', '=', (int)$param['status']);
        return $this->order(['create_time' => 'desc']);
    }
    
    public function shop(){
        return $this->belongsTo('app\store\model\store\Shop','shop_id');
    }

    
    public function setDelete(){
         return $this->save(['is_delete' => 1]);
    }
    
}