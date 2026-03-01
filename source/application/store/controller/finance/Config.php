<?php

namespace app\store\controller\finance;

use app\store\controller\Controller;
use app\store\service\finance\FinanceConfigService;

/**
 * 财务配置控制器
 * 薄控制器：只负责请求响应，业务逻辑在服务层
 */
class Config extends Controller
{
    private $service;
    
    public function __construct()
    {
        parent::__construct();
        $this->service = new FinanceConfigService();
    }
    
    /**
     * 配置页面
     * GET /store/finance.config/index
     */
    public function index()
    {
        return $this->fetch();
    }
    
    /**
     * 保存配置
     * POST /store/finance.config/save
     */
    public function save()
    {
        $data = $this->request->post();
        
        try {
            $config = $this->service->saveConfig($data);
            return $this->renderSuccess('配置保存成功', '', $config);
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 获取配置
     * GET /store/finance.config/get
     */
    public function get()
    {
        $memberId = $this->request->get('member_id/d');
        
        try {
            $config = $this->service->getConfig($memberId);
            
            if ($config) {
                return $this->renderSuccess('', '', $config);
            } else {
                return $this->renderSuccess('', '', ['message' => '暂无配置']);
            }
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 获取配置列表
     * GET /store/finance.config/list
     */
    public function list()
    {
        try {
            $list = $this->service->getConfigList();
            return $this->renderSuccess('', '', ['list' => $list]);
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 删除配置
     * POST /store/finance.config/delete
     */
    public function delete()
    {
        $configId = $this->request->post('config_id/d');
        
        if (empty($configId)) {
            return $this->renderError('配置ID不能为空');
        }
        
        try {
            $this->service->deleteConfig($configId);
            return $this->renderSuccess('配置已删除');
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 导入历史单价
     * POST /store/finance.config/importHistoryPrice
     */
    public function importHistoryPrice()
    {
        $file = $this->request->file('file');
        
        if (!$file) {
            return $this->renderError('请选择文件');
        }
        
        try {
            // 保存临时文件
            $extension = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
            $tempPath = './uploads/temp/' . uniqid() . '.' . $extension;
            $file->move(dirname($tempPath), basename($tempPath));
            
            // 根据文件类型导入
            if ($extension == 'txt') {
                $result = $this->service->importHistoryPriceFromTxt($tempPath);
            } elseif (in_array($extension, ['xls', 'xlsx'])) {
                $result = $this->service->importHistoryPriceFromExcel($tempPath);
            } else {
                return $this->renderError('不支持的文件格式');
            }
            
            // 删除临时文件
            @unlink($tempPath);
            
            $message = "导入成功：{$result['success_count']} 条";
            if ($result['failed_count'] > 0) {
                $message .= "，失败：{$result['failed_count']} 条";
            }
            
            return $this->renderSuccess($message, '', $result);
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 上传文件（二维码、LOGO）
     * POST /store/finance.config/upload
     */
    public function upload()
    {
        $file = $this->request->file('file');
        $type = $this->request->post('type'); // qrcode_alipay, qrcode_wechat, logo
        $templateId = $this->request->post('template_id', '');
        
        if (!$file) {
            return $this->renderError('请选择文件');
        }
        
        try {
            if ($type == 'qrcode_alipay') {
                $filePath = $this->service->uploadQrCode($file, $templateId, 'alipay');
            } elseif ($type == 'qrcode_wechat') {
                $filePath = $this->service->uploadQrCode($file, $templateId, 'wechat');
            } elseif ($type == 'logo') {
                $filePath = $this->service->uploadLogo($file, $templateId);
            } else {
                return $this->renderError('未知的上传类型');
            }
            
            return $this->renderSuccess('上传成功', '', [
                'file_path' => $filePath
            ]);
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 验证公式
     * POST /store/finance.config/validateFormula
     */
    public function validateFormula()
    {
        $formula = $this->request->post('formula');
        
        if (empty($formula)) {
            return $this->renderError('公式不能为空');
        }
        
        try {
            $calculator = new \app\store\service\statement\FormulaCalculator();
            $testResult = $calculator->test($formula, 10);
            
            if ($testResult['valid']) {
                return $this->renderSuccess('公式有效', '', [
                    'test_result' => $testResult['result']
                ]);
            } else {
                return $this->renderError($testResult['error']);
            }
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 保存模板
     * POST /store/finance.config/saveTemplate
     */
    public function saveTemplate()
    {
        $data = $this->request->post();
        
        \think\Log::info('Controller saveTemplate - 接收POST数据: ' . json_encode($data));
        
        try {
            $template = $this->service->saveTemplate($data);
            \think\Log::info('Controller saveTemplate - 成功返回');
            return $this->renderSuccess('模板保存成功', '', $template);
            
        } catch (\Exception $e) {
            // 记录详细错误信息
            \think\Log::error('Controller saveTemplate 失败: ' . $e->getMessage() . ' 文件: ' . $e->getFile() . ' 行: ' . $e->getLine());
            \think\Log::error('错误堆栈: ' . $e->getTraceAsString());
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 获取模板列表
     * GET /store/finance.config/templateList
     */
    public function templateList()
    {
        try {
            $list = $this->service->getTemplateList();
            return $this->renderSuccess('', '', ['list' => $list]);
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 获取模板详情
     * GET /store/finance.config/getTemplate
     */
    public function getTemplate()
    {
        $id = $this->request->get('id/d');
        if (empty($id)) {
            return $this->renderError('模板ID不能为空');
        }
        
        try {
            $template = $this->service->getTemplate($id);
            return $this->renderSuccess('', '', $template);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 删除模板
     * POST /store/finance.config/deleteTemplate
     */
    public function deleteTemplate()
    {
        $id = $this->request->post('id/d');
        if (empty($id)) {
            return $this->renderError('模板ID不能为空');
        }
        
        try {
            $this->service->deleteTemplate($id);
            return $this->renderSuccess('删除成功');
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
}
