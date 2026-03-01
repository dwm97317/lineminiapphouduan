# Excel生成模块

**负责人**：后端开发D  
**预计工期**：2天  
**依赖**：数据库设计、财务配置管理

## 一、功能概述

Excel生成模块负责将账单数据导出为格式化的Excel文件，包括：
1. Excel模板设计（布局、样式）
2. 数据填充（订单明细、汇总）
3. 图片插入（LOGO、收款二维码）
4. 多Sheet管理（订单明细、汇总）
5. 性能优化（流式写入、缓存）

**技术栈**：PhpSpreadsheet

## 二、Excel模板设计

### 2.1 整体布局

```
┌─────────────────────────────────────────────────────────┐
│                      [LOGO图片]                          │
│                                                          │
│                   泰国-中国 集运账单                      │
│                                                          │
├─────────────────────────────────────────────────────────┤
│ 账单编号：ST20260128001                                  │
│ 客户姓名：李华                                           │
│ 账单周期：2026-01-01 至 2026-01-28                       │
│ 生成时间：2026-01-28 15:30:00                           │
├─────────────────────────────────────────────────────────┤
│                      订单明细                            │
├────┬──────────────┬────────┬──────┬──────┬──────────┤
│序号│  国际单号     │ 重量KG │单价  │ 金额 │ 入库时间  │
├────┼──────────────┼────────┼──────┼──────┼──────────┤
│ 1  │73584423916776│  5.80  │46.00 │266.80│2026-01-15│
│ 2  │73584423916777│  8.20  │46.00 │377.20│2026-01-16│
│ 3  │73584423916778│  3.50  │46.00 │161.00│2026-01-18│
│ 4  │73584423916779│  6.10  │46.00 │280.60│2026-01-20│
│ 5  │73584423916780│  5.00  │46.00 │230.00│2026-01-22│
├────┴──────────────┴────────┴──────┴──────┴──────────┤
│ 合计：5件                                               │
│ 总重量：28.60 KG                                        │
│ 总金额：¥1,315.60                                       │
├─────────────────────────────────────────────────────────┤
│                      收款方式                            │
├─────────────────────────────────────────────────────────┤
│  支付宝收款码          微信收款码                        │
│  [二维码图片]          [二维码图片]                      │
├─────────────────────────────────────────────────────────┤
│ 温馨提示：                                               │
│ 请尽快核对账单，谢谢！对好了，请支付到这两个二维码，      │
│ 付款好后请给我截图 谢谢！                                │
└─────────────────────────────────────────────────────────┘
```

### 2.2 样式规范

**标题区域**：
- 字体：微软雅黑，18号，加粗
- 对齐：居中
- 背景色：浅蓝色（#E8F4F8）

**信息区域**：
- 字体：微软雅黑，11号
- 对齐：左对齐
- 背景色：白色

**表头**：
- 字体：微软雅黑，11号，加粗
- 对齐：居中
- 背景色：深蓝色（#4472C4）
- 字体颜色：白色

**数据行**：
- 字体：微软雅黑，10号
- 对齐：居中
- 边框：细线

**汇总行**：
- 字体：微软雅黑，12号，加粗
- 背景色：浅黄色（#FFF2CC）

## 三、技术实现

### 3.1 服务层（ExcelService.php）

**文件路径**：`source/application/common/service/ExcelService.php`

