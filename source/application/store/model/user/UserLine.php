<?php

namespace app\store\model\user;

use app\common\model\UserLine as UserLineModel;

/**
 * 用户路线折扣模型
 * Class PointsLog
 * @package app\store\model\user
 */
class UserLine extends UserLineModel
{
    /**
     * 获取积分明细列表
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($query = [])
    {
        // 设置查询条件
        !empty($query) && $this->setQueryWhere($query);
        // 获取列表数据
        return $this->alias('li')
            ->join('user', 'user.user_id = li.user_id')
            ->join('line', 'line.id = li.line_id')
            ->field('li.id,li.*,user.*,line.name')
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 设置查询条件
     * @param $query
     */
    private function setQueryWhere($query)
    {
        // 设置默认的检索数据
        $params = $this->setQueryDefaultValue($query, [
            'user_id' => 0,
            'search' => '',
        ]);
        // 用户ID
        $params['user_id'] > 0 && $this->where('li.user_id', '=', $params['user_id']);
        // 用户昵称
        !empty($params['search']) && $this->where('user.nickName', 'like', "%{$params['search']}%");
    }

    /**
     * 添加会员折扣
     * @param $query
     */
    public function addUserLineDiscount($data){
        $data['line_id'] = $data['id'];
        unset($data['id']);
        $data['wxapp_id'] = self::$wxapp_id;
        if(empty($data['line_id']) || $data['line_id']==0){
            $this->error = '请选择路线';
            return false;
        }
        if(empty($data['discount']) || $data['discount']>1 || $data['discount']<=0){
            $this->error = '折扣值不合法';
            return false;
        }
        $map = ['user_id' => $data['user_id'] ,'line_id'=>$data['line_id']];
        $disres = $this->where($map)->find();
        if($disres){
           $this->error = '该用户已经设置过此路线的折扣';
           return false;
        }
        $res = $this->save($data);
        return true;
    }

}