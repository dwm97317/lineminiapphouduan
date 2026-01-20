<?php
namespace app\store\controller\market;

use app\store\controller\Controller;
use app\common\model\Setting as SettingModel;

/**
 * 营销设置控制器
 * Class Marketing
 * @package app\store\controller\market
 */
class Marketing extends Controller
{
    /**
     * 营销设置
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            if ($this->update('marketing')) {
                return $this->renderSuccess('更新成功', url('market.marketing/index'));
            }
            return $this->renderError('更新失败');
        }
        return $this->fetch('index', [
            'values' => SettingModel::getItem('marketing'),
        ]);
    }

    /**
     * 更新设置
     * @param $key
     * @return bool
     */
    private function update($key)
    {
        return $this->model->edit($key, $this->postData('marketing'));
    }
}
