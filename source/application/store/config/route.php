<?php
/**
 * Store Application Route Configuration
 * 
 * This file documents the routes for the store application.
 * ThinkPHP uses automatic routing based on controller structure,
 * but explicit routes can be defined here for clarity or custom routing.
 */

return [
    /**
     * Payment Import Routes
     * 
     * 财务原始数据导入路由配置
     * Controller: app\store\controller\payment\Import
     * 
     * These routes are automatically available through ThinkPHP's
     * convention-based routing: /store/payment.import/{action}
     */
    
    // Display import page
    // GET /store/payment.import/index
    // 显示导入页面
    'payment.import/index' => [
        'method' => 'GET',
        'controller' => 'payment.Import',
        'action' => 'index',
        'description' => 'Display the payment import page with file upload interface'
    ],
    
    // File upload endpoint
    // POST /store/payment.import/upload
    // 文件上传接口
    'payment.import/upload' => [
        'method' => 'POST',
        'controller' => 'payment.Import',
        'action' => 'upload',
        'description' => 'Handle Excel file upload, validate format, and parse content',
        'requirements' => '12.1, 12.2, 12.3, 12.4'
    ],
    
    // Generate preview endpoint
    // POST /store/payment.import/preview
    // 生成预览数据接口
    'payment.import/preview' => [
        'method' => 'POST',
        'controller' => 'payment.Import',
        'action' => 'preview',
        'description' => 'Match orders and generate preview data for user confirmation',
        'requirements' => '5.1, 5.2, 5.3, 5.4, 5.5, 5.6'
    ],
    
    // Confirm import endpoint
    // POST /store/payment.import/confirm
    // 确认导入接口
    'payment.import/confirm' => [
        'method' => 'POST',
        'controller' => 'payment.Import',
        'action' => 'confirm',
        'description' => 'Execute the import with user corrections and generate report',
        'requirements' => '13.4, 13.5, 12.5'
    ],
    
    // Cancel import endpoint
    // POST /store/payment.import/cancel
    // 取消导入接口
    'payment.import/cancel' => [
        'method' => 'POST',
        'controller' => 'payment.Import',
        'action' => 'cancel',
        'description' => 'Cancel import and clean up temporary files',
        'requirements' => '5.8, 12.5'
    ],
];
