<?php

namespace app\store\controller\apps\dealer;

use app\store\controller\Controller;
use app\store\model\dealer\Rating as RatingModel;

/**
 * 分销商等级
 * Class Setting
 * @package app\store\controller\apps\dealer
 */
class Rating extends Controller
{
    /**
     * 分销商等级列表
     * @param string $search
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($search = '')
    {
        $model = new RatingModel;
        return $this->fetch('index', [
            'list' => $model->getList($search),
        ]);
    }
    
    /**
     * 添加等级
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function add()
    {
        $model = new RatingModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($model->add($this->postData('grade'))) {
            return $this->renderSuccess('添加成功', url('apps.dealer.rating/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
    /**
     * 编辑会员等级
     * @param $grade_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($rating_id)
    {
        // 会员等级详情
        $model = RatingModel::detail($rating_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 新增记录
        if ($model->edit($this->postData('grade'))) {
            return $this->renderSuccess('更新成功', url('apps.dealer.rating/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    
    /**
     * 删除会员等级
     * @param $grade_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($rating_id)
    {
        // 会员等级详情
        $model = RatingModel::detail($rating_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }


}