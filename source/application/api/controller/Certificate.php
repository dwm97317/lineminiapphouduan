<?php

namespace app\api\controller;
use app\api\model\Certificate as CertificateModel;
/**
 * 支付凭证控制器
 * Class Comment
 * @package app\api\controller
 */
class Certificate extends Controller
{
    /**
     *价列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        $model = new CertificateModel;
        $scoreType = input('cur');
        $list = $model->getList($scoreType);
        return $this->renderSuccess(compact('list'));
    }
    
    /**
     * 创建
     * */
    public function create(){
        // 验证用户
       
        $this->user = $this->getUser();
        if (!$this->user){
            return $this->renderError('请先登录');
        }
        $model = new CertificateModel;
        $post = $this->postData();
        $post['user_id'] = ($this->user)['user_id'];
        if (!$model->add($post)){
            return $this->renderError($model->getError() ?: '提交失败');
        }
        return $this->renderSuccess('提交成功');
    }
}