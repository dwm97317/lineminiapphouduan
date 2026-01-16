<?php

namespace app\api\controller;
use app\common\model\Inpack;
use app\api\model\Comment as CommentModel;
use app\api\model\Package;
/**
 * 商品评价控制器
 * Class Comment
 * @package app\api\controller
 */
class Comment extends Controller
{
    /**
     * 商品评价列表
     * @param $goods_id
     * @param int $scoreType
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($goods_id, $scoreType = -1)
    {
        $model = new CommentModel;
        $list = $model->getGoodsCommentList($goods_id, $scoreType);
        $total = $model->getTotal($goods_id);
        return $this->renderSuccess(compact('list', 'total'));
    }
    
    /**
     * 新增评论
     * */
    public function create(){
        // 验证用户
        $this->user = $this->getUser();
        if (!$this->user){
            return $this->renderError('请先登录');
        }
        $post = $this->postData();
        if (!isset($post['order_id'])){
            return $this->renderError('请选择要评论的ID');
        }
        $order = (new Inpack())->find($post['order_id']);
        if (!$order){
            return $this->renderError('订单数据错误');
        }
        if (!isset($post['content'])){
            return $this->renderError('请输入你评论的内容');
        }
        $model = new CommentModel;
        if (!$model->addForPack($order,$post)){
            return $this->renderError($model->getError() ?: '评论创建失败');
        }
        return $this->renderSuccess('评论创建成功');
    }
    
    // 热门评论
    public function hotComment(){
        $model = new CommentModel;
        $list = $model->getHotCommentList();
        foreach ($list as $k => &$v){
            $v['order_sn'] = func_substr_replace($v['inpack']['order_sn'],'*',5,8);
            $v['score'] = json_decode($v['score'],true);
        }
        return $this->renderSuccess($list);
    }
    
    // 热门评论
    public function hotMoreComment(){
        $model = new CommentModel;
        $list = $model->getHotOrderCommentList();
        foreach ($list as $k => &$v){
            $v['order_sn'] = func_substr_replace($v['inpack']['order_sn'],'*',5,8);
            $v['score'] = json_decode($v['score'],true);
        }
        return $this->renderSuccess($list);
    }
    
    // 我的评论
    public function OrderComment(){
        $post = $this->postData();
        if (!$post['order_id']){
           return $this->renderError('请选择订单');  
        }
        $order = (new Package())->field('id,line_id')->find($post['order_id']);
        $line_id = $order['line_id'];
        $model = new CommentModel;
          // 验证用户
        $this->user = $this->getUser();
        $list =$model->getOrderCommentDetails($line_id,$this->user['user_id']);
        foreach ($list as $k => $v){
            $list[$k]['order_sn'] = func_substr_replace($v['inpack']['order_sn'],'*',5,8);
            $list[$k]['score'] = json_decode($v['score'],true);
        }
        return $this->renderSuccess($list);
    }
}