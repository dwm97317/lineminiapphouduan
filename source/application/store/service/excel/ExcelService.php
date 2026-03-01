<?php

namespace app\store\service\excel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/**
 * Excel生成服务
 * 生成专业的账单Excel文件
 */
class ExcelService
{
    /**
     * 生成账单Excel
     * @param array $statement 账单信息
     * @param array $packages 订单列表
     * @param array $template 模板配置
     * @return string Excel文件路径
     */
    public function generateStatementExcel($statement, $packages, $template)
    {
        try {
            \think\Log::info('ExcelService::generateStatementExcel 开始 - statement_no=' . $statement['statement_no']);
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // 设置工作表名称为日期（月-日格式）
            $sheet->setTitle(date('m-d'));
            
            \think\Log::info('ExcelService: Spreadsheet 对象已创建，工作表名称: ' . date('m-d'));
            
            $currentRow = 1;
            
            // 1. 插入LOGO（如果有）
            if (!empty($template['logo_path'])) {
                // 处理路径：数据库存储的是相对于项目根目录的路径，需要转换为相对于web目录的路径
                $logoPath = '../' . ltrim($template['logo_path'], './');
                \think\Log::info('ExcelService: LOGO路径 - ' . $logoPath);
                if (file_exists($logoPath)) {
                    $currentRow = $this->insertLogo($sheet, $logoPath, $currentRow);
                    \think\Log::info('ExcelService: LOGO已插入');
                } else {
                    \think\Log::warning('ExcelService: LOGO文件不存在 - ' . $logoPath);
                }
            }
            
            // 2. 插入标题
            $currentRow = $this->insertTitle($sheet, $template['title'] ?? '集运订单对账单', $currentRow);
            
            \think\Log::info('ExcelService: 标题已插入 - row=' . $currentRow);
            
            // 3. 插入账单信息
            $currentRow = $this->insertStatementInfo($sheet, $statement, $currentRow);
            
            \think\Log::info('ExcelService: 账单信息已插入 - row=' . $currentRow);
            
            // 4. 插入订单明细表格
            $currentRow = $this->insertPackageTable($sheet, $packages, $currentRow);
            
            \think\Log::info('ExcelService: 订单明细已插入 - row=' . $currentRow);
            
            // 5. 插入汇总信息
            $currentRow = $this->insertSummary($sheet, $statement, $currentRow);
            
            \think\Log::info('ExcelService: 汇总信息已插入 - row=' . $currentRow);
            
            // 6. 插入收款二维码（如果有）
            if (!empty($template['alipay_qr_path']) || !empty($template['wechat_qr_path'])) {
                // 处理路径：数据库存储的是相对于项目根目录的路径，需要转换为相对于web目录的路径
                if (!empty($template['alipay_qr_path'])) {
                    $alipayPath = ltrim($template['alipay_qr_path'], './');
                    $template['alipay_qr_path'] = '../' . $alipayPath;
                }
                if (!empty($template['wechat_qr_path'])) {
                    $wechatPath = ltrim($template['wechat_qr_path'], './');
                    $template['wechat_qr_path'] = '../' . $wechatPath;
                }
                \think\Log::info('ExcelService: 二维码路径 - alipay=' . $template['alipay_qr_path'] . ', wechat=' . $template['wechat_qr_path']);
                $currentRow = $this->insertPaymentQR($sheet, $template, $currentRow);
                \think\Log::info('ExcelService: 二维码已插入 - row=' . $currentRow);
            }
            
            // 7. 插入温馨提示
            if (!empty($template['notice_text'])) {
                $this->insertNotice($sheet, $template['notice_text'], $currentRow);
                \think\Log::info('ExcelService: 温馨提示已插入');
            }
            
            \think\Log::info('ExcelService: 开始保存文件');
            
            // 保存文件
            $fileName = $statement['statement_no'] . '_' . $statement['member_id'] . '.xlsx';
            
            // 使用相对路径
            $uploadDir = './uploads/statements/';
            $filePath = $uploadDir . $fileName;
            
            \think\Log::info('ExcelService: 目标文件路径 - ' . $filePath);
            
            // 确保目录存在
            if (!is_dir($uploadDir)) {
                \think\Log::info('ExcelService: 创建目录 - ' . $uploadDir);
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new \Exception('无法创建目录: ' . $uploadDir);
                }
            }
            
            // 如果文件已存在，尝试删除（如果被占用则忽略）
            if (file_exists($filePath)) {
                \think\Log::info('ExcelService: 文件已存在，尝试删除 - ' . $filePath);
                @unlink($filePath);
            }
            
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            
            // 自动调整列宽
            \think\Log::info('ExcelService: 开始自动调整列宽');
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            \think\Log::info('ExcelService: 开始写入文件 - ' . $filePath);
            $writer->save($filePath);
            
            // 验证文件是否成功创建
            if (!file_exists($filePath)) {
                throw new \Exception('Excel文件保存失败，文件不存在: ' . $filePath);
            }
            
            $fileSize = filesize($filePath);
            \think\Log::info('ExcelService: 文件已保存成功 - size=' . $fileSize . ' bytes');
            
            // 返回相对路径（用于数据库存储和URL访问）
            $relativePath = 'uploads/statements/' . $fileName;
            \think\Log::info('ExcelService: 返回相对路径 - ' . $relativePath);
            
            return $relativePath;
            
        } catch (\Exception $e) {
            \think\Log::error('ExcelService::generateStatementExcel 失败: ' . $e->getMessage());
            \think\Log::error('ExcelService: 错误位置 - ' . $e->getFile() . ':' . $e->getLine());
            \think\Log::error('ExcelService: 堆栈跟踪 - ' . $e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * 插入LOGO
     */
    private function insertLogo($sheet, $logoPath, $currentRow)
    {
        try {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Company Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(60);
            $drawing->setCoordinates('A' . $currentRow);
            $drawing->setWorksheet($sheet);
            
            return $currentRow + 3;  // LOGO占3行
        } catch (\Exception $e) {
            return $currentRow;
        }
    }
    
    /**
     * 插入标题
     */
    private function insertTitle($sheet, $title, $currentRow)
    {
        // 合并单元格
        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        
        // 设置标题
        $sheet->setCellValue('A' . $currentRow, $title);
        
        // 设置样式 - 更精美的标题样式
        $sheet->getStyle('A' . $currentRow)->applyFromArray([
            'font' => [
                'name' => '微软雅黑',
                'size' => 20,
                'bold' => true,
                'color' => ['rgb' => '1F4E78']  // 深蓝色
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7F3FF']  // 浅蓝色背景
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '4472C4']
                ]
            ]
        ]);
        
        // 设置行高
        $sheet->getRowDimension($currentRow)->setRowHeight(35);
        
        return $currentRow + 2;  // 标题后空一行
    }
    
