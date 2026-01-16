<?php
namespace app\store\model;
use think\Model;
use app\common\model\LineService as LineServiceModel;

/**
 * LineServiceModel
 * Class LineServiceModel
 * @package app\common\model
 */
class LineService extends LineServiceModel
{
    public function getList($query){
        return $this->setListQueryWhere($query)
            ->alias('a')
            ->paginate(10,false,[
                'query'=>\request()->request()
            ]);
    }
    
    public function getListAll(){
        return $this->select();
    }

    public function setListQueryWhere($query){
        return $this;
    }

    public function add($data){
        // 表单验证
        if (!$this->onValidate($data)) return false;
        
        foreach($data['weight_start'] as $k => $v){
               if(!empty($v)){
                   $spilt_weight[0] = $v;
                   $spilt_weight[1] = $data['weight_max'][$k];
                   $data['rule'][] = [
                      'weight' => $spilt_weight,
                      'weight_price' => $data['weight_price'][$k],
                   ];
               }
         }
        $data['rule'] = json_encode($data['rule']);
        // 保存数据
        $data['wxapp_id'] = self::$wxapp_id;
        if ($this->allowField(true)->save($data)) {
            return true;
        }
        return false;
    }

    public function edit($data)
    {
        // 表单验证
        if (!$this->onValiEdit($data)) return false;
        foreach($data['weight_start'] as $k => $v){
               if(!empty($v)){
                   $spilt_weight[0] = $v;
                   $spilt_weight[1] = $data['weight_max'][$k];
                   $data['rule'][] = [
                      'weight' => $spilt_weight,
                      'weight_price' => $data['weight_price'][$k],
                   ];
               }
         }
        $data['rule'] = json_encode($data['rule']);
        // 保存数据
        if ($this->allowField(true)->save($data)) {
        }
        return true;
    }


    public function details($id){
        return $this->find($id);
    }
    public function deletes($id){
        return $this->find($id)->delete();
    }


    public function onValidate($data){
       
        if (!isset($data['name']) || empty($data['name'])) {
            $this->error = '请输入服务名称';
            return false;
        }
       
        if (!isset($data['weight_start']) || !isset($data['weight_max']) || !isset($data['weight_price'])) {
            $this->error = '请输入服务计费规则';
            return false;
        }
        return true;
    }
    
    public function onValiEdit($data){
        if (!isset($data['name']) || empty($data['name'])) {
            $this->error = '请输入项目名称';
            return false;
        }
        if (!isset($data['weight_start']) || !isset($data['weight_max']) || !isset($data['weight_price'])) {
            $this->error = '请输入服务计费规则';
            return false;
        }

        return true;
    }
}