```php
<?php
namespace app\common\service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ExcelService
{
    /**
     * 生成账单Excel
     */
    public function generateStatementExcel($statementId, $packages, $template)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 设置页面
        $sheet->setTitle('账单明细');
        
        // 当前行号
        $currentRow = 1;
        
        // 1. 插入LOGO
        if (!empty($template['logo_path']) && file_exists($template['logo_path'])) {
            $currentRow = $this->insertLogo($sheet, $template['logo_path'], $currentRow);
        }
        
        // 2. 标题
        $currentRow = $this->insertTitle($sheet, $template['title'] ?? '集运账单', $currentRow);
        
        // 3. 账单信息
        $statement = \app\common\model\Statement::find($statementId);
        $currentRow = $this->insertStatementInfo($sheet, $statement, $currentRow);
        
        // 4. 订单明细表格
        $currentRow = $this->insertPackageTable($sheet, $packages, $currentRow);
        
        // 5. 汇总信息
        $currentRow = $this->insertSummary($sheet, $statement, $currentRow);
        
        // 6. 收款二维码
        if (!empty($template['alipay_qr_path']) || !empty($template['wechat_qr_path'])) {
            $currentRow = $this->insertPaymentQR($sheet, $template, $currentRow);
        }
        
        // 7. 温馨提示
        if (!empty($template['notice_text'])) {
            $currentRow = $this->insertNotice($sheet, $template['notice_text'], $currentRow);
        }
        
        // 设置列宽
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(15);
        
        // 保存文件
        $savePath = './uploads/statements/';
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true);
        }
        
        $fileName = $statement['statement_no'] . '.xlsx';
        $filePath = $savePath . $fileName;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        
        return $filePath;
    }
    
    /**
     * 插入LOGO
     */
    private function insertLogo($sheet, $logoPath, $startRow)
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath($logoPath);
        $drawing->setHeight(60);
        $drawing->setCoordinates('A' . $startRow);
        $drawing->setWorksheet($sheet);
        
        // 合并单元格
        $sheet->mergeCells('A' . $startRow . ':F' . ($startRow + 2));
        $sheet->getRowDimension($startRow)->setRowHeight(60);
        
        return $startRow + 3;
    }
    
    /**
     * 插入标题
     */
    private function insertTitle($sheet, $title, $startRow)
    {
        $sheet->mergeCells('A' . $startRow . ':F' . $startRow);
        $sheet->setCellValue('A' . $startRow, $title);
        
        // 样式
        $sheet->getStyle('A' . $startRow)->applyFromArray([
            'font' => [
                'name' => '微软雅黑',
                'size' => 18,
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8F4F8']
            ]
        ]);
        
        $sheet->getRowDimension($startRow)->setRowHeight(30);
        
        return $startRow + 1;
    }
    
    /**
     * 插入账单信息
     */
    private function insertStatementInfo($sheet, $statement, $startRow)
    {
        $info = [
            '账单编号：' . $statement['statement_no'],
            '客户姓名：' . $statement['member_name'],
            '账单周期：' . $statement['start_date'] . ' 至 ' . $statement['end_date'],
            '生成时间：' . date('Y-m-d H:i:s')
        ];
        
        foreach ($info as $text) {
            $sheet->mergeCells('A' . $startRow . ':F' . $startRow);
            $sheet->setCellValue('A' . $startRow, $text);
            
            $sheet->getStyle('A' . $startRow)->applyFromArray([
                'font' => [
                    'name' => '微软雅黑',
                    'size' => 11
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
            
            $startRow++;
        }
        
        return $startRow + 1;
    }
    
    /**
     * 插入订单明细表格
     */
    private function insertPackageTable($sheet, $packages, $startRow)
    {
        // 表头
        $headers = ['序号', '国际单号', '重量(KG)', '单价(元/KG)', '金额(元)', '入库时间'];
        $col = 'A';
        
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $startRow, $header);
            $col++;
        }
        
        // 表头样式
        $sheet->getStyle('A' . $startRow . ':F' . $startRow)->applyFromArray([
            'font' => [
                'name' => '微软雅黑',
                'size' => 11,
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN
                ]
            ]
        ]);
        
        $startRow++;
        
        // 数据行
        $index = 1;
        foreach ($packages as $package) {
            $sheet->setCellValue('A' . $startRow, $index);
            $sheet->setCellValue('B' . $startRow, $package['express_num']);
            $sheet->setCellValue('C' . $startRow, $package['weight']);
            $sheet->setCellValue('D' . $startRow, $package['unit_price'] ?? 46.00);
            $sheet->setCellValue('E' . $startRow, $package['amount'] ?? $package['real_payment']);
            $sheet->setCellValue('F' . $startRow, date('Y-m-d', strtotime($package['entering_warehouse_time'])));
            
            // 数据行样式
            $sheet->getStyle('A' . $startRow . ':F' . $startRow)->applyFromArray([
                'font' => [
                    'name' => '微软雅黑',
                    'size' => 10
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ]
            ]);
            
            $startRow++;
            $index++;
        }
        
        return $startRow;
    }
    
    /**
     * 插入汇总信息
     */
    private function insertSummary($sheet, $statement, $startRow)
    {
        $summary = [
            '合计：' . $statement['total_packages'] . ' 件',
            '总重量：' . $statement['total_weight'] . ' KG',
            '总金额：¥' . number_format($statement['total_amount'], 2)
        ];
        
        foreach ($summary as $text) {
            $sheet->mergeCells('A' . $startRow . ':F' . $startRow);
            $sheet->setCellValue('A' . $startRow, $text);
            
            $sheet->getStyle('A' . $startRow)->applyFromArray([
                'font' => [
                    'name' => '微软雅黑',
                    'size' => 12,
                    'bold' => true
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF2CC']
                ]
            ]);
            
            $startRow++;
        }
        
        return $startRow + 1;
    }
    
    /**
     * 插入收款二维码
     */
    private function insertPaymentQR($sheet, $template, $startRow)
    {
        // 标题
        $sheet->mergeCells('A' . $startRow . ':F' . $startRow);
        $sheet->setCellValue('A' . $startRow, '收款方式');
        
        $sheet->getStyle('A' . $startRow)->applyFromArray([
            'font' => [
                'name' => '微软雅黑',
                'size' => 14,
                'bold' => true
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        
        $startRow++;
        
        // 支付宝二维码
        if (!empty($template['alipay_qr_path']) && file_exists($template['alipay_qr_path'])) {
            $drawing = new Drawing();
            $drawing->setName('Alipay QR');
            $drawing->setDescription('支付宝收款码');
            $drawing->setPath($template['alipay_qr_path']);
            $drawing->setHeight(120);
            $drawing->setCoordinates('B' . $startRow);
            $drawing->setWorksheet($sheet);
        }
        
        // 微信二维码
        if (!empty($template['wechat_qr_path']) && file_exists($template['wechat_qr_path'])) {
            $drawing = new Drawing();
            $drawing->setName('WeChat QR');
            $drawing->setDescription('微信收款码');
            $drawing->setPath($template['wechat_qr_path']);
            $drawing->setHeight(120);
            $drawing->setCoordinates('D' . $startRow);
            $drawing->setWorksheet($sheet);
        }
        
        $sheet->getRowDimension($startRow)->setRowHeight(120);
        
        return $startRow + 1;
    }
    
    /**
     * 插入温馨提示
     */
    private function insertNotice($sheet, $noticeText, $startRow)
    {
        $sheet->mergeCells('A' . $startRow . ':F' . ($startRow + 2));
        $sheet->setCellValue('A' . $startRow, '温馨提示：' . "\n" . $noticeText);
        
        $sheet->getStyle('A' . $startRow)->applyFromArray([
            'font' => [
                'name' => '微软雅黑',
                'size' => 10
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF9E6']
            ]
        ]);
        
        $sheet->getRowDimension($startRow)->setRowHeight(60);
        
        return $startRow + 3;
    }
}
```

