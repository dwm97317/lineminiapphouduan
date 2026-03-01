<?php

namespace app\store\model;

use think\Model;
use think\Db;

/**
 * 账单模型
 */
class Statement extends Model
{
    protected $name = 'statement';
    protected $createTime = false;
    protected $updateTime = false;
    
    // 关闭自动时间戳，因为我们手动处理
    protected $autoWriteTimestamp = false;
    
    // 状态常量
    const STATUS_NORMAL = 1;  // 正常
    const STATUS_VOID = 2;    // 已作废
    
    // 支付状态常量
    const PAY_STATUS_UNPAID = 1;  // 未支付
    const PAY_STATUS_PAID = 2;    // 已支付
    
    /**
     * 生成账单编号
     * 格式: ST + YYYYMMDD + 3位流水号
     * 示例: ST20260228001
     */
    public static function generateStatementNo()
    {
        $prefix = 'ST';
        $date = date('Ymd');
        
        Db::startTrans();
        try {
            // 查询今天最大的序号（使用悲观锁）
            $lastStatement = self::where('statement_no', 'like', $prefix . $date . '%')
                ->lock(true)  // SELECT ... FOR UPDATE
                ->order('statement_no', 'desc')
                ->find();
            
            if ($lastStatement) {
                // 提取序号并加1
                $lastNo = substr($lastStatement['statement_no'], -3);
                $nextNo = intval($lastNo) + 1;
            } else {
                // 今天第一个账单
                $nextNo = 1;
            }
            
            // 检查是否超过999
            if ($nextNo > 999) {
                throw new \Exception('今日账单数量已达上限(999)，请明天再试');
            }
            
            // 格式化序号（3位，不足补0）
            $statementNo = $prefix . $date . str_pad($nextNo, 3, '0', STR_PAD_LEFT);
            
            Db::commit();
            return $statementNo;
            
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
    
    /**
     * 关联集运订单
     */
    public function inpacks()
    {
        return $this->hasMany('Inpack', 'statement_id', 'id');
    }
    
    /**
     * 获取账单详情（含集运订单列表）
     */
    public function getDetailWithPackages()
    {
        // 查询关联的集运订单
        $inpacks = Db::name('inpack')
            ->where('statement_id', $this->id)
            ->where('is_delete', 0)
            ->order('created_time', 'asc')
            ->select();
        
        // 手动构建 statement 数组，避免 toArray() 触发 timestamp 转换
        $statementData = [
            'id' => $this->id,
            'statement_no' => $this->statement_no,
            'member_id' => $this->member_id,
            'member_name' => $this->member_name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_packages' => $this->total_packages,
            'total_weight' => $this->total_weight,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'pay_status' => $this->pay_status,
            'pay_time' => $this->pay_time,
            'pay_remark' => $this->pay_remark,
            'excel_path' => $this->excel_path,
            'wxapp_id' => $this->wxapp_id,
            'create_time' => $this->getData('create_time'),  // 使用 getData 获取原始值
            'update_time' => $this->getData('update_time')
        ];
        
        return [
            'statement' => $statementData,
            'packages' => $inpacks  // 保持字段名为packages以兼容前端
        ];
    }
    
    /**
     * 验证账单编号格式
     */
    public static function validateStatementNo($statementNo)
    {
        // 格式：ST + 8位日期 + 3位序号
        if (!preg_match('/^ST\d{8}\d{3}$/', $statementNo)) {
            return false;
        }
        
        // 验证日期有效性
        $date = substr($statementNo, 2, 8);
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);
        
        if (!checkdate($month, $day, $year)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 格式化账单编号（用于显示）
     * ST20260228001 -> ST-2026-02-28-001
     */
    public function getFormattedStatementNo()
    {
        $no = $this->statement_no;
        
        return substr($no, 0, 2) . '-' . 
               substr($no, 2, 4) . '-' . 
               substr($no, 6, 2) . '-' . 
               substr($no, 8, 2) . '-' . 
               substr($no, 10, 3);
    }
}
