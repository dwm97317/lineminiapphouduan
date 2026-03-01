<?php

namespace app\store\service\statement;

use app\store\model\Statement;
use app\store\model\Package;
use app\store\service\excel\ExcelService;
use think\Db;

/**
 * 账单管理服务
 * 负责账单的查询、支付、作废等管理功能
 */
class StatementManageService
{
    private $wxappId;
    
    public function __construct()
    {
        // 从Session获取wxapp_id（store模块）
        $session = \think\Session::get('yoshop_store');
        $this->wxappId = $session['wxapp']['wxapp_id'] ?? 10001;
    }
    
    /**
     * 获取账单列表
     * @param array $params 查询参数
     * @return array
     */
    public function getList($params = [])
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;
        
        $query = Statement::alias('s')
            ->field('s.*, u.nickName as member_name')
            ->join('user u', 's.member_id = u.user_id', 'LEFT')
            ->where('s.wxapp_id', $this->wxappId);
        
        // 客户筛选
        if (!empty($params['member_id'])) {
            $query->where('s.member_id', $params['member_id']);
        }
        
        // 支付状态筛选
        if (isset($params['pay_status']) && $params['pay_status'] !== '') {
            $query->where('s.pay_status', $params['pay_status']);
        }
        
        // 账单状态筛选
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('s.status', $params['status']);
        }
        
        // 日期范围筛选
        if (!empty($params['start_date'])) {
            $query->where('s.create_time', '>=', $params['start_date'] . ' 00:00:00');
        }
        if (!empty($params['end_date'])) {
            $query->where('s.create_time', '<=', $params['end_date'] . ' 23:59:59');
        }
        
        // 关键词搜索（账单编号或客户姓名）
        if (!empty($params['keyword'])) {
            $query->where(function($q) use ($params) {
                $q->where('s.statement_no', 'like', '%' . $params['keyword'] . '%')
                  ->whereOr('u.nickName', 'like', '%' . $params['keyword'] . '%');
            });
        }
        
        // 排序
        $query->order('s.create_time', 'desc');
        
        // 分页
        $list = $query->paginate($pageSize, false, ['page' => $page]);
        
        return [
            'list' => $list->items(),
            'total' => $list->total(),
            'page' => $page,
            'page_size' => $pageSize
        ];
    }
    
    /**
     * 获取账单详情
     * @param int $statementId 账单ID
     * @return array
     */
    public function getDetail($statementId)
    {
        $statement = Statement::where('id', $statementId)
            ->where('wxapp_id', $this->wxappId)
            ->find();
        
        if (!$statement) {
            throw new \Exception('账单不存在');
        }
        
        return $statement->getDetailWithPackages();
    }
    
    /**
     * 标记为已支付
     * @param int $statementId 账单ID
     * @param string $remark 支付备注
     * @return array 操作结果
     */
    public function markAsPaid($statementId, $remark = '')
    {
        $statement = Statement::where('id', $statementId)
            ->where('wxapp_id', $this->wxappId)
            ->find();
        
        if (!$statement) {
            throw new \Exception('账单不存在');
        }
        
        if ($statement['pay_status'] == Statement::PAY_STATUS_PAID) {
            throw new \Exception('账单已支付，无需重复操作');
        }
        
        if ($statement['status'] == Statement::STATUS_VOID) {
            throw new \Exception('已作废的账单不能标记为已支付');
        }
        
        Db::startTrans();
        try {
            // 1. 更新账单支付状态
            $statement->save([
                'pay_status' => Statement::PAY_STATUS_PAID,
                'pay_time' => date('Y-m-d H:i:s'),
                'pay_remark' => $remark
            ]);
            
            // 2. 更新关联集运订单支付状态（容错处理）
            $inpacks = Db::name('inpack')
                ->where('statement_id', $statementId)
                ->where('is_delete', 0)
                ->select();
            
            $successCount = 0;
            $failedCount = 0;
            $failedIds = [];
            
            foreach ($inpacks as $inpack) {
                try {
                    Db::name('inpack')
                        ->where('id', $inpack['id'])
                        ->update([
                            'is_pay' => 1,
                            'pay_time' => date('Y-m-d H:i:s')
                        ]);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $failedIds[] = $inpack['id'];
                    
                    // 记录错误日志
                    \think\Log::error('集运订单支付状态更新失败: ' . json_encode([
                        'inpack_id' => $inpack['id'],
                        'statement_id' => $statementId,
                        'error' => $e->getMessage()
                    ]));
                }
            }
            
            Db::commit();
            
            // 记录操作日志
            \think\Log::info('账单标记为已支付: ' . json_encode([
                'statement_id' => $statementId,
                'statement_no' => $statement['statement_no'],
                'success_count' => $successCount,
                'failed_count' => $failedCount
            ]));
            
            return [
                'success' => true,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'failed_ids' => $failedIds
            ];
            
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    
    /**
     * 作废账单
     * @param int $statementId 账单ID
     * @return bool
     */
    public function voidStatement($statementId)
    {
        $statement = Statement::where('id', $statementId)
            ->where('wxapp_id', $this->wxappId)
            ->find();
        
        if (!$statement) {
            throw new \Exception('账单不存在');
        }
        
        if ($statement['pay_status'] == Statement::PAY_STATUS_PAID) {
            throw new \Exception('已支付的账单不能作废');
        }
        
        if ($statement['status'] == Statement::STATUS_VOID) {
            throw new \Exception('账单已作废，无需重复操作');
        }
        
        Db::startTrans();
        try {
            // 1. 更新账单状态为已作废
            $statement->save([
                'status' => Statement::STATUS_VOID
            ]);
            
            // 2. 解除集运订单绑定（恢复待出账状态）
            Db::name('inpack')
                ->where('statement_id', $statementId)
                ->update(['statement_id' => null]);
            
            Db::commit();
            
            // 记录操作日志
            \think\Log::info('账单已作废', [
                'statement_id' => $statementId,
                'statement_no' => $statement['statement_no']
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    
    /**
     * 重新生成Excel
     * @param int $statementId 账单ID
     * @return string Excel文件路径
     */
    public function regenerateExcel($statementId)
    {
        $statement = Statement::where('id', $statementId)
            ->where('wxapp_id', $this->wxappId)
            ->find();
        
        if (!$statement) {
            throw new \Exception('账单不存在');
        }
        
        // 获取订单明细
        $detail = $statement->getDetailWithPackages();
        
        // 获取模板
        $template = \app\store\model\StatementTemplate::getDefault();
        if (!$template) {
            $template = [
                'title' => '集运订单对账单',
                'notice_text' => '请核对账单信息，如有疑问请及时联系客服。'
            ];
        }
        
        // 生成Excel
        $excelService = new ExcelService();
        $excelPath = $excelService->generateStatementExcel(
            $detail['statement'],
            $detail['packages'],
            $template
        );
        
        // 更新Excel路径
        $statement->save(['excel_path' => $excelPath]);
        
        // 记录日志
        \think\Log::info('重新生成Excel', [
            'statement_id' => $statementId,
            'excel_path' => $excelPath
        ]);
        
        return $excelPath;
    }
    
    /**
     * 获取账单统计
     * @param array $params 查询参数
     * @return array
     */
    public function getStatistics($params = [])
    {
        $query = Statement::where('wxapp_id', $this->wxappId);
        
        // 日期范围
        if (!empty($params['start_date'])) {
            $query->where('create_time', '>=', $params['start_date'] . ' 00:00:00');
        }
        if (!empty($params['end_date'])) {
            $query->where('create_time', '<=', $params['end_date'] . ' 23:59:59');
        }
        
        // 客户筛选
        if (!empty($params['member_id'])) {
            $query->where('member_id', $params['member_id']);
        }
        
        // 统计数据
        $total = $query->count();
        $totalAmount = $query->sum('total_amount');
        $paidCount = $query->where('pay_status', Statement::PAY_STATUS_PAID)->count();
        $paidAmount = $query->where('pay_status', Statement::PAY_STATUS_PAID)->sum('total_amount');
        $unpaidCount = $query->where('pay_status', Statement::PAY_STATUS_UNPAID)
            ->where('status', Statement::STATUS_NORMAL)->count();
        $unpaidAmount = $query->where('pay_status', Statement::PAY_STATUS_UNPAID)
            ->where('status', Statement::STATUS_NORMAL)->sum('total_amount');
        $voidCount = $query->where('status', Statement::STATUS_VOID)->count();
        
        return [
            'total_count' => $total,
            'total_amount' => round($totalAmount, 2),
            'paid_count' => $paidCount,
            'paid_amount' => round($paidAmount, 2),
            'unpaid_count' => $unpaidCount,
            'unpaid_amount' => round($unpaidAmount, 2),
            'void_count' => $voidCount
        ];
    }
}