    /**
     * 插入账单信息
     */
    private function insertStatementInfo($sheet, $statement, $currentRow)
    {
        try {
            // 添加调试日志 - 查看传入的 $statement 数组
            \think\Log::info('ExcelService::insertStatementInfo - statement keys: ' . implode(', ', array_keys($statement)));
            \think\Log::info('ExcelService::insertStatementInfo - statement data: ' . json_encode($statement, JSON_UNESCAPED_UNICODE));
            
            // 处理可能为空的字段
            $memberName = !empty($statement['member_name']) ? $statement['member_name'] : '用户ID: ' . ($statement['member_id'] ?? 'unknown');
            $dateRange = (!empty($statement['start_date']) && !empty($statement['end_date'])) 
                ? ($statement['start_date'] . ' 至 ' . $statement['end_date'])
                : '-';
            
            $createTime = isset($statement['create_time']) 
                ? (is_string($statement['create_time']) ? $statement['create_time'] : (string)$statement['create_time']) 
                : date('Y-m-d H:i:s');
            
            // 确保所有字段都有默认值
            $totalPackages = isset($statement['total_packages']) ? $statement['total_packages'] : 0;
            $totalWeight = isset($statement['total_weight']) ? $statement['total_weight'] : 0;
            $totalAmount = isset($statement['total_amount']) ? $statement['total_amount'] : 0;
            $payStatus = isset($statement['pay_status']) ? $statement['pay_status'] : 1;
            
            $info = [
                ['账单编号：', $statement['statement_no'] ?? 'N/A', '客户：', $memberName],
                ['生成时间：', $createTime, '账单日期：', $dateRange],
                ['订单数量：', $totalPackages . ' 个', '总重量：', $totalWeight . ' KG'],
                ['总金额：', '¥ ' . number_format($totalAmount, 2), '支付状态：', $payStatus == 2 ? '已支付' : '未支付']
            ];
            
            \think\Log::info('ExcelService: 账单信息数据准备完成');
            
            foreach ($info as $row) {
                $sheet->fromArray($row, null, 'A' . $currentRow);
                
                // 更精美的样式
                $sheet->getStyle('A' . $currentRow . ':D' . $currentRow)->applyFromArray([
                    'font' => [
                        'name' => '微软雅黑', 
                        'size' => 11
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA']  // 浅灰色背景
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E0E0E0']
                        ]
                    ]
                ]);
                
                // 加粗标签并设置颜色
                $sheet->getStyle('A' . $currentRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '495057']
                    ]
                ]);
                $sheet->getStyle('C' . $currentRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '495057']
                    ]
                ]);
                
                // 设置行高
                $sheet->getRowDimension($currentRow)->setRowHeight(22);
                
                $currentRow++;
            }
            
            \think\Log::info('ExcelService: 账单信息已插入');
            
            return $currentRow + 1;  // 信息后空一行
            
        } catch (\Exception $e) {
            \think\Log::error('ExcelService::insertStatementInfo 失败: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 插入订单明细表格
     */
    private function insertPackageTable($sheet, $packages, $currentRow)
    {
        // 表头 - 添加发货日期列
        $headers = ['序号', '发货日期', '订单编号', '国际单号', '重量(KG)', '单价(元/KG)', '金额(元)'];
        $sheet->fromArray($headers, null, 'A' . $currentRow);
        
        // 表头样式 - 更精美的渐变效果
        $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->applyFromArray([
            'font' => [
                'name' => '微软雅黑',
                'size' => 11,
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '5B9BD5']  // 更柔和的蓝色
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '4472C4']
                ]
            ]
        ]);
        
        // 设置表头行高
        $sheet->getRowDimension($currentRow)->setRowHeight(25);
        
        $currentRow++;
        $startDataRow = $currentRow;
        
        // 数据行
        $index = 1;
        foreach ($packages as $package) {
            // 处理时间字段 - 集运订单表使用 created_time，格式化为月-日
            $createTime = $package['created_time'] ?? $package['create_time'] ?? null;
            $dateStr = '-';
            if ($createTime) {
                if (is_numeric($createTime)) {
                    $dateStr = date('m-d', $createTime);
                } else {
                    $dateStr = date('m-d', strtotime($createTime));
                }
            }
            
            $rowData = [
                $index,
                $dateStr,  // 发货日期
                $package['order_sn'] ?? $package['id'],
                $package['t_order_sn'] ?? '-',  // 国际单号
                $package['cale_weight'] ?? $package['weight'] ?? 0,
                $package['unit_price'] ?? 0,
                $package['calculated_amount'] ?? $package['amount'] ?? 0
            ];
            
            $sheet->fromArray($rowData, null, 'A' . $currentRow);
            
            // 数据行样式 - 斑马纹效果
            $bgColor = ($index % 2 == 0) ? 'F8F9FA' : 'FFFFFF';
            $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->applyFromArray([
                'font' => [
                    'name' => '微软雅黑', 
                    'size' => 10,
                    'color' => ['rgb' => '212529']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $bgColor]
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DEE2E6']
                    ]
                ]
            ]);
            
            // 金额右对齐并加粗
            $sheet->getStyle('E' . $currentRow . ':G' . $currentRow)->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                'font' => ['bold' => true]
            ]);
            
            // 设置行高
            $sheet->getRowDimension($currentRow)->setRowHeight(20);
            
            $currentRow++;
            $index++;
        }
        
        // 设置列宽
        $sheet->getColumnDimension('A')->setWidth(8);   // 序号
        $sheet->getColumnDimension('B')->setWidth(12);  // 发货日期
        $sheet->getColumnDimension('C')->setWidth(22);  // 订单编号
        $sheet->getColumnDimension('D')->setWidth(22);  // 国际单号
        $sheet->getColumnDimension('E')->setWidth(12);  // 重量
        $sheet->getColumnDimension('F')->setWidth(15);  // 单价
        $sheet->getColumnDimension('G')->setWidth(13);  // 金额
        
        return $currentRow + 1;
    }
    
    /**
     * 插入汇总信息
     */
    private function insertSummary($sheet, $statement, $currentRow)
    {
        // 合并单元格
        $sheet->mergeCells('A' . $currentRow . ':F' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, '合计');
        $sheet->setCellValue('G' . $currentRow, '¥ ' . number_format($statement['total_amount'], 2));
        
        // 样式 - 更醒目的汇总行
        $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->applyFromArray([
            'font' => [
                'name' => '微软雅黑',
                'size' => 13,
                'bold' => true,
                'color' => ['rgb' => '1F4E78']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF4E6']  // 浅橙色背景
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => 'FFA500']
                ]
            ]
        ]);
        
        // 设置行高
        $sheet->getRowDimension($currentRow)->setRowHeight(28);
        
        return $currentRow + 2;
    }
    
    /**
     * 插入收款二维码
     */
    private function insertPaymentQR($sheet, $template, $currentRow)
    {
        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, '收款方式');
        $sheet->getStyle('A' . $currentRow)->applyFromArray([
            'font' => [
                'name' => '微软雅黑', 
                'size' => 14, 
                'bold' => true,
                'color' => ['rgb' => '1F4E78']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7F3FF']
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '5B9BD5']
                ]
            ]
        ]);
        
        $sheet->getRowDimension($currentRow)->setRowHeight(25);
        
        $currentRow++;
        
        $col = 'B';
        
        // 支付宝二维码
        if (!empty($template['alipay_qr_path'])) {
            \think\Log::info('ExcelService: 支付宝二维码路径 - ' . $template['alipay_qr_path']);
            if (file_exists($template['alipay_qr_path'])) {
                try {
                    $drawing = new Drawing();
                    $drawing->setName('Alipay');
                    $drawing->setDescription('支付宝收款码');
                    $drawing->setPath($template['alipay_qr_path']);
                    $drawing->setHeight(150);  // 增大二维码尺寸
                    $drawing->setCoordinates($col . $currentRow);
                    $drawing->setWorksheet($sheet);
                    
                    \think\Log::info('ExcelService: 支付宝二维码已插入');
                    $col = chr(ord($col) + 2);
                } catch (\Exception $e) {
                    \think\Log::error('ExcelService: 支付宝二维码插入失败 - ' . $e->getMessage());
                }
            } else {
                \think\Log::warning('ExcelService: 支付宝二维码文件不存在');
            }
        }
        
        // 微信二维码
        if (!empty($template['wechat_qr_path'])) {
            \think\Log::info('ExcelService: 微信二维码路径 - ' . $template['wechat_qr_path']);
            if (file_exists($template['wechat_qr_path'])) {
                try {
                    $drawing = new Drawing();
                    $drawing->setName('WeChat');
                    $drawing->setDescription('微信收款码');
                    $drawing->setPath($template['wechat_qr_path']);
                    $drawing->setHeight(150);  // 增大二维码尺寸
                    $drawing->setCoordinates($col . $currentRow);
                    $drawing->setWorksheet($sheet);
                    
                    \think\Log::info('ExcelService: 微信二维码已插入');
                } catch (\Exception $e) {
                    \think\Log::error('ExcelService: 微信二维码插入失败 - ' . $e->getMessage());
                }
            } else {
                \think\Log::warning('ExcelService: 微信二维码文件不存在');
            }
        }
        
        return $currentRow + 10;  // 二维码占10行（增大尺寸后）
    }
    
    /**
     * 插入温馨提示
     */
    private function insertNotice($sheet, $noticeText, $currentRow)
    {
        $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, '温馨提示：' . $noticeText);
        
        $sheet->getStyle('A' . $currentRow)->applyFromArray([
            'font' => [
                'name' => '微软雅黑', 
                'size' => 10, 
                'italic' => true,
                'color' => ['rgb' => '6C757D']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF9E6']  // 浅黄色背景
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'FFE082']
                ]
            ]
        ]);
        
        $sheet->getRowDimension($currentRow)->setRowHeight(25);
        
        return $currentRow + 1;
    }
}
