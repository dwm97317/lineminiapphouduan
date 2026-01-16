<?php
namespace app\store\controller\apps\sharing;
use app\store\controller\Controller;
use app\store\model\sharing\Setting as SettingModel;

/**
 * 拼单设置控制器
 * Class Active
 * @package app\store\controller\apps\sharing
 */
class Setting extends Controller
{
    
    public function basic(){
        if (!$this->request->isAjax()) {
            $detail = SettingModel::getItem('sharp');
            return $this->fetch('basic', [
                    'data' => $detail
            ]);
        }
        $model = new SettingModel;
        if ($model->edit($this->postData('share'))) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}