### 3.2 性能优化版本（大批量数据）

**文件路径**：`source/application/common/service/ExcelStreamService.php`

```php
<?php
namespace app\common\service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelStreamService
{
    /**
     * 流式生成大批量订单Excel
     */
    public function generateLargeStatementExcel($statementId, $batchSize = 1000)
    {
        $statement = \app\common\model\Statement::find($statementId);
        $template = \app\common\model\FinanceConfig::getDefaultTemplate();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 设置基本信息（同上）
        $currentRow = $this->setupBasicInfo($sheet, $statement, $template);
        
        // 流式写入订单数据
        $offset = 0;
        $index = 1;
        
        while (true) {
            // 分批查询订单
            $packages = \app\common\model\Package::where('statement_id', $statementId)
                ->limit($offset, $batchSize)
                ->select();
            
            if (empty($packages)) {
                break;
            }
            
            // 写入数据
            foreach ($packages as $package) {
                $sheet->setCellValue('A' . $currentRow, $index);
                $sheet->setCellValue('B' . $currentRow, $package['express_num']);
                $sheet->setCellValue('C' . $currentRow, $package['weight']);
                $sheet->setCellValue('D' . $currentRow, $package['unit_price'] ?? 46.00);
                $sheet->setCellValue('E' . $currentRow, $package['amount'] ?? $package['real_payment']);
                $sheet->setCellValue('F' . $currentRow, date('Y-m-d', strtotime($package['entering_warehouse_time'])));
                
                $currentRow++;
                $index++;
            }
            
            $offset += $batchSize;
            
            // 释放内存
            unset($packages);
            gc_collect_cycles();
        }
        
        // 保存文件
        $savePath = './uploads/statements/';
        $fileName = $statement['statement_no'] . '.xlsx';
        $filePath = $savePath . $fileName;
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        
        return $filePath;
    }
}
```

