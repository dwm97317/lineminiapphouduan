<?php

namespace app\api\controller\bot;

use app\api\model\Package as PackageModel;
use app\common\model\Inpack;
use app\common\exception\BaseException;
use think\Db;

/**
 * Bot 包裹管理控制器
 * Class Package
 * @package app\api\controller\bot
 */
class Package extends \app\api\controller\Controller
{
    /**
     * 创建或更新包裹
     * POST /api/bot/package/create
     * 
     * @return array
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create()
    {
        // 获取请求数据
        $data = $this->postData();
        
        // 验证必要参数
        $this->validateCreateData($data);
        
        $wxappId = $this->wxapp_id;
        
        // 检查包裹编号是否已存在
        $existingPackage = $this->checkPackageExists($data['package_code'], $wxappId);
        
        Db::startTrans();
        try {
            if ($existingPackage) {
                // 更新现有包裹
                $packageId = $existingPackage['id'];
                $this->updatePackage($packageId, $data, $wxappId);
                $action = 'updated';
            } else {
                // 创建新包裹
                $packageId = $this->createPackage($data, $wxappId);
                $action = 'created';
            }
            
            Db::commit();
            
            // 返回成功响应
            return $this->renderSuccess([
                'success' => true,
                'message' => '包裹' . ($action === 'created' ? '创建成功' : '更新成功'),
                'data' => [
                    'package_id' => $packageId,
                    'package_code' => $data['package_code'],
                    'action' => $action,
                ],
            ]);
            
        } catch (\Exception $e) {
            Db::rollback();
            
            \think\Log::record(sprintf(
                '[Bot Package Create] Error: %s, Data: %s',
                $e->getMessage(),
                json_encode($data)
            ), 'error');
            
            throw new BaseException([
                'code' => 500,
                'msg' => '包裹操作失败：' . $e->getMessage(),
            ]);
        }
    }

    /**
     * 查询包裹状态
     * GET /api/bot/package/status
     * 
     * @return array
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function status()
    {
        // 获取参数
        $packageCode = $this->request->param('package_code');
        $orderSn = $this->request->param('order_sn');
        
        if (!$packageCode && !$orderSn) {
            throw new BaseException([
                'code' => 400,
                'msg' => '缺少参数：package_code 或 order_sn',
            ]);
        }
        
        $wxappId = $this->wxapp_id;
        
        // 查询包裹信息
        $package = $this->getPackageInfo($packageCode, $orderSn, $wxappId);
        
        if (!$package) {
            return $this->renderError([
                'success' => false,
                'message' => '包裹不存在',
            ]);
        }
        
        // 查询物流轨迹
        $tracking = $this->getPackageTracking($package['order_sn']);
        
        // 格式化状态
        $statusInfo = $this->formatPackageStatus($package);
        
        return $this->renderSuccess([
            'success' => true,
            'data' => [
                'package_code' => $package['express_num'] ?? $packageCode,
                'order_sn' => $package['order_sn'],
                'status' => $statusInfo['code'],
                'status_text' => $statusInfo['text'],
                'warehouse_status' => $statusInfo['warehouse'],
                'tracking' => $tracking,
                'weight' => $package['weight'] ?? null,
                'volume' => $package['volume'] ?? null,
                'created_time' => $package['created_time'],
                'updated_time' => $package['updated_time'],
            ],
        ]);
    }

    /**
     * 验证创建/更新数据
     * 
     * @param array $data
     * @throws BaseException
     */
    protected function validateCreateData(&$data)
    {
        // 必填字段检查
        $requiredFields = ['package_code', 'customer_id'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new BaseException([
                    'code' => 400,
                    'msg' => "缺少必填字段：{$field}",
                ]);
            }
        }
        
        // 可选字段默认值
        $data['weight'] = isset($data['weight']) ? floatval($data['weight']) : 0;
        $data['volume'] = isset($data['volume']) ? floatval($data['volume']) : 0;
        $data['length'] = isset($data['length']) ? floatval($data['length']) : 0;
        $data['width'] = isset($data['width']) ? floatval($data['width']) : 0;
        $data['height'] = isset($data['height']) ? floatval($data['height']) : 0;
        
