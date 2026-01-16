<?php
namespace app\store\model\sharing;
use app\common\model\sharing\SharingOrder as SharingOrderModel;
use app\store\model\store\Shop as ShopModel;


class SharingOrder extends SharingOrderModel {
    
    public function getList($query){
        return $this
            ->setWhere($query)
            ->with(['country','User','storage','line','address'])
            ->order('create_time','desc')
            ->paginate(15,false, [
                'query' => \request()->request()
            ]);
    }
    
    public function edit($data)
    {
        if (!$this->validateForm($data, 'edit')) {
            return false;
        }
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        return $this->allowField(true)->update($data) !== false;
    }
    
    public function add($data)
    {
        if (!$this->validateForm($data, 'add')) {
            return false;
        }
        $ndata['title'] = $data['title'];
        $ndata['order_sn'] = date("YmdHis",time()).rand(00000,99999);
        $ndata['storage_id'] = $data['storage_id'];
        $ndata['line_id'] = $data['line_id'];
        $ndata['member_id'] = $data['member_id'];
        $ndata['country_id'] = $data['country_id'];
        $ndata['predict_weight'] = $data['predict_weight'];
        $ndata['min_weight'] = $data['min_weight'];
        $ndata['max_people'] = $data['max_people'];
        $ndata['address_id'] = isset($data['address_id'])?$data['address_id']:0;
        $ndata['group_leader_remark'] = $data['group_leader_remark'];
        $ndata['start_time'] = strtotime($data['start_time']);
        $ndata['end_time'] = strtotime($data['end_time']);
        $ndata['is_hot'] = $data['is_hot'];
        $ndata['is_recommend'] = $data['is_recommend'];
        $ndata['is_verify'] = $data['is_verify'];
        $ndata['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($ndata) !== false;
    }
    
    
    /**
     * 表单验证
     * @param $data
     * @param string $scene
     * @return bool
     */
    private function validateForm($data, $scene = 'add')
    {
        if ($scene === 'add') {
            // 需要判断等级权重是否已存在
            if (empty($data['title'])) {
                $this->error = '请输入拼团名称';
                return false;
            }
        } elseif ($scene === 'edit') {
            // 需要判断等级权重是否已存在
            if (empty($data['title'])) {
                $this->error = '请输入拼团名称';
                return false;
            }
        }
        return true;
    }
    
  
    
    public function setWhere($query){
        if (isset($query['user_id']) && $query['user_id']){
            $this->where(['member_id'=>$query['user_id']]);
        }
        if (isset($query['order_sn']) && $query['order_sn']){
            $this->where(['order_sn'=>$query['order_sn']]);
        }
        if (isset($query['status']) && $query['status']){
            $this->where(['status'=>$query['status']]);
        }
        if (isset($query['extract_shop_id']) && $query['extract_shop_id'] != '-1'){
            $this->where(['storage_id'=>$query['extract_shop_id']]);
        }
         // 起始时间
        !empty($params['start_time']) && $this->where('order.create_time', '>=', strtotime($params['start_time']));
        // 截止时间
        !empty($params['end_time']) && $this->where('order.create_time', '<', strtotime($params['end_time']) + 86400);
        return $this;
    }
    
}  

?>