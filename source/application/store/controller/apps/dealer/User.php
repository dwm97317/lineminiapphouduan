<?php

namespace app\store\controller\apps\dealer;

use app\store\controller\Controller;
use app\common\service\qrcode\Poster;
use app\store\model\dealer\User as UserModel;
use app\store\model\dealer\Referee as RefereeModel;
use app\store\model\dealer\Setting as SettingModel;
use app\store\model\dealer\Rating as RatingModel;

/**
 * 分销商管理
 * Class User
 * @package app\store\controller\apps\dealer
 */
class User extends Controller
{
    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
    }
    
    /**
     * 新增分销商下级用户用户
     * @param string $search
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function addreferee(){
        $param = $this->request->param();
        $RefereeModel = new RefereeModel;
        if(!isset($param['data']) || count($param['data'])==0){
            return $this->renderError('请选择该分校商的下级用户');
        }
        foreach ($param['data'] as $val){
           $RefereeModel->createRelation($val['user_id'], $param['user_id']);
        
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 分销商用户列表
     * @param string $search
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($search = '')
    {
        $model = new UserModel;
        $gradeList = RatingModel::getUsableList();
        return $this->fetch('index', [
            'list' => $model->getList($search),
            'gradeList' => $gradeList,
            'basicSetting' => SettingModel::getItem('basic'),
        ]);
    }
    
    /**
     * 修改会员等级
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function grade($user_id)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model->updateGrade($this->postData('grade'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 分销商用户列表
     * @param string $user_id
     * @param int $level
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function fans($user_id, $level = -1)
    {
        $model = new RefereeModel;
        return $this->fetch('fans', [
            'list' => $model->getList($user_id, $level),
            'basicSetting' => SettingModel::getItem('basic'),
        ]);
    }

    /**
     * 编辑分销商
     * @param $dealer_id
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function edit($dealer_id)
    {
        $model = UserModel::detail($dealer_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        if ($model->edit($this->postData('model'))) {
            return $this->renderSuccess('更新成功', url('apps.dealer.user/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除分销商
     * @param $dealer_id
     * @return array|bool
     * @throws \think\exception\DbException
     */
    public function delete($dealer_id)
    {
        $model = UserModel::detail($dealer_id);
        if (!$model->setDelete()) {
            return $this->renderError('删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 分销商二维码
     * @param $dealer_id
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function qrcode($dealer_id)
    {
        $model = UserModel::detail($dealer_id);
        $Qrcode = new Poster($model);
        $this->redirect($Qrcode->getImage());
    }

}