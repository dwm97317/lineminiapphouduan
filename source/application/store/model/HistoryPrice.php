<?php

namespace app\store\model;

use think\Model;
use think\Db;

/**
 * 历史单价模型
 */
class HistoryPrice extends Model
{
    protected $name = 'history_price';
    protected $createTime = false;
    protected $updateTime = false;
    
    // 关闭自动时间戳
    protected $autoWriteTimestamp = false;
    
    /**
     * 批量导入历史单价
     * @param array $data 格式: [['member_id' => 31398, 'unit_price' => 48.00], ...]
     * @return array ['success_count' => 10, 'failed_count' => 2, 'errors' => [...]]
     */
    public static function batchImport($data)
    {
        $wxappId = self::getWxappId();
        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        
        foreach ($data as $index => $item) {
            try {
                // 验证数据
                if (!isset($item['member_id']) || !isset($item['unit_price'])) {
                    throw new \Exception('缺少必要字段');
                }
                
                if (!is_numeric($item['unit_price']) || $item['unit_price'] <= 0) {
                    throw new \Exception('单价必须大于0');
                }
                
                // 使用replace插入或更新
                Db::name('history_price')->insert([
                    'member_id' => $item['member_id'],
                    'unit_price' => $item['unit_price'],
                    'wxapp_id' => $wxappId,
                    'create_time' => date('Y-m-d H:i:s')
                ], true);  // true表示replace模式
                
                $successCount++;
                
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = "第" . ($index + 1) . "行: " . $e->getMessage();
            }
        }
        
        return [
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'errors' => $errors
        ];
    }
    
    /**
     * 获取客户历史单价
     * @param int $memberId 客户ID
     * @return float|null
     */
    public static function getMemberPrice($memberId)
    {
        $wxappId = self::getWxappId();
        
        // 只查询需要的字段，避免timestamp字段
        $record = self::field('unit_price')
            ->where('member_id', $memberId)
            ->where('wxapp_id', $wxappId)
            ->find();
        
        return $record ? floatval($record['unit_price']) : null;
    }
    
    /**
     * 从TXT文件导入
     * 格式: 每行 "客户ID 单价"（空格或Tab分隔），支持#注释
     * @param string $filePath 文件路径
     * @return array
     */
    public static function importFromTxt($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('文件不存在');
        }
        
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $data = [];
        
        foreach ($lines as $line) {
            // 跳过注释行
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // 分割（支持空格和Tab）
            $parts = preg_split('/\s+/', trim($line));
            
            if (count($parts) >= 2) {
                $data[] = [
                    'member_id' => intval($parts[0]),
                    'unit_price' => floatval($parts[1])
                ];
            }
        }
        
        return self::batchImport($data);
    }
    
    /**
     * 从Excel文件导入
     * 格式: A列客户ID，B列单价，无需表头，只读第一个Sheet
     * @param string $filePath 文件路径
     * @return array
     */
    public static function importFromExcel($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('文件不存在');
        }
        
        // 使用PhpSpreadsheet读取
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        
        $data = [];
        $highestRow = $sheet->getHighestRow();
        
        for ($row = 1; $row <= $highestRow; $row++) {
            $memberId = $sheet->getCell('A' . $row)->getValue();
            $unitPrice = $sheet->getCell('B' . $row)->getValue();
            
            // 跳过非数字行（可能是表头）
            if (is_numeric($memberId) && is_numeric($unitPrice)) {
                $data[] = [
                    'member_id' => intval($memberId),
                    'unit_price' => floatval($unitPrice)
                ];
            }
        }
        
        return self::batchImport($data);
    }
    
    /**
     * 获取当前小程序ID
     */
    private static function getWxappId()
    {
        // 从Session获取wxapp_id（store模块）
        $session = \think\Session::get('yoshop_store');
        return $session['wxapp']['wxapp_id'] ?? 10001;
    }
}
