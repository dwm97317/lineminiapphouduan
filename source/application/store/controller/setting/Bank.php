<?php
namespace app\store\controller\setting;
use app\store\controller\Controller;
use app\store\model\Bank as BankModel;
use app\store\model\Setting;
use think\Cache;
/**
 * 银行账号
 * Class Express
 * @package app\store\controller\setting
 */
class Bank extends Controller
{
    /**
     * 物流公司列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new BankModel;
        if ($this->request->isAjax()){
          $postData = $this->postData('bank_setting');
        //   dump($postData);die;
          $model2 = new setting();
          $model2->edit('bank',$postData);
          return $this->renderSuccess('修改成功');
        }
        $list = $model->getList();
        $values = setting::getItem('bank',10001);
     
        return $this->fetch('index', compact('list','values'));
    }

    /**
     * 删除物流公司
     * @param $express_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        $model = BankModel::detail($id);
        if (!$model->remove()) {
            $error = $model->getError() ?: '删除失败';
            return $this->renderError($error);
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加物流公司
     * @return array|mixed
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        $model = new BankModel;
        if ($model->add($this->postData('bank'))) {
            return $this->renderSuccess('添加成功', url('setting.bank/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }


    /**
     * 编辑
     * @param $express_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        // 模板详情
        $model = BankModel::detail($id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('bank'))) {
            return $this->renderSuccess('更新成功', url('setting.bank/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    


    /**
     * 物流公司编码表
     * @return mixed
     */
    public function company()
    {
        return $this->fetch('company');
    }

}