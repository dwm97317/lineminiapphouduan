<?php

namespace app\store\model;

use app\common\model\Comment as CommentModel;

/**
 * 商品评价模型
 * Class Comment
 * @package app\store\model
 */
class Comment extends CommentModel
{
    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }
    
     /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User')->field(['user_id', 'nickName', 'avatarUrl']);
    }
    
     /**
     * 关联包裹表
     * @return \think\model\relation\BelongsTo
     */
    public function Package()
    {
        return $this->belongsTo('Package','order_id')->field(['id', 'order_sn', 'express_num']);
    }
    
    public function getCommentById($id){
        $map = ['order_id'=>$id,'is_delete'=>0];
        return $this->where($map)->with(['user'])
            ->order(['create_time' => 'desc'])->
            paginate(15, false, [
                'query' => request()->request()
            ]);;
    }

    public function details($id){
       return $this->find($id);
    }
    
    /**
     * 获取评价总数量
     * @return int|string
     */
    public function getCommentTotal()
    {
        return $this->where(['is_delete' => 0])->count();
    }

}