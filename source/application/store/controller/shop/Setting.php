<?php
namespace app\store\controller\shop;

use app\store\controller\Controller;
use app\store\model\store\shop\Setting as SettingModel;

/**
 * 加盟设置
 * Class Setting
 * @package app\store\controller\apps\dealer
 */
class Setting extends Controller
{
    /**
     * 加盟设置
     * @return array|bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        if (!$this->request->isAjax()) {
            $data = SettingModel::getAll();
            return $this->fetch('index', compact('data'));
        }
        $model = new SettingModel;
        if ($model->edit($this->postData('setting'))) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 加盟海报
     * @return array|mixed
     * @throws \think\exception\PDOException
     */
    public function qrcode()
    {
        if (!$this->request->isAjax()) {
            $data = SettingModel::getItem('qrcode');
            return $this->fetch('qrcode', [
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE)
            ]);
        }
        $model = new SettingModel;
        if ($model->edit(['qrcode' => $this->postData('qrcode')])) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}