<?php

namespace app\store\service\finance;

use app\store\model\FinanceConfig;
use app\store\model\HistoryPrice;
use app\store\model\StatementTemplate;
use app\store\service\statement\FormulaCalculator;

/**
 * 财务配置服务
 * 负责计价配置、历史单价导入、模板管理等
 */
class FinanceConfigService
{
    private $wxappId;
    
    public function __construct()
    {
        // 从Session获取wxapp_id（store模块）
        $session = \think\Session::get('yoshop_store');
        $this->wxappId = $session['wxapp']['wxapp_id'] ?? 10001;
    }
    
    /**
     * 保存配置
     * @param array $data 配置数据
     * @return array
     */
    public function saveConfig($data)
    {
        // 验证必填字段
        if (!isset($data['price_type'])) {
            throw new \Exception('请选择计价方式');
        }
        
        // 根据计价方式验证配置
        $this->validateConfig($data);
        
        // 准备数据
        $configData = [
            'member_id' => $data['member_id'] ?? null,
            'price_type' => $data['price_type'],
            'status' => $data['status'] ?? FinanceConfig::STATUS_ENABLED,
            'wxapp_id' => $this->wxappId,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ];
        
        // 根据计价方式设置对应字段
        switch ($data['price_type']) {
            case FinanceConfig::PRICE_TYPE_FIXED:
                $configData['unit_price'] = $data['unit_price'];
                break;
                
            case FinanceConfig::PRICE_TYPE_TIER:
                $configData['price_tier_json'] = $data['price_tier_json'];
                $configData['unit_price'] = $data['unit_price'] ?? null;
                break;
                
            case FinanceConfig::PRICE_TYPE_LINE:
                $configData['price_line_json'] = $data['price_line_json'];
                $configData['unit_price'] = $data['unit_price'] ?? null;
                break;
                
            case FinanceConfig::PRICE_TYPE_RANGE:
                $configData['price_range_json'] = $data['price_range_json'];
                $configData['unit_price'] = $data['unit_price'] ?? null;
                break;
                
            case FinanceConfig::PRICE_TYPE_FORMULA:
                $configData['price_formula'] = $data['price_formula'];
                break;
        }
        
        // 如果是更新
        if (!empty($data['id'])) {
            $config = FinanceConfig::find($data['id']);
            if (!$config) {
                throw new \Exception('配置不存在');
            }
            $configData['update_time'] = date('Y-m-d H:i:s');
            unset($configData['create_time']); // 更新时不修改创建时间
            $config->save($configData);
            return $config->toArray();
        }
        
        // 新增配置
        $config = FinanceConfig::create($configData);
        return $config->toArray();
    }
    
    /**
     * 验证配置
     */
    private function validateConfig($data)
    {
        $priceType = $data['price_type'];
        
        switch ($priceType) {
            case FinanceConfig::PRICE_TYPE_FIXED:
                if (empty($data['unit_price']) || $data['unit_price'] <= 0) {
                    throw new \Exception('固定单价必须大于0');
                }
                break;
                
            case FinanceConfig::PRICE_TYPE_TIER:
                if (empty($data['price_tier_json'])) {
                    throw new \Exception('请配置阶梯价格');
                }
                $this->validateTierConfig($data['price_tier_json']);
                break;
                
            case FinanceConfig::PRICE_TYPE_LINE:
                if (empty($data['price_line_json'])) {
                    throw new \Exception('请配置线路价格');
                }
                break;
                
            case FinanceConfig::PRICE_TYPE_RANGE:
                if (empty($data['price_range_json'])) {
                    throw new \Exception('请配置区间价格');
                }
                break;
                
            case FinanceConfig::PRICE_TYPE_FORMULA:
                if (empty($data['price_formula'])) {
                    throw new \Exception('请输入自定义公式');
                }
                $this->validateFormula($data['price_formula']);
                break;
        }
    }
    
