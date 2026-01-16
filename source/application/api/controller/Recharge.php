<?php

namespace app\api\controller;

use app\api\model\Setting as SettingModel;
use app\api\model\recharge\Plan as PlanModel;
use app\api\model\recharge\Order as OrderModel;
use app\api\service\Payment as PaymentService;
use app\common\enum\OrderType as OrderTypeEnum;

/**
 * 用户充值管理
 * Class Recharge
 * @package app\api\controller
 */
class Recharge extends Controller
{
    /**
     * 充值中心
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        // 用户信息
        $userInfo = $this->getUser();
        // 充值套餐列表
        $planList = (new PlanModel)->getList();
        // 充值设置
        $setting = SettingModel::getItem('recharge');
        return $this->renderSuccess(compact('userInfo', 'planList', 'setting'));
    }

    /**
     * 确认充值
     * @param null $planId
     * @param int $customMoney
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function submitPlus($planId = null, $customMoney = 0)
    {
        // 用户信息
        $userInfo = $this->getUser();
        $paytype = $this->postData('paytype')[0];  //支付类型
        $client = $this->postData('client')[0];
        
        // 生成充值订单
        $model = new OrderModel;
        if (!$model->createOrder($userInfo, $planId, $customMoney)) {
            return $this->renderError($model->getError() ?: '充值失败');
        }
        
        
        switch ($paytype) {
            case '1':
                // 构建微信支付
                $payment = PaymentService::wechat(
                    $userInfo,
                    $model['order_id'],
                    $model['order_no'],
                    $model['pay_price'],
                    OrderTypeEnum::RECHARGE
                );
                break;
                
            case '3':
                //构建汉特支付
                if($model['pay_price'] < 0.1){
                    return $this->renderError('充值金额不能低于0.1');;
                }
                $payment = PaymentService::Hantepay(
                    $userInfo,
                    $model['order_id'],
                    $model['order_no'],
                    $model['pay_price'],
                    OrderTypeEnum::RECHARGE
                );
                break;
            
            default:
                // code...
                break;
        }
        
        // 充值状态提醒
        $message = ['success' => '充值成功', 'error' => '订单未支付'];
        return $this->renderSuccess(compact('payment', 'message'), $message);
    }
    
    /**
     * 确认充值新版本
     * @param null $planId
     * @param int $customMoney
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function newRechargesubmit($planId = null, $customMoney = 0)
    {
        // 用户信息
        $userInfo = $this->getUser();
        $paytype = $this->postData('paytype')[0];  //支付类型
        $client = $this->postData('client')[0];
        // 生成充值订单
        $model = new OrderModel;
        if (!$model->createOrder($userInfo, $planId, $customMoney)) {
            return $this->renderError($model->getError() ?: '充值失败');
        }
        switch ($paytype) {
            case '20':
                // 构建微信支付
                $payment = PaymentService::wechat(
                    $userInfo,
                    $model['order_id'],
                    $model['order_no'],
                    $model['pay_price'],
                    OrderTypeEnum::RECHARGE
                );
                break;
                
            case '30':
                //构建汉特支付
                if($model['pay_price'] < 0.1){
                    return $this->renderError('充值金额不能低于0.1');;
                }
                $payment = PaymentService::Hantepay(
                    $userInfo,
                    $model['order_id'],
                    $model['order_no'],
                    $model['pay_price'],
                    OrderTypeEnum::RECHARGE
                );
                break;
            
            default:
                // code...
                break;
        }
        
        // 充值状态提醒
        $message = ['success' => '充值成功', 'error' => '订单未支付'];
        return $this->renderSuccess(compact('payment', 'message'), $message);
    }

    /**
     * 确认充值
     * @param null $planId
     * @param int $customMoney
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function submit($planId = null, $customMoney = 0)
    {
        // 用户信息
        $userInfo = $this->getUser();
        // $paytype = $this->postData('paytype')[0];  //支付类型
        // 生成充值订单
        $model = new OrderModel;
        if (!$model->createOrder($userInfo, $planId, $customMoney)) {
            return $this->renderError($model->getError() ?: '充值失败');
        }
        
         // 构建微信支付
        $payment = PaymentService::wechat(
            $userInfo,
            $model['order_id'],
            $model['order_no'],
            $model['pay_price'],
            OrderTypeEnum::RECHARGE
        );
        
        // 充值状态提醒
        $message = ['success' => '充值成功', 'error' => '订单未支付'];
        return $this->renderSuccess(compact('payment', 'message'), $message);
    }

    /**
     * 转账充值申请 (Manual Transfer Recharge)
     * 使用现有的Certificate（汇款凭证）系统
     * @return array
     * @throws \app\common\exception\BaseException
     */
    public function apply()
    {
        // 获取用户信息
        $userInfo = $this->getUser();
        
        // 获取POST数据
        $data = $this->postData();
        
        // 验证必填字段
        if (empty($data['transfer_date'])) {
            return $this->renderError('请选择转账日期');
        }
        if (empty($data['transfer_time'])) {
            return $this->renderError('请选择转账时间');
        }
        if (empty($data['amount']) || $data['amount'] <= 0) {
            return $this->renderError('请输入有效的充值金额');
        }
        if (empty($data['screenshots']) || !is_array($data['screenshots'])) {
            return $this->renderError('请上传转账截图');
        }
        
        // 获取存储配置
        $storageConfig = SettingModel::getItem('storage', $this->wxapp_id);
        
        // 处理截图 - 使用StorageDriver上传到云端
        $imageIds = [];
        
        foreach ($data['screenshots'] as $index => $base64Image) {
            // 解析Base64图片
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                $imageType = strtolower($type[1]);
                
                // 验证图片类型
                if (!in_array($imageType, ['jpg', 'jpeg', 'png', 'gif'])) {
                    return $this->renderError('不支持的图片格式');
                }
                
                // 解码Base64
                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    return $this->renderError('图片数据无效');
                }
                
                // 创建临时文件
                $tempPath = 'uploads/' . time() . rand(10000, 99999) . '_' . $index . '.' . $imageType;
                if (!file_put_contents($tempPath, $imageData)) {
                    return $this->renderError('临时文件创建失败');
                }
                
                try {
                    // 使用StorageDriver上传到云端
                    $StorageDriver = new \app\common\library\storage\Driver($storageConfig);
                    $StorageDriver->setUploadFileByReal($tempPath);
                    
                    if (!$StorageDriver->put()) {
                        // 删除临时文件
                        @unlink($tempPath);
                        return $this->renderError('图片上传失败: ' . $StorageDriver->getError());
                    }
                    
                    // 获取上传后的文件信息
                    $fileName = $StorageDriver->getFileName();
                    $fileInfo = $StorageDriver->getFileInfo();
                    
                    // 删除临时文件
                    @unlink($tempPath);
                    
                    // 添加文件库记录
                    $uploadFile = $this->addUploadFile($fileName, $fileInfo, 'image');
                    if ($uploadFile && $uploadFile->file_id) {
                        $imageIds[] = $uploadFile->file_id;
                    }
                    
                } catch (\Exception $e) {
                    // 删除临时文件
                    @unlink($tempPath);
                    return $this->renderError('图片上传异常: ' . $e->getMessage());
                }
                
            } else {
                return $this->renderError('无效的图片格式');
            }
        }
        
        if (empty($imageIds)) {
            return $this->renderError('图片上传失败,请重试');
        }
        
        // 组合日期和时间
        $certDate = $data['transfer_date'] . ' ' . $data['transfer_time'];
        
        // 使用Certificate模型保存汇款凭证
        $certificateData = [
            'user_id' => $userInfo['user_id'],
            'order_sn' => '', // 充值不关联订单
            'amount' => $data['amount'],
            'bank_name' => $data['remarks'] ?? '线上转账', // 使用备注作为银行名称
            'coin_type' => 2, // 2=泰铢 (根据cert_type定义)
            'dates' => $certDate,
            'imageIds' => $imageIds,
        ];
        
        // 使用Certificate模型的add方法
        $certificateModel = new \app\api\model\Certificate();
        if ($certificateModel->add($certificateData)) {
            return $this->renderSuccess([
                'message' => '充值申请提交成功，请等待审核'
            ], '提交成功');
        } else {
            return $this->renderError('提交失败，请重试');
        }
    }
    
    /**
     * 添加文件库上传记录
     * @param $fileName
     * @param $fileInfo
     * @param $fileType
     * @return \app\api\model\UploadFile
     */
    private function addUploadFile($fileName, $fileInfo, $fileType)
    {
        // 存储引擎
        $storageConfig = SettingModel::getItem('storage', $this->wxapp_id);
        $storage = $storageConfig['default'];
        // 存储域名
        $fileUrl = isset($storageConfig['engine'][$storage]['domain'])
            ? $storageConfig['engine'][$storage]['domain'] : '';
        
        // 添加文件库记录
        $model = new \app\api\model\UploadFile();
        $model->add([
            'storage' => $storage,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
            'file_size' => $fileInfo['size'],
            'file_type' => $fileType,
            'extension' => pathinfo($fileInfo['name'], PATHINFO_EXTENSION),
            'is_user' => 1
        ]);
        return $model;
    }

}