        // 状态默认值
        if (!isset($data['status'])) {
            $data['status'] = 1; // 1=待入库
        }
    }

    /**
     * 检查包裹编号是否存在
     * 
     * @param string $packageCode
     * @param int $wxappId
     * @return array|null
     */
    protected function checkPackageExists($packageCode, $wxappId)
    {
        // 在 yoshop_package 表中查找
        $package = PackageModel::useGlobalScope(false)
            ->where(['express_num' => $packageCode, 'wxapp_id' => $wxappId])
            ->find();
        
        return $package ?: null;
    }

    /**
     * 创建新包裹
     * 
     * @param array $data
     * @param int $wxappId
     * @return int 包裹 ID
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function createPackage($data, $wxappId)
    {
        // 根据 customer_id 查找用户
        $customer = $this->findUserByCustomerId($data['customer_id']);
        if (!$customer) {
            throw new BaseException([
                'code' => 404,
                'msg' => '客户不存在：' . $data['customer_id'],
            ]);
        }
        
        // 构建包裹数据
        $packageData = [
            'express_num' => $data['package_code'],
            'member_id' => $customer['user_id'],
            'status' => $data['status'],
            'weight' => $data['weight'],
            'volume' => $data['volume'],
            'length' => $data['length'],
            'width' => $data['width'],
            'height' => $data['height'],
            'remark' => $data['remark'] ?? '',
            'wxapp_id' => $wxappId,
            'created_time' => getTime(),
            'updated_time' => getTime(),
        ];
        
        // 保存包裹
        $packageModel = new PackageModel();
        $packageId = $packageModel->insertGetId($packageData);
        
        // 同时创建入库记录 (yoshop_inpack)
        $this->createInpackRecord($packageId, $packageData, $customer);
        
        return $packageId;
    }

    /**
     * 更新现有包裹
     * 
     * @param int $packageId
     * @param array $data
     * @param int $wxappId
     * @return bool
     * @throws \think\Exception
     */
    protected function updatePackage($packageId, $data, $wxappId)
    {
        $updateData = [
            'weight' => $data['weight'],
            'volume' => $data['volume'],
            'length' => $data['length'],
            'width' => $data['width'],
            'height' => $data['height'],
            'remark' => $data['remark'] ?? '',
            'status' => $data['status'] ?? null,
            'updated_time' => getTime(),
        ];
        
        $packageModel = new PackageModel();
        return $packageModel->where(['id' => $packageId, 'wxapp_id' => $wxappId])->update($updateData);
    }

    /**
     * 创建入库记录
     * 
     * @param int $packageId
     * @param array $packageData
     * @param array $customer
     * @return void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     */
    protected function createInpackRecord($packageId, $packageData, $customer)
    {
        $inpackModel = new Inpack();
        
        $inpackData = [
            'order_sn' => $this->generateOrderSn($packageId),
            'package_id' => $packageId,
            'user_id' => $customer['user_id'],
            't_order_sn' => $packageData['express_num'],
            'status' => $packageData['status'],
            'weight' => $packageData['weight'],
            'volume' => $packageData['volume'],
            'wxapp_id' => $packageData['wxapp_id'],
            'created_time' => getTime(),
        ];
        
        $inpackModel->save($inpackData);
    }

    /**
     * 生成订单号
     * 
     * @param int $packageId
     * @return string
     */
    protected function generateOrderSn($packageId)
    {
        return 'PK' . date('Ymd') . str_pad($packageId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * 根据 Customer ID 查找用户
     * 
     * @param string $customerId
     * @return array|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function findUserByCustomerId($customerId)
    {
        // 复用 Customer 控制器的查找逻辑
        $customerController = new Customer();
        return $customerController->findUserByCustomerId($customerId);
    }

    /**
     * 获取包裹信息
     * 
     * @param string|null $packageCode
     * @param string|null $orderSn
     * @param int $wxappId
     * @return array|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function getPackageInfo($packageCode, $orderSn, $wxappId)
    {
        $packageModel = new PackageModel();
        
        $where = ['wxapp_id' => $wxappId];
        
        if ($packageCode) {
            $where['express_num'] = $packageCode;
        }
        
        if ($orderSn) {
            $where['order_sn'] = $orderSn;
        }
        
        return $packageModel->useGlobalScope(false)
            ->where($where)
            ->order('id', 'DESC')
            ->find();
    }

    /**
     * 获取包裹物流轨迹
     * 
     * @param string $orderSn
     * @return array
     */
    protected function getPackageTracking($orderSn)
    {
        // 查询物流表
        $logistics = Db::name('logistics')
            ->where('order_sn', $orderSn)
            ->order('created_time', 'DESC')
            ->select();
        
        $tracking = [];
        foreach ($logistics as $log) {
            $tracking[] = [
                'time' => $log['created_time'],
                'status' => $log['status'],
                'status_text' => $log['status_cn'],
                'description' => $log['logistics_describe'],
                'location' => $log['location'] ?? '',
            ];
        }
        
        return $tracking;
    }

    /**
     * 格式化包裹状态
     * 
     * @param array $package
     * @return array
     */
    protected function formatPackageStatus($package)
    {
        // 状态映射
        $statusMap = [
            1 => ['未入库', '待入库'],
            2 => ['已入库', '已入库'],
            3 => ['已拣货', '拣货中'],
            4 => ['待打包', '待打包'],
            5 => ['待支付', '待支付'],
            6 => ['已支付', '已支付'],
            7 => ['已分拣', '分拣完成'],
            8 => ['已打包', '已打包'],
            9 => ['已发货', '已发货'],
            10 => ['已收货', '运输中'],
            11 => ['已完成', '已完成'],
        ];
        
        $statusCode = $package['status'] ?? 1;
        $statusText = $statusMap[$statusCode][0] ?? '未知状态';
        $warehouseStatus = $statusMap[$statusCode][1] ?? '未知';
        
        return [
            'code' => $statusCode,
            'text' => $statusText,
            'warehouse' => $warehouseStatus,
        ];
    }
}
