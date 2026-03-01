<?php

namespace app\store\service\statement;

use app\store\model\Statement;
use app\store\model\Inpack;
use app\store\model\FinanceConfig;
use app\store\service\excel\ExcelService;
use think\Db;

/**
 * 账单生成服务
 * 负责账单的创建和Excel生成
 */
class StatementService
{
    private $priceCalculator;
    private $wxappId;
    
    public function __construct()
    {
        $this->priceCalculator = new PriceCalculator();
        // 从Session获取wxapp_id（store模块）
        $session = \think\Session::get('yoshop_store');
        $this->wxappId = $session['wxapp']['wxapp_id'] ?? 10001;
    }
    
    /**
     * 创建账单（核心方法）
     * @param array $inpackIds 集运订单ID列表
     * @param int $memberId 客户ID
     * @return array 账单信息
     * @throws \Exception
     */
    public function createStatement($inpackIds, $memberId)
    {
        \think\Log::info('=== 开始创建账单 ===');
        
        // 1. 验证参数
        if (empty($inpackIds)) {
            throw new \Exception('请选择集运订单');
        }
        
        if (empty($memberId)) {
            throw new \Exception('客户ID不能为空');
        }
        
        \think\Log::info('步骤1: 参数验证通过 - inpack_ids=' . json_encode($inpackIds) . ', member_id=' . $memberId);
        
        // 2. 开启事务
        Db::startTrans();
        \think\Log::info('步骤2: 事务已开启');
        
        try {
            // 3. 验证和锁定集运订单
            \think\Log::info('步骤3: 开始验证和锁定集运订单');
            $inpacks = $this->validateAndLockInpacks($inpackIds, $memberId);
            \think\Log::info('步骤3: 验证集运订单成功 - count=' . count($inpacks));
            
            // 4. 获取计价配置
            \think\Log::info('步骤4: 开始获取计价配置');
            $priceConfig = FinanceConfig::getEffectivePrice($memberId);
            \think\Log::info('步骤4: 获取计价配置成功 - type=' . ($priceConfig['price_type'] ?? 'unknown'));
            
            // 5. 计算金额（每个集运订单单独计算）
            \think\Log::info('步骤5: 开始计算金额');
            $inpacks = $this->priceCalculator->calculate($inpacks, $priceConfig);
            \think\Log::info('步骤5: 计算金额成功 - count=' . count($inpacks));
            
            // 6. 创建账单记录
            \think\Log::info('步骤6: 开始创建账单记录');
            $statement = $this->createStatementRecord($inpacks, $memberId);
            \think\Log::info('步骤6: 创建账单记录成功 - id=' . $statement['id']);
            
            // 7. 绑定集运订单（保存单价和金额）
            \think\Log::info('步骤7: 开始绑定集运订单');
            $this->bindInpacks($inpackIds, $statement['id'], $inpacks);
            \think\Log::info('步骤7: 绑定集运订单成功');
            
            // 8. 提交事务
            Db::commit();
            \think\Log::info('步骤8: 事务已提交');
            
            // 9. 生成Excel（事务外，避免阻塞）
            try {
                \think\Log::info('步骤9: 开始生成Excel');
                
                // 检查 PhpSpreadsheet 是否可用
                if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                    \think\Log::warning('步骤9: PhpSpreadsheet 未安装，跳过 Excel 生成');
                    $statement['excel_path'] = '';
                } else {
                    \think\Log::info('步骤9: PhpSpreadsheet 可用，开始生成');
                    
                    $excelPath = $this->generateExcel($statement, $inpacks);
                    
                    \think\Log::info('步骤9: Excel 文件已生成 - path=' . $excelPath);
                    
                    // 更新Excel路径
                    Statement::where('id', $statement['id'])
                        ->update(['excel_path' => $excelPath]);
                    
                    $statement['excel_path'] = $excelPath;
                    \think\Log::info('步骤9: Excel路径已更新到数据库');
                }
                
            } catch (\Exception $e) {
                // Excel生成失败不影响账单创建
                \think\Log::error('步骤9: Excel生成失败 - ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
                \think\Log::error('步骤9: 堆栈跟踪 - ' . $e->getTraceAsString());
                $statement['excel_path'] = '';
            }
            
            \think\Log::info('=== 账单创建完成 ===');
            
            return $statement;
            
        } catch (\Exception $e) {
            Db::rollback();
            \think\Log::error('=== 账单创建失败 === ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            
            // 返回更详细的错误信息
            throw new \Exception('账单生成失败: ' . $e->getMessage() . ' (文件: ' . basename($e->getFile()) . ' 行: ' . $e->getLine() . ')');
        }
    }
    
    /**
     * 验证和锁定集运订单
     * @param array $inpackIds
     * @param int $memberId
     * @return array
     * @throws \Exception
     */
    private function validateAndLockInpacks($inpackIds, $memberId)
    {
        \think\Log::info('validateAndLockInpacks: 开始查询集运订单');
        
        // 使用悲观锁查询集运订单 - 查询需要的字段（包含created_time用于发货日期）
        $inpacks = Db::name('inpack')
            ->field('id, order_sn, member_id, statement_id, cale_weight, weight, t_order_sn, line_id, created_time')
            ->where('id', 'in', $inpackIds)
            ->where('is_delete', 0)
            ->lock(true)  // SELECT ... FOR UPDATE
            ->select();
        
        \think\Log::info('validateAndLockInpacks: 查询完成 - count=' . count($inpacks));
        
        if (empty($inpacks)) {
            throw new \Exception('未找到有效的集运订单');
        }
        
        // 验证订单数量
        if (count($inpacks) != count($inpackIds)) {
            throw new \Exception('部分集运订单不存在或已删除');
        }
        
        \think\Log::info('validateAndLockInpacks: 开始验证客户一致性');
        
        // 验证客户一致性
        foreach ($inpacks as $inpack) {
            if ($inpack['member_id'] != $memberId) {
                throw new \Exception('集运订单 ' . $inpack['order_sn'] . ' 不属于该客户');
            }
        }
        
        \think\Log::info('validateAndLockInpacks: 开始验证是否已出账');
        
        // 验证是否已出账
        foreach ($inpacks as $inpack) {
            if (!empty($inpack['statement_id'])) {
                throw new \Exception('集运订单 ' . $inpack['order_sn'] . ' 已出账，不能重复生成');
            }
        }
        
        \think\Log::info('validateAndLockInpacks: 验证通过');
        return $inpacks;
    }
    
    /**
     * 创建账单记录
     * @param array $inpacks
     * @param int $memberId
     * @return array
     * @throws \Exception
     */
    private function createStatementRecord($inpacks, $memberId)
    {
        // 计算总金额和总重量
        $totalAmount = 0.00;
        $totalWeight = 0.00;
        foreach ($inpacks as $inpack) {
            $totalAmount += floatval($inpack['calculated_amount'] ?? 0);
            // 使用计费重量，如果没有则使用实际重量
            $weight = floatval($inpack['cale_weight'] ?? $inpack['weight'] ?? 0);
            $totalWeight += $weight;
        }
        
        // 生成账单编号（带锁）
        $statementNo = $this->generateStatementNo();
        
        $now = date('Y-m-d H:i:s');
        
        // 准备数据 - 使用严格类型转换
        $data = [
            'statement_no' => (string)$statementNo,
            'member_id' => (int)$memberId,
            'member_name' => '',
            'total_packages' => (int)count($inpacks),
            'total_weight' => (float)$totalWeight,
            'total_amount' => (float)$totalAmount,
            'status' => (int)Statement::STATUS_NORMAL,
            'pay_status' => (int)Statement::PAY_STATUS_UNPAID,
            'wxapp_id' => (int)$this->wxappId,
            'create_time' => $now,
            'update_time' => $now
        ];
        
        // 调试日志
        \think\Log::info('准备插入账单数据: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
        
        // 创建账单
        try {
            $statementId = Db::name('statement')->insertGetId($data);
        } catch (\Exception $e) {
            \think\Log::error('插入账单失败: ' . $e->getMessage() . ' | Data: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
            throw $e;
        }
        
        // 返回完整的账单数据（包含所有字段）
        $result = [
            'id' => $statementId,
            'statement_no' => $statementNo,
            'member_id' => $memberId,
            'member_name' => '',
            'total_packages' => (int)count($inpacks),
            'total_weight' => (float)$totalWeight,
            'total_amount' => $totalAmount,
            'status' => Statement::STATUS_NORMAL,
            'pay_status' => Statement::PAY_STATUS_UNPAID,
            'create_time' => $now
        ];
        
        \think\Log::info('createStatementRecord 返回数据: ' . json_encode($result, JSON_UNESCAPED_UNICODE));
        
        return $result;
    }
    
    /**
     * 绑定集运订单
     * @param array $inpackIds
     * @param int $statementId
     * @param array $inpacks 计算后的集运订单数据（包含单价和金额）
     */
    private function bindInpacks($inpackIds, $statementId, $inpacks = [])
    {
        // 如果有计算后的数据，逐个更新（保存单价和金额）
        if (!empty($inpacks)) {
            foreach ($inpacks as $inpack) {
                Db::name('inpack')
                    ->where('id', $inpack['id'])
                    ->update([
                        'statement_id' => $statementId,
                        'unit_price' => $inpack['unit_price'] ?? 0,
                        'calculated_amount' => $inpack['calculated_amount'] ?? 0
                    ]);
            }
        } else {
            // 如果没有计算数据，只更新 statement_id
            Db::name('inpack')
                ->where('id', 'in', $inpackIds)
                ->update(['statement_id' => $statementId]);
        }
    }
    
    /**
     * 生成账单编号
     * @return string
     * @throws \Exception
     */
    private function generateStatementNo()
    {
        \think\Log::info('generateStatementNo: 开始生成账单编号');
        
        // 使用数据库锁确保编号唯一
        $today = date('Ymd');
        $prefix = 'ST' . $today;
        
        \think\Log::info('generateStatementNo: prefix=' . $prefix);
        
        // 查询今天最大的序号 - 只查询需要的字段
        $lastStatement = Db::name('statement')
            ->field('statement_no')
            ->where('statement_no', 'like', $prefix . '%')
            ->where('wxapp_id', $this->wxappId)
            ->order('id', 'desc')
            ->lock(true)
            ->find();
        
        \think\Log::info('generateStatementNo: 查询完成 - last=' . ($lastStatement['statement_no'] ?? 'null'));
        
        if ($lastStatement && !empty($lastStatement['statement_no'])) {
            // 提取序号并+1
            $lastNo = substr($lastStatement['statement_no'], -3);
            $nextNo = intval($lastNo) + 1;
        } else {
            $nextNo = 1;
        }
        
        // 格式化为3位数字
        $sequence = str_pad($nextNo, 3, '0', STR_PAD_LEFT);
        $statementNo = $prefix . $sequence;
        
        \think\Log::info('generateStatementNo: 生成完成 - no=' . $statementNo);
        
        return $statementNo;
    }
    
    /**
     * 生成Excel文件
     * @param array $statement
     * @param array $inpacks
     * @return string Excel文件路径
     * @throws \Exception
     */
    private function generateExcel($statement, $inpacks)
    {
        $excelService = new ExcelService();
        
        // 添加调试日志
        \think\Log::info('StatementService::generateExcel - statement keys: ' . implode(', ', array_keys($statement)));
        \think\Log::info('StatementService::generateExcel - statement data: ' . json_encode($statement, JSON_UNESCAPED_UNICODE));
        
        // 从数据库读取默认模板配置
        $template = Db::name('statement_template')
            ->where('is_default', 1)
            ->find();
        
        if (!$template) {
            // 如果没有默认模板，使用空配置
            $template = [
                'title' => '集运订单对账单',
                'logo_path' => '',
                'alipay_qr_path' => '',
                'wechat_qr_path' => '',
                'notice_text' => '请在收到账单后3个工作日内完成支付，谢谢！'
            ];
        }
        
        \think\Log::info('StatementService::generateExcel - template: ' . json_encode($template, JSON_UNESCAPED_UNICODE));
        
        return $excelService->generateStatementExcel($statement, $inpacks, $template);
    }
}
