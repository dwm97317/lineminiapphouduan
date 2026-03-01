<?php

namespace app\store\controller\payment;

use app\store\controller\Controller;
use app\store\service\payment\PaymentImportService;

/**
 * 财务原始数据导入控制器
 * 
 * 负责处理历史订单支付状态的Excel导入功能
 * 只允许财务管理员访问
 */
class Import extends Controller
{
    /**
     * @var PaymentImportService 导入服务实例
     */
    private $service;
    
    /**
     * 构造函数
     * 初始化服务层实例
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = new PaymentImportService();
        
        // 临时：清除OPcache以确保使用最新代码
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }
    
    /**
     * 导入页面
     * GET /store/payment.import/index
     * 
     * 显示文件上传和导入界面
     */
    public function index()
    {
        return $this->fetch();
    }
    
    /**
     * 文件上传接口
     * POST /store/payment.import/upload
     * 
     * 处理Excel文件上传，验证文件格式，解析文件内容
     * 
     * 需求: 12.1, 12.2, 12.3, 12.4
     * 
     * @return array JSON响应
     */
    public function upload()
    {
        try {
            // 获取上传的文件
            $file = $this->request->file('file');
            
            if (!$file) {
                return $this->renderError('请选择要上传的文件');
            }
            
            // 验证文件大小（最大10MB）
            $maxSize = 10 * 1024 * 1024; // 10MB
            if ($file->getSize() > $maxSize) {
                return $this->renderError('文件大小不能超过10MB');
            }
            
            // 验证MIME类型
            $allowedMimes = [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/octet-stream'
            ];
            
            $fileMime = $file->getMime();
            if (!in_array($fileMime, $allowedMimes)) {
                \think\Log::warning("文件MIME类型不匹配: {$fileMime}");
                // 不严格验证MIME，因为不同系统可能返回不同的MIME类型
            }
            
            // 获取文件扩展名
            $extension = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
            
            // 验证文件扩展名（需求12.1, 12.2）
            if (!in_array($extension, ['xls', 'xlsx'])) {
                return $this->renderError('不支持的文件格式，仅支持 .xls 或 .xlsx 文件');
            }
            
            // 生成随机文件名（需求12.4: 使用随机文件名存储）
            $randomFileName = uniqid('payment_import_') . '_' . time() . '.' . $extension;
            
            // 保存到临时目录（需求12.4: 上传到临时目录）
            $tempDir = './uploads/temp/payment_import/';
            
            // 确保临时目录存在
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // 移动文件到临时目录
            $fileInfo = $file->move($tempDir, $randomFileName);
            
            if (!$fileInfo) {
                return $this->renderError('文件上传失败: ' . $file->getError());
            }
            
            // 获取完整文件路径
            $filePath = $tempDir . $randomFileName;
            
            \think\Log::info("文件上传成功: {$filePath}");
            
            // 调用服务层解析文件（需求12.3: 处理解析错误）
            $parseResult = $this->service->parseExcelFile($filePath);
            
            if (!$parseResult['success']) {
                // 解析失败，删除临时文件
                @unlink($filePath);
                return $this->renderError($parseResult['error']);
            }
            
            // 解析成功，返回解析结果和文件路径
            return $this->renderSuccess('文件上传成功', '', [
                'file_path' => $filePath,
                'file_name' => $file->getInfo('name'),
                'parsed_data' => $parseResult['data']
            ]);
            
        } catch (\Exception $e) {
            \think\Log::error('文件上传失败: ' . $e->getMessage());
            return $this->renderError('文件上传失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 预览接口
     * POST /store/payment.import/preview
     * 
     * 接收解析后的数据，匹配订单，生成预览数据
     * 
     * 需求: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6
     * 
     * @return array JSON响应
     */
    public function preview()
    {
        // 增加执行时间限制，处理大量数据时可能需要更长时间
        set_time_limit(300); // 5分钟
        
        try {
            // 获取JSON请求体
            $input = file_get_contents('php://input');
            $requestData = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->renderError('请求数据格式错误: ' . json_last_error_msg());
            }
            
            $parsedData = $requestData['parsed_data'] ?? null;
            
            if (empty($parsedData)) {
                return $this->renderError('解析数据不能为空');
            }
            
            \think\Log::info('开始生成预览数据');
            
            // 调用服务层生成预览数据
            $previewData = $this->service->generatePreview($parsedData);
            
            \think\Log::info('预览数据生成成功');
            
            // 返回预览数据
            return $this->renderSuccess('预览数据生成成功', '', [
                'preview_data' => $previewData
            ]);
            
        } catch (\Exception $e) {
            \think\Log::error('预览数据生成失败: ' . $e->getMessage());
            return $this->renderError('预览数据生成失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 导入确认接口
     * POST /store/payment.import/confirm
     * 
     * 接收预览数据和用户修正数据，验证并执行导入
     * 
     * 需求: 13.4, 13.5, 12.5
     * 
     * @return array JSON响应
     */
    public function confirm()
    {
        // 增加执行时间限制，批量更新数据库可能需要更长时间
        set_time_limit(300); // 5分钟
        
        try {
            // 获取JSON请求体
            $input = file_get_contents('php://input');
            $requestData = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->renderError('请求数据格式错误: ' . json_last_error_msg());
            }
            
            $previewData = $requestData['preview_data'] ?? null;
            $userCorrections = $requestData['user_corrections'] ?? [];
            $filePath = $requestData['file_path'] ?? null;
            
            if (empty($previewData)) {
                return $this->renderError('预览数据不能为空');
            }
            
            \think\Log::info('开始执行导入确认');
            
            // 调用服务层执行导入（需求13.4, 13.5）
            $importReport = $this->service->executeImport($previewData, $userCorrections);
            
            // 需求12.5: 删除临时文件
            if (!empty($filePath) && file_exists($filePath)) {
                @unlink($filePath);
                \think\Log::info("临时文件已删除: {$filePath}");
            }
            
            \think\Log::info('导入执行完成');
            
            // 返回导入报告
            if ($importReport['success']) {
                return $this->renderSuccess('导入完成', '', [
                    'report' => $importReport
                ]);
            } else {
                return $this->renderError('导入部分失败，请查看报告', '', [
                    'report' => $importReport
                ]);
            }
            
        } catch (\Exception $e) {
            \think\Log::error('导入执行失败: ' . $e->getMessage());
            
            // 尝试删除临时文件
            if (!empty($requestData['file_path']) && file_exists($requestData['file_path'])) {
                @unlink($requestData['file_path']);
            }
            
            return $this->renderError('导入执行失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 从服务器读取文件接口
     * POST /store/payment.import/loadFromServer
     * 
     * 直接从服务器目录读取Excel文件，绕过浏览器上传导致的格式丢失问题
     * 
     * @return array JSON响应
     */
    public function loadFromServer()
    {
        try {
            // 获取文件名
            $fileName = $this->request->post('file_name');
            
            if (empty($fileName)) {
                return $this->renderError('请输入文件名');
            }
            
            // 服务器文件目录
            $serverDir = './uploads/temp/payment_import/';
            $filePath = $serverDir . $fileName;
            
            // 验证文件是否存在
            if (!file_exists($filePath)) {
                return $this->renderError("文件不存在: {$fileName}");
            }
            
            // 验证文件扩展名
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($extension, ['xls', 'xlsx'])) {
                return $this->renderError('不支持的文件格式，仅支持 .xls 或 .xlsx 文件');
            }
            
            \think\Log::info("从服务器读取文件: {$filePath}");
            
            // 调用服务层解析文件
            $parseResult = $this->service->parseExcelFile($filePath);
            
            if (!$parseResult['success']) {
                return $this->renderError($parseResult['error']);
            }
            
            // 解析成功，返回解析结果和文件路径
            return $this->renderSuccess('文件读取成功', '', [
                'file_path' => $filePath,
                'file_name' => $fileName,
                'parsed_data' => $parseResult['data']
            ]);
            
        } catch (\Exception $e) {
            \think\Log::error('从服务器读取文件失败: ' . $e->getMessage());
            return $this->renderError('从服务器读取文件失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 取消导入接口
     * POST /store/payment.import/cancel
     * 
     * 删除临时文件，清理会话数据
     * 
     * 需求: 5.8, 12.5
     * 
     * @return array JSON响应
     */
    public function cancel()
    {
        try {
            // 获取文件路径
            $filePath = $this->request->post('file_path');
            
            // 需求12.5: 删除临时文件
            if (!empty($filePath) && file_exists($filePath)) {
                @unlink($filePath);
                \think\Log::info("导入已取消，临时文件已删除: {$filePath}");
            }
            
            // 清理会话数据（如果有）
            // 注意：当前实现中没有使用会话存储数据，所以这里不需要清理
            
            return $this->renderSuccess('导入已取消');
            
        } catch (\Exception $e) {
            \think\Log::error('取消导入失败: ' . $e->getMessage());
            return $this->renderError('取消导入失败: ' . $e->getMessage());
        }
    }
}