## 四、多Sheet管理

### 4.1 创建多个Sheet

```php
/**
 * 生成多Sheet账单Excel
 */
public function generateMultiSheetExcel($statementId)
{
    $spreadsheet = new Spreadsheet();
    
    // Sheet1: 账单明细
    $sheet1 = $spreadsheet->getActiveSheet();
    $sheet1->setTitle('账单明细');
    $this->fillStatementSheet($sheet1, $statementId);
    
    // Sheet2: 订单汇总
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('订单汇总');
    $this->fillSummarySheet($sheet2, $statementId);
    
    // Sheet3: 客户信息
    $sheet3 = $spreadsheet->createSheet();
    $sheet3->setTitle('客户信息');
    $this->fillMemberSheet($sheet3, $statementId);
    
    // 保存
    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);
}
```

## 五、图片处理

### 5.1 图片压缩

```php
/**
 * 压缩图片
 */
private function compressImage($sourcePath, $targetPath, $quality = 75)
{
    $info = getimagesize($sourcePath);
    $mime = $info['mime'];
    
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            imagejpeg($image, $targetPath, $quality);
            break;
        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            imagepng($image, $targetPath, 9);
            break;
    }
    
    imagedestroy($image);
}
```

### 5.2 图片缓存

```php
/**
 * 获取缓存的图片路径
 */
private function getCachedImagePath($originalPath)
{
    $cacheDir = './runtime/cache/images/';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $hash = md5_file($originalPath);
    $cachedPath = $cacheDir . $hash . '.jpg';
    
    if (!file_exists($cachedPath)) {
        $this->compressImage($originalPath, $cachedPath);
    }
    
    return $cachedPath;
}
```

## 六、性能优化

### 6.1 内存优化

```php
// 1. 使用流式写入
$writer = new Xlsx($spreadsheet);
$writer->setPreCalculateFormulas(false);  // 不预计算公式
$writer->save($filePath);

// 2. 及时释放内存
unset($packages);
gc_collect_cycles();

// 3. 设置内存限制
ini_set('memory_limit', '512M');
```

### 6.2 异步生成（队列）

```php
/**
 * 异步生成Excel
 */
public function asyncGenerateExcel($statementId)
{
    // 使用队列
    $job = new GenerateExcelJob($statementId);
    Queue::push($job);
    
    return [
        'status' => 'pending',
        'message' => 'Excel正在生成中，请稍后下载'
    ];
}
```

### 6.3 缓存模板

```php
/**
 * 缓存模板配置
 */
private function getCachedTemplate()
{
    $cacheKey = 'statement_template';
    $template = cache($cacheKey);
    
    if (!$template) {
        $template = \app\common\model\FinanceConfig::getDefaultTemplate();
        cache($cacheKey, $template, 3600);  // 缓存1小时
    }
    
    return $template;
}
```

## 七、测试用例

### 7.1 功能测试

- [ ] 生成基本账单Excel
- [ ] 插入LOGO图片
- [ ] 插入收款二维码
- [ ] 多Sheet生成
- [ ] 样式正确应用
- [ ] 中文显示正常
- [ ] 文件可正常打开

### 7.2 性能测试

- [ ] 100个订单生成时间
- [ ] 500个订单生成时间
- [ ] 1000个订单生成时间
- [ ] 内存占用测试
- [ ] 并发生成测试

### 7.3 兼容性测试

- [ ] Excel 2007打开
- [ ] Excel 2010打开
- [ ] Excel 2016打开
- [ ] WPS打开
- [ ] Mac Numbers打开

## 八、常见问题

### Q1: 图片不显示？
A: 检查图片路径是否正确，文件是否存在，权限是否正确。

### Q2: 中文乱码？
A: 确保使用UTF-8编码，PhpSpreadsheet默认支持UTF-8。

### Q3: 内存溢出？
A: 使用流式写入，分批处理数据，及时释放内存。

### Q4: 生成速度慢？
A: 使用异步队列，缓存模板配置，压缩图片。

## 九、交付清单

- [ ] ExcelService.php服务
- [ ] ExcelStreamService.php（性能优化版）
- [ ] 图片处理工具类
- [ ] 队列任务类
- [ ] Excel模板示例
- [ ] 性能测试报告
- [ ] 使用文档
