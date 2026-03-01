# PhpSpreadsheet 研究

## 概述

PhpSpreadsheet 是一个用于读写Excel文件的PHP库，是PHPExcel的继任者。

## 基本信息

- **项目地址**：https://github.com/PHPOffice/PhpSpreadsheet
- **文档**：https://phpspreadsheet.readthedocs.io/
- **PHP版本要求**：PHP 7.2+
- **安装方式**：`composer require phpoffice/phpspreadsheet`

## 核心功能

### 1. 创建Excel文件

```php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 设置单元格值
$sheet->setCellValue('A1', 'Hello World');

// 保存文件
$writer = new Xlsx($spreadsheet);
$writer->save('hello_world.xlsx');
```

### 2. 样式设置

```php
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// 设置样式
$sheet->getStyle('A1')->applyFromArray([
    'font' => [
        'name' => '微软雅黑',
        'size' => 14,
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
```

### 3. 插入图片

```php
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$drawing = new Drawing();
$drawing->setName('Logo');
$drawing->setDescription('Company Logo');
$drawing->setPath('./assets/logo.png');
$drawing->setHeight(60);
$drawing->setCoordinates('A1');
$drawing->setWorksheet($sheet);
```

### 4. 合并单元格

```php
// 合并A1到F1
$sheet->mergeCells('A1:F1');
```

### 5. 设置列宽和行高

```php
// 设置列宽
$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(15);

// 设置行高
$sheet->getRowDimension(1)->setRowHeight(30);
```

## 性能优化

### 1. 内存优化

```php
// 禁用预计算公式（提高性能）
$writer->setPreCalculateFormulas(false);

// 及时释放内存
unset($spreadsheet);
gc_collect_cycles();
```

### 2. 大批量数据处理

对于大量数据（1000+行），建议：

```php
// 方案1：分批写入
$batchSize = 1000;
$offset = 0;

while ($packages = getPackages($offset, $batchSize)) {
    foreach ($packages as $index => $package) {
        $row = $offset + $index + 2;
        $sheet->setCellValue('A' . $row, $package['id']);
        // ... 其他字段
    }
    
    $offset += $batchSize;
    unset($packages);
    gc_collect_cycles();
}

// 方案2：使用fromArray批量写入
$data = [
    ['ID', 'Name', 'Amount'],
    [1, 'Item 1', 100],
    [2, 'Item 2', 200],
];
$sheet->fromArray($data, null, 'A1');
```

### 3. 缓存设置

```php
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Collection\Memory;

// 使用内存缓存
Settings::setCache(new Memory());
```

## 账单系统应用

### 1. 账单Excel结构

```
Row 1-3:   LOGO区域（合并单元格）
Row 4:     标题（合并单元格，居中，大字体）
Row 5-8:   账单信息（账单编号、客户、日期等）
Row 9:     空行
Row 10:    表头（订单明细）
Row 11+:   订单数据
Row N:     汇总信息
Row N+1:   收款二维码
Row N+2:   温馨提示
```

### 2. 示例代码

```php
class ExcelService
{
    public function generateStatementExcel($statement, $packages, $template)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $currentRow = 1;
        
        // 1. 插入LOGO
        if ($template['logo_path']) {
            $currentRow = $this->insertLogo($sheet, $template['logo_path'], $currentRow);
        }
        
        // 2. 标题
        $currentRow = $this->insertTitle($sheet, $template['title'], $currentRow);
        
        // 3. 账单信息
        $currentRow = $this->insertStatementInfo($sheet, $statement, $currentRow);
        
        // 4. 订单明细
        $currentRow = $this->insertPackageTable($sheet, $packages, $currentRow);
        
        // 5. 汇总
        $currentRow = $this->insertSummary($sheet, $statement, $currentRow);
        
        // 6. 二维码
        if ($template['alipay_qr_path'] || $template['wechat_qr_path']) {
            $currentRow = $this->insertPaymentQR($sheet, $template, $currentRow);
        }
        
        // 7. 提示
        if ($template['notice_text']) {
            $this->insertNotice($sheet, $template['notice_text'], $currentRow);
        }
        
        // 保存
        $fileName = $statement['statement_no'] . '_' . $statement['member_id'] . '.xlsx';
        $filePath = './uploads/statements/' . $fileName;
        
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($filePath);
        
        return $filePath;
    }
}
```

## 常见问题

### Q1: 中文乱码？
A: PhpSpreadsheet默认使用UTF-8，确保PHP文件和数据都是UTF-8编码。

### Q2: 图片不显示？
A: 检查图片路径是否正确，文件是否存在，权限是否正确。

### Q3: 内存溢出？
A: 使用分批处理，及时释放内存，禁用预计算公式。

### Q4: 生成速度慢？
A: 减少样式设置，使用fromArray批量写入，考虑异步生成。

## 结论

PhpSpreadsheet 功能强大，适合账单系统的Excel生成需求。关键点：
- ✅ 支持样式、图片、合并单元格
- ✅ 性能可接受（1000行以内）
- ✅ 文档完善，社区活跃
- ⚠️ 需要注意内存管理
- ⚠️ 大批量数据需要优化

**推荐使用**。