    /**
     * 验证阶梯价格配置
     */
    private function validateTierConfig($tierConfig)
    {
        if (!isset($tierConfig['tiers']) || empty($tierConfig['tiers'])) {
            throw new \Exception('阶梯价格配置不能为空');
        }
        
        $tiers = $tierConfig['tiers'];
        
        foreach ($tiers as $index => $tier) {
            if (!isset($tier['min']) || !isset($tier['price'])) {
                throw new \Exception('第' . ($index + 1) . '个阶梯配置不完整');
            }
            
            if ($tier['price'] <= 0) {
                throw new \Exception('第' . ($index + 1) . '个阶梯价格必须大于0');
            }
        }
        
        // 验证区间不重叠
        usort($tiers, function($a, $b) {
            return $a['min'] - $b['min'];
        });
        
        for ($i = 0; $i < count($tiers) - 1; $i++) {
            $current = $tiers[$i];
            $next = $tiers[$i + 1];
            
            if ($current['max'] !== null && $current['max'] > $next['min']) {
                throw new \Exception('阶梯区间不能重叠');
            }
        }
    }
    
    /**
     * 验证公式
     */
    private function validateFormula($formula)
    {
        $calculator = new FormulaCalculator();
        
        if (!$calculator->validate($formula)) {
            throw new \Exception('公式语法错误，请检查');
        }
        
        // 测试计算
        $testResult = $calculator->test($formula, 10);
        
        if (!$testResult['valid']) {
            throw new \Exception('公式测试失败: ' . $testResult['error']);
        }
        
        if ($testResult['result'] <= 0) {
            throw new \Exception('公式计算结果必须大于0');
        }
    }
    
    /**
     * 获取配置
     * @param int $memberId 客户ID（null为全局配置）
     * @return array|null
     */
    public function getConfig($memberId = null)
    {
        $config = FinanceConfig::where('member_id', $memberId)
            ->where('wxapp_id', $this->wxappId)
            ->find();
        
        return $config ? $config->toArray() : null;
    }
    
    /**
     * 获取配置列表
     * @return array
     */
    public function getConfigList()
    {
        $list = FinanceConfig::where('wxapp_id', $this->wxappId)
            ->order('member_id', 'asc')
            ->select();
        
        return collection($list)->toArray();
    }
    
    /**
     * 删除配置
     * @param int $configId 配置ID
     * @return bool
     */
    public function deleteConfig($configId)
    {
        $config = FinanceConfig::find($configId);
        
        if (!$config) {
            throw new \Exception('配置不存在');
        }
        
        if ($config['wxapp_id'] != $this->wxappId) {
            throw new \Exception('无权删除此配置');
        }
        
        return $config->delete();
    }
    
    /**
     * 导入历史单价（TXT文件）
     * @param string $filePath 文件路径
     * @return array
     */
    public function importHistoryPriceFromTxt($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('文件不存在');
        }
        
