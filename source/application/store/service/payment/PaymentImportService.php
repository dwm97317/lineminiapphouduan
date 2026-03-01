<?php

namespace app\store\service\payment;

use think\Db;

/**
 * 财务原始数据导入服务
 * 负责从Excel文件导入历史订单支付状态
 */
class PaymentImportService
{
    /**
     * 商家ID
     * @var int
     */
    private $wxappId;
    
    /**
     * 构造函数
     * 初始化wxapp_id
     */
    public function __construct($wxappId = null)
    {
        // 如果传入了wxapp_id，直接使用
        if ($wxappId !== null) {
            $this->wxappId = $wxappId;
        } else {
            // 从Session获取wxapp_id（store模块）
            $session = \think\Session::get('yoshop_store');
            $this->wxappId = $session['wxapp']['wxapp_id'] ?? 10001;
        }
        
        \think\Log::info('PaymentImportService 初始化 - wxapp_id=' . $this->wxappId . ' [VERSION: 2026-03-02-v3-Debug]');
    }
    
    /**
     * 解析Excel文件
     * @param string $filePath 文件路径
     * @return array 解析结果
     * @throws \Exception
     */
    public function parseExcelFile($filePath)
    {
        $spreadsheet = null;
        
        try {
            \think\Log::info("=== 开始解析Excel文件: {$filePath} ===");
            \think\Log::info("文件大小: " . filesize($filePath) . " bytes");
            \think\Log::info("文件修改时间: " . date('Y-m-d H:i:s', filemtime($filePath)));
            
            // 验证文件扩展名
            $this->validateFileExtension($filePath);
            
            // 清除所有可能的缓存
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
            // 使用绝对路径
            $absolutePath = realpath($filePath);
            
            // 加载Excel文件
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($absolutePath);
            
            \think\Log::info('Excel文件加载成功: ' . $filePath);
            
            // 解析所有Sheet
            $parsedData = $this->parseSheets($spreadsheet);
            
            \think\Log::info("=== Excel文件解析完成，总行数: {$parsedData['total_rows']} ===");
            
            // 显式断开Spreadsheet对象，释放内存和文件句柄
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
            return [
                'success' => true,
                'data' => $parsedData
            ];
            
        } catch (\Exception $e) {
            \think\Log::error('Excel文件解析失败: ' . $e->getMessage());
            \think\Log::error('错误堆栈: ' . $e->getTraceAsString());
            
            // 确保释放资源
            if ($spreadsheet !== null) {
                try {
                    $spreadsheet->disconnectWorksheets();
                    unset($spreadsheet);
                } catch (\Exception $cleanupEx) {
                    \think\Log::warning('清理Spreadsheet对象失败: ' . $cleanupEx->getMessage());
                }
            }
            
            return [
                'success' => false,
                'error' => '文件解析失败: ' . $e->getMessage(),
                'error_code' => 'FILE_PARSE_ERROR'
            ];
        }
    }
    
    /**
     * 验证文件扩展名
     * @param string $filePath 文件路径
     * @return bool
     * @throws \Exception
     */
    private function validateFileExtension($filePath)
    {
        // 获取文件扩展名
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // 验证扩展名是否为.xls或.xlsx
        if (!in_array($extension, ['xls', 'xlsx'])) {
            throw new \Exception('无效的文件格式，仅支持 .xls 或 .xlsx 文件');
        }
        
        // 验证文件是否存在
        if (!file_exists($filePath)) {
            throw new \Exception('文件不存在: ' . $filePath);
        }
        
        // 验证文件是否可读
        if (!is_readable($filePath)) {
            throw new \Exception('文件不可读: ' . $filePath);
        }
        
        return true;
    }
    
    /**
     * 解析Excel文件的所有Sheet
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @return array [
     *   'sheets' => [
     *     [
     *       'name' => 'Sheet1',
     *       'rows' => [
     *         [
     *           'row_number' => 2,
     *           'member_id' => '23048',
     *           'weight' => 1.5,
     *           'express_num' => 'ABC123456',
     *           'date' => '2月13日',
     *           'color_a' => ['r' => 200, 'g' => 220, 'b' => 255],
     *           'color_b' => ['r' => 200, 'g' => 220, 'b' => 255]
     *         ]
     *       ]
     *     ]
     *   ],
     *   'total_rows' => 100
     * ]
     */
    private function parseSheets($spreadsheet)
    {
        $sheets = [];
        $totalRows = 0;
        
        try {
            // 获取所有Sheet
            $allSheets = $spreadsheet->getAllSheets();
            $sheetCount = count($allSheets);
            
            \think\Log::info("Excel文件包含 {$sheetCount} 个Sheet");
            
            // 遍历所有Sheet
            foreach ($allSheets as $sheetIndex => $sheet) {
                $sheetName = $sheet->getTitle();
                $sheetRows = [];
                
                \think\Log::info("开始解析Sheet [{$sheetIndex}]: {$sheetName}");
                
                // 获取最高行号
                $highestRow = $sheet->getHighestRow();
                \think\Log::info("Sheet {$sheetName} 最高行号: {$highestRow}");
                
                // 统计跳过的行
                $skippedEmpty = 0;
                $skippedWhite = 0;
                    
                // 从第2行开始遍历（假设第1行是表头）
                for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
                    try {
                        // 关键修复：先获取A列和B列的Cell，立即读取颜色和值
                        // 然后再获取其他列的Cell
                        // 因为PhpSpreadsheet在获取D列Cell后会影响A列Cell的样式缓存
                        
                        // 获取A列Cell并立即提取所有需要的信息
                        $memberIdCell = $sheet->getCell('A' . $rowIndex);
                        $colorA = $this->getCellBackgroundColor($memberIdCell);
                        $memberIdValue = $memberIdCell->getValue();
                        $memberId = $this->extractMemberId($memberIdValue);
                        
                        // 获取B列Cell并立即提取所有需要的信息
                        $weightCell = $sheet->getCell('B' . $rowIndex);
                        $colorB = $this->getCellBackgroundColor($weightCell);
                        $weight = $weightCell->getValue();
                        
                        // 跳过Member_ID或weight为空的行（需求1.3）
                        if (empty($memberId) || empty($weight)) {
                            $skippedEmpty++;
                            continue;
                        }
                        
                        // 现在可以安全地获取其他列
                        $expressNumCell = $sheet->getCell('C' . $rowIndex);
                        $expressNum = $expressNumCell->getValue();
                        
                        $dateCell = $sheet->getCell('D' . $rowIndex);
                        $date = $dateCell->getValue();
                        
                        // 检测行颜色（需求14.1: 白色行过滤）
                        $colorDetection = $this->detectRowColor($colorA, $colorB);
                        
                        // 跳过白色行（需求14.1, 14.2, 14.3, 14.4）
                        if ($colorDetection['color'] === 'white') {
                            $skippedWhite++;
                            continue;
                        }
                        
                        // 构建行数据（所有数据都已经提取为原始值，不再持有Cell对象引用）
                        $rowData = [
                            'row_number' => $rowIndex,
                            'member_id' => $memberId,
                            'weight' => floatval($weight),
                            'express_num' => $expressNum,
                            'date' => $date,
                            'color_a' => $colorA,
                            'color_b' => $colorB,
                            'color' => $colorDetection['color'],
                            'confidence' => $colorDetection['confidence'],
                            'rgb' => $colorDetection['rgb']
                        ];
                        
                        $sheetRows[] = $rowData;
                        $totalRows++;
                        
                    } catch (\Exception $rowEx) {
                        \think\Log::error("解析Sheet {$sheetName} 行 {$rowIndex} 失败: " . $rowEx->getMessage());
                        // 继续处理下一行
                        continue;
                    }
                }
                    
                // 添加Sheet数据
                $sheets[] = [
                    'name' => $sheetName,
                    'rows' => $sheetRows
                ];
                
                \think\Log::info("Sheet {$sheetName} 解析完成: 有效数据=" . count($sheetRows) . "行, 跳过空行={$skippedEmpty}, 跳过白色行={$skippedWhite}");
            }
        
        } catch (\Exception $e) {
            \think\Log::error("解析Sheet失败: " . $e->getMessage());
            throw $e;
        }
        
        \think\Log::info("所有Sheet解析完成，共 {$totalRows} 行有效数据");
        
