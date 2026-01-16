<?php
namespace app\store\model;
use think\Model;
use app\common\model\SendPreOrder as SendPreOrderModel;
/**
 * 线路模型
 * Class Delivery
 * @package app\common\model
 */
class SendPreOrder extends SendPreOrderModel
{
    
    public function getList($query){
        return $this->setListQueryWhere($query)
        ->alias('a')->order('created_time DESC')
        ->paginate(10,false,[
            'query'=>\request()->request()
        ]);
    }

    public function setListQueryWhere($query){
        return $this;
    }

    public function details($id){
        return $this->find($id);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function edit($data)
    {
        // 表单验证
        if (!$this->onValidate($data)) return false;
        // 保存数据
        if ($this->allowField(true)->save($data)) {
            return $this->createDeliveryRule($data['rule']);
        }
        return false;
    }

    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function onValidate($data)
    {
        if (!isset($data['name']) || empty($data['name'])) {
            $this->error = '请输入线路名称';
            return false;
        }
        if ($data['free_mode'] == 1){
            if (empty($data['weight'])){
               $this->error = '请完善计费规则';
               return false;
            } 
        }
        if ($data['free_mode'] == 2){
            
        }
        return true;
    }

    /**
     * 删除记录
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        return $this->delete();
    }

    public function getSendOrderList($id){
      $order = $this->where(['send_id'=>$id])->find();
      $orderPackids = explode(',',$order['pack_ids']);
      $item = (new Package())->whereIn('id',$orderPackids)->select();
      $order['item'] = $item;
      return $order;   
  }
}