        return HistoryPrice::importFromTxt($filePath);
    }
    
    /**
     * 导入历史单价（Excel文件）
     * @param string $filePath 文件路径
     * @return array
     */
    public function importHistoryPriceFromExcel($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('文件不存在');
        }
        
        return HistoryPrice::importFromExcel($filePath);
    }
    
    /**
     * 上传二维码
     * @param object $file 上传文件对象
     * @param string $templateId 模板ID（可为空，新建模板时）
     * @param string $type 类型（alipay/wechat）
     * @return string 文件路径
     */
    public function uploadQrCode($file, $templateId, $type = 'alipay')
    {
        // 验证文件
        if (!$file || !$file->isValid()) {
            throw new \Exception('文件上传失败');
        }
        
        // 验证文件类型
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!in_array($file->getMime(), $allowedTypes)) {
            throw new \Exception('只支持PNG和JPG格式');
        }
        
        // 验证文件大小（最大2MB）
        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new \Exception('文件大小不能超过2MB');
        }
        
        // 保存文件
        $saveDir = './uploads/qrcode/';
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }
        
        // 使用模板ID或时间戳命名文件，保留原始扩展名
        $identifier = !empty($templateId) ? $templateId : time() . '_' . mt_rand(1000, 9999);
        $extension = strtolower($file->getOriginalExtension());
        if (!in_array($extension, ['png', 'jpg', 'jpeg'])) {
            $extension = 'png';
        }
        $fileName = $type . '_' . $identifier . '.' . $extension;
        $savePath = $saveDir . $fileName;
        
        if (!$file->move($saveDir, $fileName)) {
            throw new \Exception('文件保存失败');
        }
        
        return $savePath;
    }
    
    /**
     * 上传LOGO
     * @param object $file 上传文件对象
     * @param string $templateId 模板ID（可为空，新建模板时）
     * @return string 文件路径
     */
    public function uploadLogo($file, $templateId)
    {
        // 验证文件
        if (!$file || !$file->isValid()) {
            throw new \Exception('文件上传失败');
        }
        
        // 验证文件类型
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!in_array($file->getMime(), $allowedTypes)) {
            throw new \Exception('只支持PNG和JPG格式');
        }
        
        // 验证文件大小（最大1MB）
        if ($file->getSize() > 1024 * 1024) {
            throw new \Exception('文件大小不能超过1MB');
        }
        
        // 保存文件
        $saveDir = './uploads/logo/';
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }
        
        // 使用模板ID或时间戳命名文件，保留原始扩展名
        $identifier = !empty($templateId) ? $templateId : time() . '_' . mt_rand(1000, 9999);
        $extension = strtolower($file->getOriginalExtension());
        if (!in_array($extension, ['png', 'jpg', 'jpeg'])) {
            $extension = 'png';
        }
        $fileName = 'logo_' . $identifier . '.' . $extension;
        $savePath = $saveDir . $fileName;
        
        if (!$file->move($saveDir, $fileName)) {
            throw new \Exception('文件保存失败');
        }
        
        return $savePath;
    }
    
    /**
     * 保存模板配置
     * @param array $data 模板数据
     * @return array
     */
    public function saveTemplate($data)
    {
        \think\Log::info('saveTemplate 开始 - 接收数据: ' . json_encode($data));
        
        try {
            $templateData = [
                'template_name' => $data['template_name'] ?? '默认模板',
                'title' => $data['title'] ?? '集运订单对账单',
                'logo_path' => $data['logo_path'] ?? null,
                'alipay_qr_path' => $data['alipay_qr_path'] ?? null,
                'wechat_qr_path' => $data['wechat_qr_path'] ?? null,
                'notice_text' => $data['notice_text'] ?? '',
                'is_default' => isset($data['is_default']) ? (int)$data['is_default'] : 0,
                'wxapp_id' => $this->wxappId,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s')
            ];
            
            \think\Log::info('saveTemplate - 准备的数据: ' . json_encode($templateData));
            
            // 如果设置为默认，取消其他模板的默认状态
            if ($templateData['is_default']) {
                \think\Log::info('saveTemplate - 取消其他模板的默认状态');
                StatementTemplate::where('wxapp_id', $this->wxappId)
                    ->update(['is_default' => 0]);
            }
            
            // 如果是更新
            if (!empty($data['id'])) {
                \think\Log::info('saveTemplate - 更新模板 ID: ' . $data['id']);
                $template = StatementTemplate::find($data['id']);
                if (!$template) {
                    throw new \Exception('模板不存在');
                }
                $templateData['update_time'] = date('Y-m-d H:i:s');
                unset($templateData['create_time']); // 更新时不修改创建时间
                $template->save($templateData);
                \think\Log::info('saveTemplate - 模板更新成功');
                return $template->toArray();
            }
            
            // 新增模板
            \think\Log::info('saveTemplate - 创建新模板');
            $template = StatementTemplate::create($templateData);
            \think\Log::info('saveTemplate - 模板创建成功 ID: ' . $template->id);
            return $template->toArray();
            
        } catch (\Exception $e) {
            \think\Log::error('saveTemplate 失败: ' . $e->getMessage() . ' 文件: ' . $e->getFile() . ' 行: ' . $e->getLine());
            throw $e;
        }
    }
    
    /**
     * 获取模板列表
     * @return array
     */
    public function getTemplateList()
    {
        $list = StatementTemplate::where('wxapp_id', $this->wxappId)
            ->order('is_default', 'desc')
            ->order('create_time', 'desc')
            ->select();
        
        return collection($list)->toArray();
    }
    
    /**
     * 获取模板详情
     * @param int $id 模板ID
     * @return array|null
     */
    public function getTemplate($id)
    {
        $template = StatementTemplate::where('wxapp_id', $this->wxappId)
            ->where('id', $id)
            ->find();
        
        return $template ? $template->toArray() : null;
    }
    
    /**
     * 删除模板
     * @param int $id 模板ID
     * @return bool
     */
    public function deleteTemplate($id)
    {
        $template = StatementTemplate::where('wxapp_id', $this->wxappId)
            ->where('id', $id)
            ->find();
        
        if (!$template) {
            throw new \Exception('模板不存在');
        }
        
        if ($template['is_default']) {
            throw new \Exception('默认模板不能删除');
        }
        
        return $template->delete();
    }
}
