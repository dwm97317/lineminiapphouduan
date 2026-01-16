<?php

namespace app\store\model\dealer;

use app\common\model\dealer\Rating as RatingModel;
use app\common\service\Message as MessageService;
use app\common\enum\dealer\ApplyStatus as ApplyStatusEnum;
use app\store\model\dealer\User as UserModel;
/**
 * 分销商等级
 * Class Apply
 * @package app\store\model\dealer
 */
class Rating extends RatingModel
{
    /**
     * 分销商等级
     * @param string $search
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($search = '')
    {
        // 构建查询规则
        $this->where('is_delete',0)
        ->order(['create_time' => 'desc']);
        // 查询条件
        !empty($search) && $this->where('user.nickName|apply.real_name|apply.mobile', 'like', "%$search%");
        // 获取列表数据
        return $this->paginate(15, false, [
            'query' => \request()->request()
        ]);
    }
    
    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        // 判断该等级下是否存在会员
        if (UserModel::checkExistByGradeId($this['rating_id'])) {
            $this->error = '该会员等级下存在用户，不允许删除';
            return false;
        }
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        $data['setting'] = json_encode($data['setting']);  
        $data['upgrade'] = json_encode($data['upgrade']);  
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }
    
    /**
     * 编辑记录
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
        if (!$this->validateForm($data, 'edit')) {
            return false;
        }
        $data['setting'] = json_encode($data['setting']);  
        $data['upgrade'] = json_encode($data['upgrade']);  
        return $this->allowField(true)->save($data) !== false;
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
            if (self::checkExistByWeight($data['weight'])) {
                $this->error = '等级权重已存在';
                return false;
            }
        } elseif ($scene === 'edit') {
            // 需要判断等级权重是否已存在
            if (self::checkExistByWeight($data['weight'], $this['rating_id'])) {
                $this->error = '等级权重已存在';
                return false;
            }
        }
        return true;
    }

}