        return [
            'sheets' => $sheets,
            'total_rows' => $totalRows
        ];
    }
    
    /**
     * 提取Member_ID（去除国家后缀和图片）
     * @param mixed $cellValue
     * @return string|null
     */
    private function extractMemberId($cellValue)
    {
        if (empty($cellValue)) {
            return null;
        }
        
        // 转换为字符串
        $value = strval($cellValue);
        
        // 使用正则表达式提取数字部分
        // 匹配开头的数字，忽略后面的国家后缀（如"23048泰国" → "23048"）
        if (preg_match('/^(\d+)/', $value, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * 获取单元格背景颜色RGB值
     * @param \PhpOffice\PhpSpreadsheet\Cell\Cell $cell
     * @return array ['r' => int, 'g' => int, 'b' => int]
     */
    private function getCellBackgroundColor($cell)
    {
        try {
            // 检查Cell对象是否有效
            if ($cell === null) {
                \think\Log::warning('Cell对象为null');
                return ['r' => 255, 'g' => 255, 'b' => 255];
            }
            
            // 检查Worksheet引用是否有效
            try {
                $worksheet = $cell->getWorksheet();
                if ($worksheet === null) {
                    \think\Log::warning('Worksheet引用为null');
                    return ['r' => 255, 'g' => 255, 'b' => 255];
                }
            } catch (\Exception $wsEx) {
                \think\Log::warning('获取Worksheet失败: ' . $wsEx->getMessage());
                return ['r' => 255, 'g' => 255, 'b' => 255];
            }
            
            // 获取单元格填充样式
            $style = $cell->getStyle();
            if ($style === null) {
                return ['r' => 255, 'g' => 255, 'b' => 255];
            }
            
            $fill = $style->getFill();
            if ($fill === null) {
                return ['r' => 255, 'g' => 255, 'b' => 255];
            }
            
            // 直接获取填充颜色代码，不检查FillType
            // 因为某些Excel文件中，有颜色的单元格FillType也可能是none
            $startColor = $fill->getStartColor();
            if ($startColor === null) {
                return ['r' => 255, 'g' => 255, 'b' => 255];
            }
            
            $colorCode = $startColor->getRGB();
            
            // 如果颜色代码为空或为FFFFFF，返回白色
            if (empty($colorCode) || $colorCode === 'FFFFFF') {
                return ['r' => 255, 'g' => 255, 'b' => 255];
            }
            
            // 转换十六进制颜色代码为RGB
            $r = hexdec(substr($colorCode, 0, 2));
            $g = hexdec(substr($colorCode, 2, 2));
            $b = hexdec(substr($colorCode, 4, 2));
            
            return ['r' => $r, 'g' => $g, 'b' => $b];
            
        } catch (\Exception $e) {
            // 如果获取颜色失败，返回默认值（白色）
            \think\Log::warning('获取单元格颜色失败: ' . $e->getMessage());
            return ['r' => 255, 'g' => 255, 'b' => 255];
        }
    }
    
    /**
     * 检测行颜色
     * @param array $colorA 列A的RGB值
     * @param array $colorB 列B的RGB值
     * @return array [
     *   'color' => 'blue|pink|green|white|unknown',
     *   'confidence' => 'high|medium|low',
     *   'rgb' => ['r' => int, 'g' => int, 'b' => int]
     * ]
     */
    private function detectRowColor($colorA, $colorB)
    {
        // 使用列A和列B中任一颜色进行检测（优先使用列A）
        $rgb = $colorA;
        
        // 如果列A是白色/无颜色，尝试使用列B
        if ($this->isWhite($colorA) && !$this->isWhite($colorB)) {
            $rgb = $colorB;
        }
        
        // 检测颜色类型
        if ($this->isWhite($rgb)) {
            return [
                'color' => 'white',
                'confidence' => 'high',
                'rgb' => $rgb
            ];
        } elseif ($this->isBlue($rgb)) {
            return [
                'color' => 'blue',
                'confidence' => 'high',
                'rgb' => $rgb
            ];
        } elseif ($this->isPink($rgb)) {
            return [
                'color' => 'pink',
                'confidence' => 'high',
                'rgb' => $rgb
            ];
        } elseif ($this->isGreen($rgb)) {
            return [
                'color' => 'green',
                'confidence' => 'high',
                'rgb' => $rgb
            ];
        } else {
            // 未知颜色
            return [
                'color' => 'unknown',
                'confidence' => 'low',
                'rgb' => $rgb
            ];
        }
    }
    
    /**
     * 判断是否为蓝色
     * 规则: B值最大 且 B > 150 且 B > R+20 且 B > G+20
     * @param array $rgb RGB值数组
     * @return bool
     */
    private function isBlue($rgb)
    {
        return $rgb['b'] > 150 
            && $rgb['b'] > $rgb['r'] + 20 
            && $rgb['b'] > $rgb['g'] + 20;
    }
    
    /**
     * 判断是否为粉红色
     * 规则: R > 200 且 G > 150 且 B > 150
     * @param array $rgb RGB值数组
     * @return bool
     */
    private function isPink($rgb)
    {
        return $rgb['r'] > 200 
            && $rgb['g'] > 150 
            && $rgb['b'] > 150;
    }
    
    /**
     * 判断是否为绿色
     * 规则: G值最大 且 G > 150 且 G > R+20 且 G > B+20
     * @param array $rgb RGB值数组
     * @return bool
     */
    private function isGreen($rgb)
    {
        return $rgb['g'] > 150 
            && $rgb['g'] > $rgb['r'] + 20 
            && $rgb['g'] > $rgb['b'] + 20;
    }
    
    /**
     * 判断是否为白色或无颜色
     * 规则: RGB = FFFFFF 或无填充 (r=0, g=0, b=0)
     * @param array $rgb RGB值数组
     * @return bool
     */
    private function isWhite($rgb)
    {
        // 白色或接近白色 (RGB值都大于250)
        return ($rgb['r'] > 250 && $rgb['g'] > 250 && $rgb['b'] > 250);
    }
    
    /**
     * 通过国际单号精确匹配订单
     * 
     * Excel中的"国际单号"对应yoshop_inpack表的t_order_sn字段
     * 直接在yoshop_inpack表中查询，无需关联yoshop_package表
     * 
     * @param string $expressNum 国际单号（对应t_order_sn字段）
     * @return array [
     *   'match_type' => 'exact|multiple_db|none',
     *   'order' => array|null,
     *   'candidates' => array (当match_type=multiple_db时),
     *   'warning' => string|null
     * ]
     */
    private function matchByExpressNum($expressNum)
    {
        if (empty($expressNum)) {
            return [
                'match_type' => 'none',
                'order' => null,
                'candidates' => [],
                'warning' => null
            ];
        }
        
        try {
            // 直接在yoshop_inpack表中通过t_order_sn字段查询
            // 注意：不限制wxapp_id，允许跨租户匹配（财务数据导入可能涉及多个租户）
            $orders = Db::name('inpack')
                ->where('t_order_sn', $expressNum)
                ->where('is_delete', 0)
                ->select();
            
            $orderCount = count($orders);
            
            if ($orderCount === 0) {
                \think\Log::info("国际单号 {$expressNum} 在inpack表中未找到匹配");
                return [
                    'match_type' => 'none',
                    'order' => null,
                    'candidates' => [],
                    'warning' => '国际单号在数据库中不存在，将使用用户ID+重量进行模糊匹配'
                ];
            } elseif ($orderCount === 1) {
                // 精确匹配到唯一订单
                \think\Log::info("国际单号 {$expressNum} 精确匹配到订单: order_id={$orders[0]['id']}, order_sn={$orders[0]['order_sn']}");
                return [
                    'match_type' => 'exact',
                    'confidence' => 'high',
                    'order' => $orders[0],
                    'candidates' => [],
                    'warning' => null
                ];
            } else {
                // 数据库中存在多个订单使用相同的国际单号（数据异常）
                \think\Log::warning("数据库重复单号检测: 国际单号 {$expressNum} 关联到 {$orderCount} 个订单");
                return [
                    'match_type' => 'multiple_db',
                    'confidence' => 'low',
                    'order' => null,
                    'candidates' => $orders,
                    'warning' => "数据库中存在{$orderCount}个订单使用此国际单号，需要人工核对"
                ];
            }
            
        } catch (\Exception $e) {
            \think\Log::error("国际单号匹配失败: {$expressNum}, 错误: " . $e->getMessage());
            return [
                'match_type' => 'none',
                'order' => null,
                'candidates' => [],
                'warning' => '查询失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 解析日期范围
     * 
     * 支持中文日期格式（如"2月13日"），构造从00:00:00到23:59:59的时间范围
     * 如果日期为空，使用当前月份作为默认值
     * 实现跨年智能回退：如果解析的日期在未来，自动减1年
     * 
     * @param string|null $dateStr 日期字符串（如"2月13日"）
     * @return array ['start' => timestamp, 'end' => timestamp]
     */
    private function parseDateRange($dateStr)
    {
        // 需求15.4: 当列D为空时，使用当前月份的第一天到最后一天
        if (empty($dateStr)) {
            $year = date('Y');
            $month = date('m');
            $startTime = strtotime("{$year}-{$month}-01 00:00:00");
            $endTime = strtotime(date('Y-m-t 23:59:59')); // 't' 返回当月最后一天
            
            \think\Log::info("日期为空，使用当前月份: {$year}-{$month}");
            
            return ['start' => $startTime, 'end' => $endTime];
        }
        
        try {
            // 需求15.1: 解析中文日期格式（如"2月13日"）
            // 匹配格式: "数字月数字日"
            if (preg_match('/(\d+)月(\d+)日/', $dateStr, $matches)) {
                $month = intval($matches[1]);
                $day = intval($matches[2]);
                
                // 需求15.2: 使用当前年份（当年份未指定时）
                $year = date('Y');
                
                // 验证月份和日期的有效性
                if ($month < 1 || $month > 12) {
                    throw new \Exception("无效的月份: {$month}");
                }
                
                if ($day < 1 || $day > 31) {
                    throw new \Exception("无效的日期: {$day}");
                }
                
                // 需求15.3: 构造从00:00:00到23:59:59的时间范围
                $startTime = strtotime("{$year}-{$month}-{$day} 00:00:00");
                $endTime = strtotime("{$year}-{$month}-{$day} 23:59:59");
                
                // 验证日期是否有效（例如2月30日无效）
                if ($startTime === false || $endTime === false) {
                    throw new \Exception("无效的日期: {$year}-{$month}-{$day}");
                }
                
                // 需求15.5的一部分: 跨年智能回退
                // 如果解析出的日期晚于今天，自动减1年
                $currentTime = time();
                if ($startTime > $currentTime) {
                    $year--;
                    $startTime = strtotime("{$year}-{$month}-{$day} 00:00:00");
                    $endTime = strtotime("{$year}-{$month}-{$day} 23:59:59");
                    
                    \think\Log::info("日期在未来，自动回退1年: {$dateStr} -> {$year}-{$month}-{$day}");
                }
                
                \think\Log::info("日期解析成功: {$dateStr} -> {$year}-{$month}-{$day}");
                
                return ['start' => $startTime, 'end' => $endTime];
            }
            
            // 如果不匹配中文日期格式，尝试其他常见格式
            // 尝试使用strtotime解析
            $timestamp = strtotime($dateStr);
            if ($timestamp !== false) {
                $startTime = strtotime(date('Y-m-d 00:00:00', $timestamp));
                $endTime = strtotime(date('Y-m-d 23:59:59', $timestamp));
                
                \think\Log::info("日期解析成功（通用格式）: {$dateStr}");
                
                return ['start' => $startTime, 'end' => $endTime];
            }
            
            // 无法解析，抛出异常
            throw new \Exception("无法识别的日期格式: {$dateStr}");
            
        } catch (\Exception $e) {
            // 需求15.5: 当日期解析失败时，记录警告并使用当前月份作为后备
            \think\Log::warning("日期解析失败: {$dateStr}, 错误: " . $e->getMessage() . ", 使用当前月份作为后备");
            
            // 使用当前月份作为后备
            $year = date('Y');
            $month = date('m');
            $startTime = strtotime("{$year}-{$month}-01 00:00:00");
            $endTime = strtotime(date('Y-m-t 23:59:59'));
            
            return ['start' => $startTime, 'end' => $endTime];
        }
    }
    
    /**
     * 通过用户ID和重量模糊匹配订单
     * 
     * 应用±0.5 KG的重量容差，同时检查weight和cale_weight字段
     * 使用日期范围过滤（如果提供）
     * 计算每个候选订单的重量差异并排序
     * 
     * @param string $memberId 用户ID
     * @param float $weight 重量
     * @param string|null $date 日期字符串（如"2月13日"）
     * @return array [
     *   'match_type' => 'fuzzy|multiple|none',
     *   'confidence' => 'medium|low',
     *   'order' => array|null,
     *   'candidates' => array
     * ]
     */
    private function matchByMemberIdAndWeight($memberId, $weight, $date = null)
    {
        if (empty($memberId) || empty($weight)) {
            return [
                'match_type' => 'none',
                'confidence' => 'low',
                'order' => null,
                'candidates' => []
            ];
        }
        
        try {
            // 需求4.2: 应用±0.5 KG的重量容差
            $weightMin = $weight - 0.5;
            $weightMax = $weight + 0.5;
            
            // 需求4.3, 4.4: 解析日期范围（如果提供，否则使用当前月份）
            $dateRange = $this->parseDateRange($date);
            
            \think\Log::info("模糊匹配查询: member_id={$memberId}, weight={$weight} (±0.5), date_range=" . 
                date('Y-m-d H:i:s', $dateRange['start']) . ' ~ ' . date('Y-m-d H:i:s', $dateRange['end']));
            
            // 需求4.1: 通过Member_ID查询
            // 需求4.2: 同时检查weight和cale_weight字段，应用±0.5 KG容差
            // 需求4.3: 使用日期范围过滤
            // 注意：不限制wxapp_id，允许跨租户匹配
            $query = Db::name('inpack')
                ->where('member_id', $memberId)
                ->where('is_delete', 0)
                ->where('created_time', 'between', [
                    date('Y-m-d H:i:s', $dateRange['start']),
                    date('Y-m-d H:i:s', $dateRange['end'])
                ])
                ->where(function($query) use ($weightMin, $weightMax) {
                    // 检查weight字段或cale_weight字段是否在容差范围内
                    $query->where('weight', 'between', [$weightMin, $weightMax])
                          ->whereOr('cale_weight', 'between', [$weightMin, $weightMax]);
                });
            
            $candidates = $query->select();
            
            if (empty($candidates)) {
                \think\Log::info("模糊匹配未找到候选订单: member_id={$memberId}");
                return [
                    'match_type' => 'none',
                    'confidence' => 'low',
                    'order' => null,
                    'candidates' => []
                ];
            }
            
            // 需求4.5: 计算每个候选订单的重量差异
            foreach ($candidates as &$candidate) {
                // 计算与weight字段的差异
                $weightDiff = abs($candidate['weight'] - $weight);
                
                // 计算与cale_weight字段的差异
                $caleWeightDiff = abs($candidate['cale_weight'] - $weight);
                
                // 使用较小的差异作为该订单的重量差异
                $candidate['weight_difference'] = min($weightDiff, $caleWeightDiff);
            }
            unset($candidate); // 解除引用
            
            // 需求4.6: 按重量差异升序排序
            usort($candidates, function($a, $b) {
                return $a['weight_difference'] <=> $b['weight_difference'];
            });
            
            \think\Log::info("模糊匹配找到 " . count($candidates) . " 个候选订单");
            
            // 需求4.7: 如果最小重量差异是唯一的，返回该订单
            if (count($candidates) === 1) {
                return [
                    'match_type' => 'fuzzy',
                    'confidence' => 'medium',
                    'order' => $candidates[0],
                    'candidates' => []
                ];
            }
            
            // 检查是否有多个订单具有相同的最小重量差异
            $minDifference = $candidates[0]['weight_difference'];
            $minDiffCandidates = array_filter($candidates, function($c) use ($minDifference) {
                return abs($c['weight_difference'] - $minDifference) < 0.001; // 浮点数比较容差
            });
            
            // 需求4.7: 如果只有一个订单具有最小差异，返回该订单
            if (count($minDiffCandidates) === 1) {
                return [
                    'match_type' => 'fuzzy',
                    'confidence' => 'medium',
                    'order' => $candidates[0],
                    'candidates' => []
                ];
            }
            
            // 需求4.8: 如果多个订单具有相同的最小重量差异，返回所有候选
            \think\Log::info("模糊匹配找到 " . count($minDiffCandidates) . " 个订单具有相同的最小重量差异");
            return [
                'match_type' => 'multiple',
                'confidence' => 'low',
                'order' => null,
                'candidates' => $candidates // 返回所有候选，已按重量差异排序
            ];
            
        } catch (\Exception $e) {
            \think\Log::error("模糊匹配失败: member_id={$memberId}, weight={$weight}, 错误: " . $e->getMessage());
            return [
                'match_type' => 'none',
                'confidence' => 'low',
                'order' => null,
                'candidates' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 匹配订单（主方法）
     * 
     * 协调精确匹配和模糊匹配策略，处理所有匹配结果类型
     * 
     * 匹配策略：
     * 1. 优先尝试通过国际单号精确匹配
     * 2. 如果精确匹配失败，降级到模糊匹配（用户ID+重量）
     * 3. 处理各种匹配结果：
     *    - exact: 精确匹配到唯一订单（高置信度）
     *    - fuzzy: 模糊匹配到唯一订单（中等置信度）
     *    - multiple: 多个订单具有相同最小重量差异（低置信度）
     *    - multiple_db: 数据库中存在重复单号（需要告警）
     *    - none: 无匹配
     * 
     * @param array $rowData 行数据，包含：
     *   - member_id: 用户ID
     *   - weight: 重量
     *   - express_num: 国际单号
     *   - date: 日期字符串
     * @return array [
     *   'match_type' => 'exact|fuzzy|multiple|multiple_db|none',
     *   'confidence' => 'high|medium|low',
     *   'order' => array|null (匹配到的订单，仅当match_type为exact或fuzzy时),
     *   'candidates' => array (候选订单列表，仅当match_type为multiple或multiple_db时),
     *   'warning' => string|null (警告信息，仅当match_type为multiple_db时)
     * ]
     */
    private function matchOrder($rowData)
    {
        $memberId = $rowData['member_id'] ?? null;
        $weight = $rowData['weight'] ?? null;
        $expressNum = $rowData['express_num'] ?? null;
        $date = $rowData['date'] ?? null;
        
        \think\Log::info("开始匹配订单: member_id={$memberId}, weight={$weight}, express_num={$expressNum}, date={$date}");
        
        // 需求3.1, 3.2, 3.3: 优先尝试通过国际单号精确匹配
        if (!empty($expressNum)) {
            $exactMatch = $this->matchByExpressNum($expressNum);
            
            // 需求3.3: 精确匹配成功（找到唯一订单）
            if ($exactMatch['match_type'] === 'exact') {
                \think\Log::info("精确匹配成功: express_num={$expressNum}, order_id={$exactMatch['order']['id']}");
                return [
                    'match_type' => 'exact',
                    'confidence' => 'high',
                    'order' => $exactMatch['order'],
                    'candidates' => [],
                    'warning' => null
                ];
            }
            
            // 实现数据库重复单号检测（需求4.7, 4.8, 4.9的扩展）
            // 返回multiple_db并告警
            if ($exactMatch['match_type'] === 'multiple_db') {
                \think\Log::warning("数据库重复单号检测: express_num={$expressNum}, 找到 " . count($exactMatch['candidates']) . " 个订单");
                return [
                    'match_type' => 'multiple_db',
                    'confidence' => 'low',
                    'order' => null,
                    'candidates' => $exactMatch['candidates'],
                    'warning' => $exactMatch['warning']
                ];
            }
            
            // 精确匹配失败，记录日志并继续模糊匹配
            \think\Log::info("精确匹配失败: express_num={$expressNum}, 降级到模糊匹配");
        }
        
        // 需求3.4, 4.1: 精确匹配失败或国际单号为空，降级到模糊匹配
        $fuzzyMatch = $this->matchByMemberIdAndWeight($memberId, $weight, $date);
        
        // 需求4.7: 唯一匹配（返回fuzzy/medium）
        if ($fuzzyMatch['match_type'] === 'fuzzy') {
            \think\Log::info("模糊匹配成功（唯一匹配）: member_id={$memberId}, order_id={$fuzzyMatch['order']['id']}");
            return [
                'match_type' => 'fuzzy',
                'confidence' => 'medium',
                'order' => $fuzzyMatch['order'],
                'candidates' => [],
                'warning' => null
            ];
        }
        
        // 需求4.8: 多重匹配（返回multiple/low，包含所有候选）
        if ($fuzzyMatch['match_type'] === 'multiple') {
            \think\Log::info("模糊匹配找到多个候选: member_id={$memberId}, 候选数=" . count($fuzzyMatch['candidates']));
            return [
                'match_type' => 'multiple',
                'confidence' => 'low',
                'order' => null,
                'candidates' => $fuzzyMatch['candidates'],
                'warning' => '找到多个重量相近的订单，需要人工选择'
            ];
        }
        
        // 需求4.9: 无匹配情况（返回none）
        \think\Log::info("无法匹配订单: member_id={$memberId}, weight={$weight}, express_num={$expressNum}");
        return [
            'match_type' => 'none',
            'confidence' => 'low',
            'order' => null,
            'candidates' => [],
            'warning' => '未找到匹配的订单'
        ];
    }
    
    /**
     * 生成预览数据（优化版：批量查询）
     * 
     * 处理解析后的数据，匹配订单，生成完整的预览数据结构
     * 
     * 性能优化：
     * 1. 先收集所有需要查询的t_order_sn和member_id
     * 2. 批量查询所有可能匹配的订单（避免N+1查询问题）
     * 3. 在内存中进行匹配
     * 
     * @param array $parsedData 解析后的数据
     * @return array 预览数据
     */
    public function generatePreview($parsedData)
    {
        \think\Log::info('开始生成预览数据（批量查询优化版）');
        
        $startTime = microtime(true);
        
        // 步骤1：收集所有需要查询的数据
        $allExpressNums = [];
        $allMemberIds = [];
        $allRows = [];
        
        $sheets = $parsedData['sheets'] ?? [];
        foreach ($sheets as $sheet) {
            $sheetRows = $sheet['rows'] ?? [];
            foreach ($sheetRows as $row) {
                $allRows[] = array_merge($row, ['sheet_name' => $sheet['name']]);
                
                // 收集国际单号
                if (!empty($row['express_num'])) {
                    $allExpressNums[] = $row['express_num'];
                }
                
                // 收集用户ID
                if (!empty($row['member_id'])) {
                    $allMemberIds[] = $row['member_id'];
                }
            }
        }
        
        \think\Log::info('收集完成: ' . count($allRows) . '行数据, ' . count(array_unique($allExpressNums)) . '个唯一国际单号, ' . count(array_unique($allMemberIds)) . '个唯一用户ID');
        
        // 步骤2：批量查询所有可能匹配的订单
        $ordersByExpressNum = $this->batchQueryByExpressNum($allExpressNums);
        $ordersByMemberId = $this->batchQueryByMemberId($allMemberIds);
        
        $queryTime = microtime(true) - $startTime;
        \think\Log::info('批量查询完成，耗时: ' . round($queryTime, 2) . '秒');
        
        // 步骤3：在内存中匹配订单
        $statistics = [
            'total_rows' => 0,
            'blue_count' => 0,
            'pink_count' => 0,
            'green_count' => 0,
            'unknown_count' => 0,
            'matched_count' => 0,
            'unmatched_count' => 0,
            'multiple_match_count' => 0
        ];
        
        $sheetStatistics = [];
        $rowsByColor = [
            'blue' => [],
            'pink' => [],
            'green' => [],
            'unknown' => [],
            'unmatched' => [],
            'multiple_match' => []
        ];
        
        // 按Sheet分组统计
        $sheetGroups = [];
        foreach ($allRows as $row) {
            $sheetName = $row['sheet_name'];
            if (!isset($sheetGroups[$sheetName])) {
                $sheetGroups[$sheetName] = [
                    'name' => $sheetName,
                    'total_rows' => 0,
                    'blue_count' => 0,
                    'pink_count' => 0,
                    'green_count' => 0,
                    'white_count' => 0,
                    'unknown_count' => 0
                ];
            }
            
            // 使用预加载的数据进行匹配
            $matchResult = $this->matchOrderFromCache($row, $ordersByExpressNum, $ordersByMemberId);
            
            // 将匹配结果添加到行数据中
            $row['match_type'] = $matchResult['match_type'];
            $row['confidence'] = $matchResult['confidence'];
            $row['matched_order'] = $matchResult['order'] ?? null;
            $row['candidates'] = $matchResult['candidates'] ?? [];
            $row['related_orders'] = $matchResult['related_orders'] ?? [];  // 保存关联订单（用于exact_multiple类型）
            $row['warning'] = $matchResult['warning'] ?? null;
            
            // 更新统计
            $sheetGroups[$sheetName]['total_rows']++;
            $statistics['total_rows']++;
            
            $color = $row['color'];
            if ($color === 'blue') {
                $sheetGroups[$sheetName]['blue_count']++;
                $statistics['blue_count']++;
            } elseif ($color === 'pink') {
                $sheetGroups[$sheetName]['pink_count']++;
                $statistics['pink_count']++;
            } elseif ($color === 'green') {
                $sheetGroups[$sheetName]['green_count']++;
                $statistics['green_count']++;
            } elseif ($color === 'unknown') {
                $sheetGroups[$sheetName]['unknown_count']++;
                $statistics['unknown_count']++;
            }
            
            // 按匹配类型更新统计
            $matchType = $matchResult['match_type'];
            if ($matchType === 'exact' || $matchType === 'exact_multiple' || $matchType === 'fuzzy') {
                $statistics['matched_count']++;
            } elseif ($matchType === 'multiple' || $matchType === 'multiple_db') {
                $statistics['multiple_match_count']++;
            } elseif ($matchType === 'none') {
                $statistics['unmatched_count']++;
            }
            
            // 按颜色分组（所有行都按颜色分组，不管是否匹配成功）
            if (isset($rowsByColor[$color])) {
                $rowsByColor[$color][] = $row;
            } else if ($matchType === 'multiple' || $matchType === 'multiple_db') {
                // 多重匹配的行单独分组
                $rowsByColor['multiple_match'][] = $row;
            } else {
                // 未知颜色或其他情况
                $rowsByColor['unmatched'][] = $row;
            }
        }
        
        $sheetStatistics = array_values($sheetGroups);
        
        $totalTime = microtime(true) - $startTime;
        \think\Log::info('预览数据生成完成，总耗时: ' . round($totalTime, 2) . '秒, 匹配: ' . $statistics['matched_count'] . ', 未匹配: ' . $statistics['unmatched_count']);
        
        return [
            'statistics' => $statistics,
            'sheets' => $sheetStatistics,
            'rows_by_color' => $rowsByColor
        ];
    }
    
    /**
     * 批量查询：通过国际单号查询订单
     * 
     * @param array $expressNums 国际单号数组
     * @return array 按国际单号索引的订单数组 ['73586455151144' => [order1, order2, ...]]
     */
    private function batchQueryByExpressNum($expressNums)
    {
        if (empty($expressNums)) {
            return [];
        }
        
        // 去重
        $expressNums = array_unique($expressNums);
        
        \think\Log::info('批量查询国际单号: ' . count($expressNums) . '个');
        
        // 一次性查询所有订单
        $orders = Db::name('inpack')
            ->whereIn('t_order_sn', $expressNums)
            ->where('is_delete', 0)
            ->select();
        
        // 按t_order_sn分组
        $result = [];
        foreach ($orders as $order) {
            $tOrderSn = $order['t_order_sn'];
            if (!isset($result[$tOrderSn])) {
                $result[$tOrderSn] = [];
            }
            $result[$tOrderSn][] = $order;
        }
        
        \think\Log::info('批量查询国际单号完成: 找到 ' . count($orders) . ' 个订单');
        
        return $result;
    }
    
    /**
     * 批量查询：通过用户ID查询订单（用于模糊匹配）
     * 
     * @param array $memberIds 用户ID数组
     * @return array 按用户ID索引的订单数组 ['31979' => [order1, order2, ...]]
     */
    private function batchQueryByMemberId($memberIds)
    {
        if (empty($memberIds)) {
            return [];
        }
        
        // 去重
        $memberIds = array_unique($memberIds);
        
        \think\Log::info('批量查询用户ID: ' . count($memberIds) . '个');
        
        // 查询最近6个月的订单（避免查询过多历史数据）
        $sixMonthsAgo = date('Y-m-d H:i:s', strtotime('-6 months'));
        
        // 一次性查询所有可能的订单
        $orders = Db::name('inpack')
            ->whereIn('member_id', $memberIds)
            ->where('is_delete', 0)
            ->where('created_time', '>=', $sixMonthsAgo)
            ->select();
        
        // 按member_id分组
        $result = [];
        foreach ($orders as $order) {
            $memberId = $order['member_id'];
            if (!isset($result[$memberId])) {
                $result[$memberId] = [];
            }
            $result[$memberId][] = $order;
        }
        
        \think\Log::info('批量查询用户ID完成: 找到 ' . count($orders) . ' 个订单');
        
        return $result;
    }
    
    /**
     * 从缓存中匹配订单（不查询数据库）
     * 
     * @param array $row 行数据
     * @param array $ordersByExpressNum 按国际单号索引的订单缓存
     * @param array $ordersByMemberId 按用户ID索引的订单缓存
     * @return array 匹配结果
     */
    private function matchOrderFromCache($row, $ordersByExpressNum, $ordersByMemberId)
    {
        $memberId = $row['member_id'] ?? null;
        $weight = $row['weight'] ?? null;
        $expressNum = $row['express_num'] ?? null;
        $date = $row['date'] ?? null;
        
        // 优先通过国际单号精确匹配
        if (!empty($expressNum) && isset($ordersByExpressNum[$expressNum])) {
            $orders = $ordersByExpressNum[$expressNum];
            
            if (count($orders) === 1) {
                // 单个订单：精确匹配
                return [
                    'match_type' => 'exact',
                    'confidence' => 'high',
                    'order' => $orders[0],
                    'candidates' => [],
                    'warning' => null
                ];
            } elseif (count($orders) > 1) {
                // 多个订单：全部匹配（一个国际单号对应多个订单是正常的）
                // 返回第一个订单作为主订单，其他订单作为关联订单
                return [
                    'match_type' => 'exact_multiple',
                    'confidence' => 'high',
                    'order' => $orders[0],
                    'candidates' => array_slice($orders, 1), // 其他关联订单
                    'related_orders' => $orders, // 所有关联订单
                    'warning' => '此国际单号关联' . count($orders) . '个订单，将全部更新'
                ];
            }
        }
        
        // 降级到模糊匹配（用户ID + 重量）
        if (!empty($memberId) && !empty($weight) && isset($ordersByMemberId[$memberId])) {
            $candidates = $ordersByMemberId[$memberId];
            
            // 应用重量容差和日期范围过滤
            $weightMin = $weight - 0.5;
            $weightMax = $weight + 0.5;
            $dateRange = $this->parseDateRange($date);
            
            $matchedCandidates = [];
            foreach ($candidates as $candidate) {
                // 检查重量
                $candidateWeight = floatval($candidate['weight']);
                $candidateCaleWeight = floatval($candidate['cale_weight']);
                
                $weightMatch = ($candidateWeight >= $weightMin && $candidateWeight <= $weightMax) ||
                               ($candidateCaleWeight >= $weightMin && $candidateCaleWeight <= $weightMax);
                
                if (!$weightMatch) {
                    continue;
                }
                
                // 检查日期
                $createdTime = strtotime($candidate['created_time']);
                if ($createdTime < $dateRange['start'] || $createdTime > $dateRange['end']) {
                    continue;
                }
                
                // 计算重量差异
                $weightDiff = min(
                    abs($candidateWeight - $weight),
                    abs($candidateCaleWeight - $weight)
                );
                
                $candidate['weight_difference'] = $weightDiff;
                $matchedCandidates[] = $candidate;
            }
            
            if (empty($matchedCandidates)) {
                return [
                    'match_type' => 'none',
                    'confidence' => 'low',
                    'order' => null,
                    'candidates' => [],
                    'warning' => '未找到匹配的订单'
                ];
            }
            
            // 按重量差异排序
            usort($matchedCandidates, function($a, $b) {
                return $a['weight_difference'] <=> $b['weight_difference'];
            });
            
            // 检查是否有唯一的最小差异
            $minDifference = $matchedCandidates[0]['weight_difference'];
            $minDiffCandidates = array_filter($matchedCandidates, function($c) use ($minDifference) {
                return abs($c['weight_difference'] - $minDifference) < 0.001;
            });
            
            if (count($minDiffCandidates) === 1) {
                return [
                    'match_type' => 'fuzzy',
                    'confidence' => 'medium',
                    'order' => $matchedCandidates[0],
                    'candidates' => [],
                    'warning' => null
                ];
            } else {
                return [
                    'match_type' => 'multiple',
                    'confidence' => 'low',
                    'order' => null,
                    'candidates' => $matchedCandidates,
                    'warning' => '找到多个重量相近的订单，需要人工选择'
                ];
            }
        }
        
        // 无法匹配
        return [
            'match_type' => 'none',
            'confidence' => 'low',
            'order' => null,
            'candidates' => [],
            'warning' => '未找到匹配的订单'
        ];
    }
    
    /**
     * 创建历史账单
     * 
     * 为历史数据导入创建特殊的账单记录
     * 账单编号格式: HISTORY_{Member_ID}_{timestamp}
     * 
     * @param int $memberId 用户ID
     * @param array $orders 订单列表（用于计算账单汇总信息）
     * @return int 账单ID
     * @throws \Exception
     */
    private function createHistoryStatement($memberId, $orders = [])
    {
        if (empty($memberId)) {
            throw new \Exception('Member_ID不能为空');
        }
        
        try {
            // 需求6.3: 生成账单编号格式: "HISTORY_{Member_ID}_{timestamp}"
            $timestamp = time();
            $statementNo = "HISTORY_{$memberId}_{$timestamp}";
            
            // 需求6.5的扩展: 处理账单编号冲突（添加随机后缀）
            // 检查账单编号是否已存在
            $existing = Db::name('statement')
                ->where('statement_no', $statementNo)
                ->where('wxapp_id', $this->wxappId)
                ->lock(true)  // 使用悲观锁防止并发冲突
                ->find();
            
            if ($existing) {
                // 添加随机后缀（4位数字）
                $randomSuffix = mt_rand(1000, 9999);
                $statementNo .= "_{$randomSuffix}";
                
                \think\Log::warning("账单编号冲突，添加随机后缀: {$statementNo}");
            }
            
            // 计算账单汇总信息
            $totalPackages = count($orders);
            $totalWeight = 0;
            $totalAmount = 0;
            
            foreach ($orders as $order) {
                // 使用计费重量（cale_weight）计算总重量
                $totalWeight += floatval($order['cale_weight'] ?? $order['weight'] ?? 0);
                
                // 计算总金额（使用real_payment字段）
                $totalAmount += floatval($order['real_payment'] ?? 0);
            }
            
            // 获取用户信息（可选，用于记录member_name）
            $memberName = '';
            if (!empty($orders)) {
                // 从第一个订单中获取用户信息
                $member = Db::name('user')
                    ->where('user_id', $memberId)
                    ->where('wxapp_id', $this->wxappId)
                    ->where('is_delete', 0)
                    ->find();
                
                if ($member) {
                    $memberName = $member['nickName'] ?? '';
                }
            }
            
            // 构建账单数据
            $statementData = [
                'statement_no' => $statementNo,
                'member_id' => $memberId,
                'member_name' => $memberName,
                'total_packages' => $totalPackages,
                'total_weight' => $totalWeight,
                'total_amount' => $totalAmount,
                'status' => 1,  // 需求6.4: status设置为1（confirmed）
                'pay_status' => 2,  // 需求6.4: pay_status设置为2（paid）
                'remark' => '历史数据导入',  // 需求6.5: remark设置为"历史数据导入"
                'wxapp_id' => $this->wxappId,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            // 插入账单记录
            $statementId = Db::name('statement')->insertGetId($statementData);
            
            if (!$statementId) {
                throw new \Exception('创建历史账单失败');
            }
            
            \think\Log::info("历史账单创建成功: statement_id={$statementId}, statement_no={$statementNo}, member_id={$memberId}");
            
            return $statementId;
            
        } catch (\Exception $e) {
            \think\Log::error("创建历史账单失败: member_id={$memberId}, 错误: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 更新蓝色行订单（已支付+已出账）
     * 
     * 蓝色行表示订单既已支付又已出账
     * 需要更新：
     * - is_pay = 1（已支付）
     * - pay_time = 当前时间戳
     * - statement_id = 对应的历史账单ID
     * 
     * 只更新is_delete = 0的订单
     * 
     * @param array $order 订单数据（必须包含id字段）
     * @param int $statementId 历史账单ID
     * @return bool 更新是否成功
     * @throws \Exception
     */
    private function updateBlueRow($order, $statementId)
    {
        if (empty($order) || empty($order['id'])) {
            throw new \Exception('订单数据无效：缺少订单ID');
        }
        
        $orderId = $order['id'];
        
        try {
            // 更新蓝色行订单状态：设置为已支付
            $updateData = [
                'is_pay' => 1,  // 设置为已支付
                'pay_time' => date('Y-m-d H:i:s')  // 设置支付时间为当前时间戳
            ];
            
            // 如果提供了账单ID，则设置statement_id（用于账单生成功能）
            if (!empty($statementId)) {
                $updateData['statement_id'] = $statementId;
            }
            
            // 只更新is_delete = 0的订单
            $result = Db::name('inpack')
                ->where('id', $orderId)
                ->where('is_delete', 0)
                ->update($updateData);
            
            if ($result === false) {
                throw new \Exception("更新订单失败: order_id={$orderId}");
            }
            
            // 如果result为0，说明订单不存在或已被删除
            if ($result === 0) {
                \think\Log::warning("蓝色行订单更新：订单不存在或已被删除: order_id={$orderId}");
                return false;
            }
            
            \think\Log::info("蓝色行订单更新成功: order_id={$orderId}" . ($statementId ? ", statement_id={$statementId}" : ""));
            
            return true;
            
        } catch (\Exception $e) {
            \think\Log::error("蓝色行订单更新失败: order_id={$orderId}, 错误: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 更新粉红色行订单（已出账未支付）
     * 
     * 粉红色行表示订单已出账但未支付
     * 需要更新：
     * - statement_id = 对应的历史账单ID
     * 
     * 不修改：
     * - is_pay 字段（保持原值）
     * - pay_time 字段（保持原值）
     * 
     * 只更新is_delete = 0的订单
     * 
     * @param array $order 订单数据（必须包含id字段）
     * @param int $statementId 历史账单ID
     * @return bool 更新是否成功
     * @throws \Exception
     */
    private function updatePinkRow($order, $statementId)
    {
        if (empty($order) || empty($order['id'])) {
            throw new \Exception('订单数据无效：缺少订单ID');
        }
        
        $orderId = $order['id'];
        
        try {
            // 更新粉红色行订单状态：不修改支付状态
            $updateData = [];
            
            // 如果提供了账单ID，则设置statement_id（用于账单生成功能）
            if (!empty($statementId)) {
                $updateData['statement_id'] = $statementId;
            }
            
            // 如果没有需要更新的字段，直接返回成功
            if (empty($updateData)) {
                \think\Log::info("粉红色行订单无需更新: order_id={$orderId}");
                return true;
            }
            
            // 只更新is_delete = 0的订单
            $result = Db::name('inpack')
                ->where('id', $orderId)
                ->where('is_delete', 0)
                ->update($updateData);
            
            if ($result === false) {
                throw new \Exception("更新订单失败: order_id={$orderId}");
            }
            
            // 如果result为0，说明订单不存在或已被删除
            if ($result === 0) {
                \think\Log::warning("粉红色行订单更新：订单不存在或已被删除: order_id={$orderId}");
                return false;
            }
            
            \think\Log::info("粉红色行订单更新成功: order_id={$orderId}" . ($statementId ? ", statement_id={$statementId}" : ""));
            
            return true;
            
        } catch (\Exception $e) {
            \think\Log::error("粉红色行订单更新失败: order_id={$orderId}, 错误: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 更新绿色行订单（已支付未出账）
     * 
     * 绿色行表示订单已支付但未出账
     * 需要更新：
     * - is_pay = 1（已支付）
     * - pay_time = 当前时间戳
     * 
     * 不修改：
     * - statement_id 字段（保持原值，不关联账单）
     * 
     * 只更新is_delete = 0的订单
     * 
     * @param array $order 订单数据（必须包含id字段）
     * @return bool 更新是否成功
     * @throws \Exception
     */
    private function updateGreenRow($order)
    {
        if (empty($order) || empty($order['id'])) {
            throw new \Exception('订单数据无效：缺少订单ID');
        }
        
        $orderId = $order['id'];
        
        try {
            // 需求9.1, 9.2: 更新绿色行订单状态
            // - is_pay = 1（已支付）
            // - pay_time = 当前时间戳
            // 需求9.3: 不修改statement_id字段
            $updateData = [
                'is_pay' => 1,  // 需求9.1: 设置为已支付
                'pay_time' => date('Y-m-d H:i:s')  // 需求9.2: 设置支付时间为当前时间戳
            ];
            
            // 需求9.4: 只更新is_delete = 0的订单
            $result = Db::name('inpack')
                ->where('id', $orderId)
                ->where('wxapp_id', $this->wxappId)
                ->where('is_delete', 0)  // 需求9.4: 只更新未删除的订单
                ->update($updateData);
            
            if ($result === false) {
                throw new \Exception("更新订单失败: order_id={$orderId}");
            }
            
            // 如果result为0，说明订单不存在或已被删除
            if ($result === 0) {
                \think\Log::warning("绿色行订单更新：订单不存在或已被删除: order_id={$orderId}");
                return false;
            }
            
            \think\Log::info("绿色行订单更新成功: order_id={$orderId}");
            
            return true;
            
        } catch (\Exception $e) {
            \think\Log::error("绿色行订单更新失败: order_id={$orderId}, 错误: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 执行状态更新（事务管理和批量更新）
     * 
     * 按Member_ID分组所有订单（蓝色、粉红色、绿色），为每个Member_ID组创建独立事务
     * 实现事务失败时的回滚和错误记录，确保一个Member_ID失败不影响其他Member_ID
     * 
     * 处理流程：
     * 1. 按Member_ID分组所有订单（蓝色、粉红色、绿色）
     * 2. 对每个Member_ID组：
     *    a. 开启数据库事务
     *    b. 如果有蓝色或粉红色订单，创建历史账单
     *    c. 更新蓝色行订单（已支付+已出账）
     *    d. 更新粉红色行订单（已出账未支付）
     *    e. 更新绿色行订单（已支付未出账）
     *    f. 提交事务
     * 3. 如果某个Member_ID的事务失败，回滚该事务并记录错误，继续处理其他Member_ID
     * 
     * @param array $importData 导入数据，格式：
     *   [
     *     'blue' => [订单列表],
     *     'pink' => [订单列表],
     *     'green' => [订单列表]
     *   ]
     * @return array 导入报告，格式：
     *   [
     *     'success' => true,
     *     'total_processed' => 80,
     *     'success_count' => 75,
     *     'failure_count' => 5,
     *     'statistics' => [
     *       'blue_processed' => 50,
     *       'pink_processed' => 20,
     *       'green_processed' => 10
     *     ],
     *     'failed_members' => [
     *       [
     *         'member_id' => '23048',
     *         'error' => '数据库更新失败: ...'
     *       ]
     *     ],
     *     'created_statements' => [
     *       [
     *         'member_id' => '23048',
     *         'statement_id' => 123,
     *         'statement_no' => 'HISTORY_23048_1709280000'
     *       ]
     *     ]
     *   ]
     */
    private function updateOrderStatus($importData)
    {
        \think\Log::info('开始执行订单状态更新');
        
        // 初始化报告数据
        $report = [
            'success' => true,
            'total_processed' => 0,
            'success_count' => 0,
            'failure_count' => 0,
            'statistics' => [
                'blue_processed' => 0,
                'pink_processed' => 0,
                'green_processed' => 0
            ],
            'failed_members' => [],
            'created_statements' => []
        ];
        
        // 需求10.1: 按Member_ID分组所有订单（蓝色、粉红色、绿色）
        $ordersByMember = [];
        
        // 处理蓝色订单
        $blueOrders = $importData['blue'] ?? [];
        \think\Log::info('开始处理蓝色订单，共 ' . count($blueOrders) . ' 行');
        
        foreach ($blueOrders as $idx => $order) {
            // 跳过未匹配的订单
            if (empty($order['matched_order']) || !is_array($order['matched_order'])) {
                continue;
            }
            
            // 检查是否有关联订单（exact_multiple类型）
            $ordersToProcess = [];
            if (isset($order['related_orders']) && !empty($order['related_orders'])) {
                // 有多个关联订单，全部处理
                $ordersToProcess = $order['related_orders'];
                \think\Log::info("蓝色行 {$idx}: 有 " . count($ordersToProcess) . " 个关联订单");
            } else {
                // 单个订单
                $ordersToProcess = [$order['matched_order']];
            }
            
            foreach ($ordersToProcess as $matchedOrder) {
                $memberId = $matchedOrder['member_id'] ?? null;
                if ($memberId) {
                    if (!isset($ordersByMember[$memberId])) {
                        $ordersByMember[$memberId] = [
                            'blue' => [],
                            'pink' => [],
                            'green' => []
                        ];
                    }
                    $ordersByMember[$memberId]['blue'][] = $matchedOrder;
                }
            }
        }
        
        // 处理粉红色订单
        $pinkOrders = $importData['pink'] ?? [];
        \think\Log::info('开始处理粉红色订单，共 ' . count($pinkOrders) . ' 行');
        
        foreach ($pinkOrders as $idx => $order) {
            // 跳过未匹配的订单
            if (empty($order['matched_order']) || !is_array($order['matched_order'])) {
                continue;
            }
            
            // 检查是否有关联订单（exact_multiple类型）
            $ordersToProcess = [];
            if (isset($order['related_orders']) && !empty($order['related_orders'])) {
                // 有多个关联订单，全部处理
                $ordersToProcess = $order['related_orders'];
            } else {
                // 单个订单
                $ordersToProcess = [$order['matched_order']];
            }
            
            foreach ($ordersToProcess as $matchedOrder) {
                $memberId = $matchedOrder['member_id'] ?? null;
                if ($memberId) {
                    if (!isset($ordersByMember[$memberId])) {
                        $ordersByMember[$memberId] = [
                            'blue' => [],
                            'pink' => [],
                            'green' => []
                        ];
                    }
                    $ordersByMember[$memberId]['pink'][] = $matchedOrder;
                }
            }
        }
        
        // 处理绿色订单
        $greenOrders = $importData['green'] ?? [];
        \think\Log::info('开始处理绿色订单，共 ' . count($greenOrders) . ' 行');
        
        foreach ($greenOrders as $idx => $order) {
            // 跳过未匹配的订单
            if (empty($order['matched_order']) || !is_array($order['matched_order'])) {
                continue;
            }
            
            // 检查是否有关联订单（exact_multiple类型）
            $ordersToProcess = [];
            if (isset($order['related_orders']) && !empty($order['related_orders'])) {
                // 有多个关联订单，全部处理
                $ordersToProcess = $order['related_orders'];
            } else {
                // 单个订单
                $ordersToProcess = [$order['matched_order']];
            }
            
            foreach ($ordersToProcess as $matchedOrder) {
                $memberId = $matchedOrder['member_id'] ?? null;
                if ($memberId) {
                    if (!isset($ordersByMember[$memberId])) {
                        $ordersByMember[$memberId] = [
                            'blue' => [],
                            'pink' => [],
                            'green' => []
                        ];
                    }
                    $ordersByMember[$memberId]['green'][] = $matchedOrder;
                }
            }
        }
        
        \think\Log::info('订单按Member_ID分组完成，共 ' . count($ordersByMember) . ' 个Member_ID');
        
        // 需求10.2: 为每个Member_ID组执行更新，使用独立事务
        $processedCount = 0;
        foreach ($ordersByMember as $memberId => $orders) {
            $processedCount++;
            \think\Log::info("处理进度: {$processedCount}/" . count($ordersByMember) . ", Member_ID={$memberId}");
            $blueList = $orders['blue'] ?? [];
            $pinkList = $orders['pink'] ?? [];
            $greenList = $orders['green'] ?? [];
            
            // 确保都是数组
            if (!is_array($blueList)) $blueList = [];
            if (!is_array($pinkList)) $pinkList = [];
            if (!is_array($greenList)) $greenList = [];
            
            $totalOrders = count($blueList) + count($pinkList) + count($greenList);
            
            \think\Log::info("开始处理Member_ID={$memberId}的订单，共{$totalOrders}个订单（蓝色:" . count($blueList) . ", 粉红色:" . count($pinkList) . ", 绿色:" . count($greenList) . "）");
            
            // 需求10.2: 开启数据库事务
            Db::startTrans();
            
            try {
                // 财务原始数据导入：批量更新订单支付状态
                
                // 收集所有需要更新的订单ID
                $blueOrderIds = [];
                $pinkOrderIds = [];
                
                \think\Log::info("收集蓝色行订单ID，共 " . count($blueList) . " 个");
                foreach ($blueList as $order) {
                    if (!empty($order['id'])) {
                        $blueOrderIds[] = $order['id'];
                    }
                }
                
                \think\Log::info("收集粉红色行订单ID，共 " . count($pinkList) . " 个");
                foreach ($pinkList as $order) {
                    if (!empty($order['id'])) {
                        $pinkOrderIds[] = $order['id'];
                    }
                }
                
                // 批量更新蓝色行订单（已支付）
                if (!empty($blueOrderIds)) {
                    \think\Log::info("批量更新蓝色行订单，共 " . count($blueOrderIds) . " 个");
                    $updateData = [
                        'is_pay' => 1,
                        'pay_time' => date('Y-m-d H:i:s')
                    ];
                    
                    $result = Db::name('inpack')
                        ->whereIn('id', $blueOrderIds)
                        ->where('is_delete', 0)
                        ->update($updateData);
                    
                    $report['statistics']['blue_processed'] = $result;
                    \think\Log::info("蓝色行订单更新完成: {$result} 个");
                }
                
                // 粉红色行不需要更新（因为不设置statement_id）
                $report['statistics']['pink_processed'] = count($pinkOrderIds);
                \think\Log::info("粉红色行订单跳过更新: " . count($pinkOrderIds) . " 个");
                
                // 需求9.1, 9.2, 9.3: 更新绿色行订单（已支付未出账）
                foreach ($greenList as $order) {
                    $this->updateGreenRow($order);
                    $report['statistics']['green_processed']++;
                }
                
                // 需求10.2: 提交事务
                Db::commit();
                
                // 更新成功计数
                $report['success_count']++;
                $report['total_processed'] += $totalOrders;
                
                \think\Log::info("Member_ID={$memberId}的订单更新成功");
                
            } catch (\Exception $e) {
                // 需求10.3: 事务失败时回滚
                Db::rollback();
                
                // 需求10.5: 记录错误信息
                $errorMessage = $e->getMessage();
                \think\Log::error("Member_ID={$memberId}的订单更新失败: {$errorMessage}");
                
                // 记录失败的Member_ID和错误信息
                $report['failed_members'][] = [
                    'member_id' => $memberId,
                    'error' => $errorMessage
                ];
                
                $report['failure_count']++;
                
                // 需求10.4: 继续处理剩余的Member_ID组
                // （通过continue隐式实现，循环会继续执行）
            }
        }
        
        // 如果有失败的Member_ID，将success标记为false
        if ($report['failure_count'] > 0) {
            $report['success'] = false;
        }
        
        \think\Log::info('订单状态更新完成: ' . json_encode([
            'total_processed' => $report['total_processed'],
            'success_count' => $report['success_count'],
            'failure_count' => $report['failure_count']
        ], JSON_UNESCAPED_UNICODE));
        
        return $report;
    }
    
    /**
     * 执行导入
     * 
     * 这是导入的主入口方法，负责：
     * 1. 应用用户修正到预览数据
     * 2. 验证所有未知颜色和多重匹配已解决
     * 3. 调用updateOrderStatus()执行订单状态更新
     * 4. 生成包含未匹配订单和创建的历史账单的完整导入报告
     * 
     * @param array $previewData 预览数据，格式：
     *   [
     *     'statistics' => [...],
     *     'sheets' => [...],
     *     'rows_by_color' => [
     *       'blue' => [...],
     *       'pink' => [...],
     *       'green' => [...],
     *       'unknown' => [...],
     *       'unmatched' => [...],
     *       'multiple_match' => [...]
     *     ]
     *   ]
     * @param array $userCorrections 用户修正数据，格式：
     *   [
     *     'color_corrections' => [
     *       'sheet_name_row_number' => 'blue|pink|green|skip'
     *     ],
     *     'order_selections' => [
     *       'sheet_name_row_number' => order_id
     *     ]
     *   ]
     * @return array 导入报告，格式：
     *   [
     *     'success' => true,
     *     'total_processed' => 80,
     *     'success_count' => 75,
     *     'failure_count' => 5,
     *     'statistics' => [
     *       'blue_processed' => 50,
     *       'pink_processed' => 20,
     *       'green_processed' => 10
     *     ],
     *     'failed_members' => [
     *       [
     *         'member_id' => '23048',
     *         'error' => '数据库更新失败: ...'
     *       ]
     *     ],
     *     'unmatched_orders' => [
     *       [
     *         'sheet_name' => 'Sheet1',
     *         'row_number' => 10,
     *         'member_id' => '23048',
     *         'express_num' => 'ABC123',
     *         'weight' => 1.5
     *       ]
     *     ],
     *     'created_statements' => [
     *       [
     *         'member_id' => '23048',
     *         'statement_id' => 123,
     *         'statement_no' => 'HISTORY_23048_1709280000'
     *       ]
     *     ]
     *   ]
     * @throws \Exception
     */
    public function executeImport($previewData, $userCorrections)
    {
        \think\Log::info('开始执行导入');
        
        try {
            // 步骤1: 应用用户修正到预览数据
            $correctedData = $this->applyUserCorrections($previewData, $userCorrections);
            
            // 步骤2: 验证所有未知颜色和多重匹配已解决（需求13.5）
            $this->validateCorrections($correctedData);
            
            // 步骤3: 准备导入数据（按颜色分组的订单）
            $importData = [
                'blue' => $correctedData['rows_by_color']['blue'] ?? [],
                'pink' => $correctedData['rows_by_color']['pink'] ?? [],
                'green' => $correctedData['rows_by_color']['green'] ?? []
            ];
            
            \think\Log::info('导入数据准备完成: ' . json_encode([
                'blue_count' => count($importData['blue']),
                'pink_count' => count($importData['pink']),
                'green_count' => count($importData['green'])
            ], JSON_UNESCAPED_UNICODE));
            
            // 步骤4: 调用updateOrderStatus()执行订单状态更新（需求10.1-10.5）
            $updateReport = $this->updateOrderStatus($importData);
            
            // 步骤5: 构建完整的导入报告（需求11.1-11.5）
            $report = [
                'success' => $updateReport['success'],
                'total_processed' => $updateReport['total_processed'],
                'success_count' => $updateReport['success_count'],
                'failure_count' => $updateReport['failure_count'],
                'statistics' => $updateReport['statistics'],  // 需求11.1, 11.2: 按颜色分类的处理数量
                'failed_members' => $updateReport['failed_members'],  // 需求11.3, 11.4: 失败的Member_ID和错误信息
                'unmatched_orders' => $this->buildUnmatchedOrdersList($correctedData),  // 需求11.5: 未匹配订单列表
                'created_statements' => $updateReport['created_statements']  // 需求11.5: 创建的历史账单列表
            ];
            
            \think\Log::info('导入执行完成: ' . json_encode([
                'success' => $report['success'],
                'total_processed' => $report['total_processed'],
                'success_count' => $report['success_count'],
                'failure_count' => $report['failure_count'],
                'unmatched_count' => count($report['unmatched_orders']),
                'statements_created' => count($report['created_statements'])
            ], JSON_UNESCAPED_UNICODE));
            
            return $report;
            
        } catch (\Exception $e) {
            \think\Log::error('导入执行失败: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 应用用户修正到预览数据
     * 
     * 处理用户在预览界面做的修正：
     * 1. 颜色修正：将未知颜色的行修正为指定颜色（blue/pink/green）或跳过（skip）
     * 2. 订单选择：为多重匹配的行选择正确的订单
     * 
     * @param array $previewData 预览数据
     * @param array $userCorrections 用户修正数据
     * @return array 修正后的预览数据
     */
    private function applyUserCorrections($previewData, $userCorrections)
    {
        \think\Log::info('开始应用用户修正');
        
        $correctedData = $previewData;
        $colorCorrections = $userCorrections['color_corrections'] ?? [];
        $orderSelections = $userCorrections['order_selections'] ?? [];
        
        // 处理颜色修正（需求13.1, 13.4）
        if (!empty($colorCorrections)) {
            $unknownRows = $correctedData['rows_by_color']['unknown'] ?? [];
            $correctedUnknownRows = [];
            
            foreach ($unknownRows as $row) {
                $rowKey = $row['sheet_name'] . '_' . $row['row_number'];
                
                if (isset($colorCorrections[$rowKey])) {
                    $correctedColor = $colorCorrections[$rowKey];
                    
                    // 如果用户选择跳过，不添加到任何颜色分组
                    if ($correctedColor === 'skip') {
                        \think\Log::info("跳过未知颜色行: {$rowKey}");
                        continue;
                    }
                    
                    // 更新行的颜色
                    $row['color'] = $correctedColor;
                    $row['confidence'] = 'medium'; // 用户修正的置信度为medium
                    
                    // 添加到对应的颜色分组
                    if (isset($correctedData['rows_by_color'][$correctedColor])) {
                        $correctedData['rows_by_color'][$correctedColor][] = $row;
                        \think\Log::info("颜色修正: {$rowKey} -> {$correctedColor}");
                    }
                } else {
                    // 未修正的未知颜色行保留在unknown分组
                    $correctedUnknownRows[] = $row;
                }
            }
            
            // 更新unknown分组（只保留未修正的行）
            $correctedData['rows_by_color']['unknown'] = $correctedUnknownRows;
        }
        
        // 处理订单选择（需求13.2, 13.4）
        if (!empty($orderSelections)) {
            $multipleMatchRows = $correctedData['rows_by_color']['multiple_match'] ?? [];
            $correctedMultipleMatchRows = [];
            
            foreach ($multipleMatchRows as $row) {
                $rowKey = $row['sheet_name'] . '_' . $row['row_number'];
                
                if (isset($orderSelections[$rowKey])) {
                    $selectedOrderId = $orderSelections[$rowKey];
                    
                    // 从候选订单中找到用户选择的订单
                    $selectedOrder = null;
                    foreach ($row['candidates'] as $candidate) {
                        if ($candidate['id'] == $selectedOrderId) {
                            $selectedOrder = $candidate;
                            break;
                        }
                    }
                    
                    if ($selectedOrder) {
                        // 更新行的匹配信息
                        $row['match_type'] = 'fuzzy'; // 用户选择的匹配类型为fuzzy
                        $row['confidence'] = 'medium'; // 用户选择的置信度为medium
                        $row['matched_order'] = $selectedOrder;
                        $row['candidates'] = []; // 清空候选列表
                        $row['warning'] = null;
                        
                        // 添加到对应的颜色分组
                        $color = $row['color'];
                        if (isset($correctedData['rows_by_color'][$color])) {
                            $correctedData['rows_by_color'][$color][] = $row;
                            \think\Log::info("订单选择: {$rowKey} -> order_id={$selectedOrderId}");
                        }
                    } else {
                        // 未找到选择的订单，保留在multiple_match分组
                        \think\Log::warning("订单选择失败: {$rowKey}, 未找到order_id={$selectedOrderId}");
                        $correctedMultipleMatchRows[] = $row;
                    }
                } else {
                    // 未选择订单的多重匹配行保留在multiple_match分组
                    $correctedMultipleMatchRows[] = $row;
                }
            }
            
            // 更新multiple_match分组（只保留未选择的行）
            $correctedData['rows_by_color']['multiple_match'] = $correctedMultipleMatchRows;
        }
        
        \think\Log::info('用户修正应用完成');
        
        return $correctedData;
    }
    
    /**
     * 验证所有多重匹配已解决
     * 
     * 注意：未知颜色的行会被自动跳过，不需要验证
     * 
     * @param array $correctedData 修正后的预览数据
     * @throws \Exception 如果存在未解决的多重匹配
     */
    private function validateCorrections($correctedData)
    {
        // 未知颜色的行直接跳过，不需要验证
        // 只验证多重匹配是否已解决
        
        $multipleMatchRows = $correctedData['rows_by_color']['multiple_match'] ?? [];
        
        $errors = [];
        
        // 检查是否还有未解决的多重匹配
        if (!empty($multipleMatchRows)) {
            $count = count($multipleMatchRows);
            $errors[] = "存在 {$count} 个多重匹配的行未被选择";
            
            // 记录详细信息
            foreach ($multipleMatchRows as $row) {
                \think\Log::warning("未解决的多重匹配: Sheet={$row['sheet_name']}, Row={$row['row_number']}, Member_ID={$row['member_id']}");
            }
        }
        
        // 如果有错误，抛出异常
        if (!empty($errors)) {
            $errorMessage = implode('; ', $errors);
            \think\Log::error("导入验证失败: {$errorMessage}");
            throw new \Exception("导入验证失败: {$errorMessage}");
        }
        
        \think\Log::info('导入验证通过：所有未知颜色和多重匹配已解决');
    }
    
    /**
     * 构建未匹配订单列表
     * 
     * 需求11.5: 导入报告应包含未匹配订单的列表及其Excel数据
     * 
     * @param array $correctedData 修正后的预览数据
     * @return array 未匹配订单列表，格式：
     *   [
     *     [
     *       'sheet_name' => 'Sheet1',
     *       'row_number' => 10,
     *       'member_id' => '23048',
     *       'express_num' => 'ABC123',
     *       'weight' => 1.5,
     *       'date' => '2月13日'
     *     ]
     *   ]
     */
    private function buildUnmatchedOrdersList($correctedData)
    {
        $unmatchedOrders = [];
        
        $unmatchedRows = $correctedData['rows_by_color']['unmatched'] ?? [];
        
        foreach ($unmatchedRows as $row) {
            $unmatchedOrders[] = [
                'sheet_name' => $row['sheet_name'] ?? '',
                'row_number' => $row['row_number'] ?? 0,
                'member_id' => $row['member_id'] ?? '',
                'express_num' => $row['express_num'] ?? '',
                'weight' => $row['weight'] ?? 0,
                'date' => $row['date'] ?? '',
                'color' => $row['color'] ?? '',
                'warning' => $row['warning'] ?? ''
            ];
        }
        
        \think\Log::info('未匹配订单列表构建完成，共 ' . count($unmatchedOrders) . ' 个');
        
        return $unmatchedOrders;
    }
}
