<?php

namespace app\store\controller\package;

use app\store\controller\Controller;
use app\store\service\statement\StatementService;
use app\store\service\statement\StatementManageService;

/**
 * 账单控制器
 * 薄控制器：只负责请求响应，业务逻辑在服务层
 */
class Statement extends Controller
{
    private $service;
    private $manageService;
    
    public function __construct()
    {
        parent::__construct();
        $this->service = new StatementService();
        $this->manageService = new StatementManageService();
    }
    
    /**
     * 生成账单
     * POST /store/package.statement/create
     */
    public function create()
    {
        // 支持两种参数名：inpack_ids（集运订单ID）和 package_ids（包裹ID）
        $inpackIds = $this->request->post('inpack_ids/a');
        $packageIds = $this->request->post('package_ids/a');
        $memberId = $this->request->post('member_id/d');
        
        // 优先使用 inpack_ids，如果没有则使用 package_ids
        $ids = !empty($inpackIds) ? $inpackIds : $packageIds;
        
        // 详细日志 - 使用 JSON 格式
        \think\Log::info('Statement::create 开始: ' . json_encode([
            'inpack_ids' => $inpackIds,
            'package_ids' => $packageIds,
            'final_ids' => $ids,
            'member_id' => $memberId
        ]));
        
        if (empty($ids)) {
            return $this->renderError('请选择集运订单或包裹');
        }
        
        if (empty($memberId)) {
            return $this->renderError('客户ID不能为空');
        }
        
        try {
            $statement = $this->service->createStatement($ids, $memberId);
            
            return $this->renderSuccess('账单生成成功', [
                'statement_id' => $statement['id'],
                'statement_no' => $statement['statement_no'],
                'total_amount' => $statement['total_amount'],
                'excel_path' => $statement['excel_path'] ?? null
            ]);
            
        } catch (\Exception $e) {
            \think\Log::error('Statement::create 失败: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return $this->renderError($e->getMessage());
        } catch (\Throwable $e) {
            \think\Log::error('Statement::create 严重错误: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return $this->renderError('系统错误: ' . $e->getMessage());
        }
    }
    
    /**
     * 账单列表页面
     * GET /store/package.statement/list
     */
    public function list()
    {
        return $this->fetch('list');
    }
    
    /**
     * 获取账单列表数据（Ajax）
     * GET /store/package.statement/getList
     */
    public function getList()
    {
        $params = [
            'page' => $this->request->get('page/d', 1),
            'page_size' => $this->request->get('page_size/d', 20),
            'member_id' => $this->request->get('member_id/d'),
            'pay_status' => $this->request->get('pay_status'),
            'status' => $this->request->get('status'),
            'start_date' => $this->request->get('start_date'),
            'end_date' => $this->request->get('end_date'),
            'keyword' => $this->request->get('keyword')
        ];
        
        try {
            $result = $this->manageService->getList($params);
            return $this->renderSuccess('', '', $result);
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 账单详情页面
     * GET /store/package.statement/detail
     */
    public function detail()
    {
        // 尝试多种方式获取参数
        $statementId = $this->request->param('statement_id/d');
        
        if (empty($statementId)) {
            $statementId = $this->request->get('statement_id/d');
        }
        
        if (empty($statementId)) {
            return $this->renderError('账单ID不能为空');
        }
        
        try {
            $detail = $this->manageService->getDetail($statementId);
            
            // 直接传递数据给视图，不使用 Ajax
            return $this->fetch('detail', [
                'statement' => $detail['statement'],
                'packages' => $detail['packages']
            ]);
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 获取账单详情数据（Ajax）
     * GET /store/package.statement/getDetail
     */
    public function getDetail()
    {
        // 尝试多种方式获取参数
        $statementId = $this->request->param('statement_id/d');
        
        if (empty($statementId)) {
            $statementId = $this->request->get('statement_id/d');
        }
        
        if (empty($statementId)) {
            return $this->renderError('账单ID不能为空');
        }
        
        try {
            $detail = $this->manageService->getDetail($statementId);
            return $this->renderSuccess('', '', $detail);
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 标记为已支付
     * POST /store/package.statement/markPaid
     */
    public function markPaid()
    {
        $statementId = $this->request->post('statement_id/d');
        $remark = $this->request->post('remark', '');
        
        if (empty($statementId)) {
            return $this->renderError('账单ID不能为空');
        }
        
        try {
            $result = $this->manageService->markAsPaid($statementId, $remark);
            
            $message = '操作成功';
            if ($result['failed_count'] > 0) {
                $message .= '，但有' . $result['failed_count'] . '个订单更新失败';
            }
            
            return $this->renderSuccess($message, $result);
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 作废账单
     * POST /store/package.statement/void
     */
    public function void()
    {
        $statementId = $this->request->post('statement_id/d');
        
        if (empty($statementId)) {
            return $this->renderError('账单ID不能为空');
        }
        
        try {
            $this->manageService->voidStatement($statementId);
            return $this->renderSuccess('账单已作废');
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 下载Excel
     * GET /store/package.statement/downloadExcel/statement_id/15
     */
    public function downloadExcel()
    {
        // 使用 ThinkPHP 的 param 方法获取路径参数
        $statementId = $this->request->param('statement_id/d', 0);
        
        if (empty($statementId)) {
            return $this->renderError('账单ID不能为空');
        }
        
        try {
            $detail = $this->manageService->getDetail($statementId);
            $statement = $detail['statement'];
            
            $filePath = $statement['excel_path'];
            
            if (empty($filePath)) {
                return $this->renderError('Excel文件路径为空');
            }
            
            // 处理文件路径
            // 数据库存储: uploads/statements/xxx.xlsx
            // PHP运行在web目录，所以使用相对路径
            $fullPath = './' . ltrim($filePath, './');
            
            if (!file_exists($fullPath)) {
                return $this->renderError('Excel文件不存在: ' . $fullPath);
            }
            
            // 下载文件 - 使用 ThinkPHP 的文件下载方法
            $filename = basename($fullPath);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            readfile($fullPath);
            exit;
            
        } catch (\Exception $e) {
            \think\Log::error('downloadExcel 失败: ' . $e->getMessage());
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 重新生成Excel
     * POST /store/package.statement/regenerateExcel
     */
    public function regenerateExcel()
    {
        $statementId = $this->request->post('statement_id/d');
        
        if (empty($statementId)) {
            return $this->renderError('账单ID不能为空');
        }
        
        try {
            $excelPath = $this->manageService->regenerateExcel($statementId);
            
            return $this->renderSuccess('Excel重新生成成功', [
                'excel_path' => $excelPath
            ]);
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 获取统计数据
     * GET /store/package.statement/statistics
     */
    public function statistics()
    {
        $params = [
            'start_date' => $this->request->get('start_date'),
            'end_date' => $this->request->get('end_date'),
            'member_id' => $this->request->get('member_id/d')
        ];
        
        try {
            $stats = $this->manageService->getStatistics($params);
            return $this->renderSuccess('', '', $stats);
            
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
}
