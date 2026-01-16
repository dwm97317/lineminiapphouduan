<?php
namespace app\store\controller\setting;
use app\store\controller\Controller;
use app\common\model\Certificate as CertificateModel;
use think\Cache;
/**
 * 汇款凭证
 * Class Certificate
 * @Certificate app\store\controller\setting
 */
class Certificate extends Controller
{
    /**
     * 凭证列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new CertificateModel;
        $scoreType = 0;
        $list = $model->getList($scoreType);
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除凭证
     * @param $express_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        $model = CertificateModel::detail($id);
        if (!$model->remove($id)) {
            $error = $model->getError() ?: '删除失败';
            return $this->renderError($error);
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 更新凭证状态
     * @param $express_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function updateStatus($id)
    {
        $cert_status = $this->postData('certificate')['status'];
        $model = CertificateModel::detail($id);
        // 更新记录
        if ($model->edit($id,$cert_status)) {
            return $this->renderSuccess('更新成功', url('setting.certificate/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    

}