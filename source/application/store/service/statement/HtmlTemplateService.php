<?php

namespace app\store\service\statement;

/**
 * HTML模板服务
 * 生成账单的HTML模板
 */
class HtmlTemplateService
{
    /**
     * 生成账单HTML
     * @param array $statement 账单信息
     * @param array $packages 订单列表
     * @param array $template 模板配置
     * @return string HTML内容
     */
    public function generateStatementHtml($statement, $packages, $template)
    {
        // 处理支付状态
        $payStatusText = ($statement['pay_status'] ?? 1) == 2 ? '已支付 (Paid)' : '未支付 (Unpaid)';
        $payStatusClass = ($statement['pay_status'] ?? 1) == 2 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        
        // 处理二维码路径
        $alipayQrUrl = !empty($template['alipay_qr_path']) ? '/uploads/qrcode/alipay_1.jpg' : '';
        $wechatQrUrl = !empty($template['wechat_qr_path']) ? '/uploads/qrcode/wechat_1.jpg' : '';
        
        // 生成订单行
        $orderRows = '';
        $index = 1;
        foreach ($packages as $package) {
            $bgClass = ($index % 2 == 0) ? 'bg-gray-50' : 'bg-white';
            $orderRows .= sprintf(
                '<tr class="%s hover:bg-gray-100 transition-colors">
                    <td class="px-6 py-4 text-sm text-gray-500">%d</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">%s</td>
                    <td class="px-6 py-4 text-sm text-gray-500 font-mono text-xs">%s</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right font-medium">%s</td>
                    <td class="px-6 py-4 text-sm text-gray-500 text-right">%s</td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">%s</td>
                </tr>',
                $bgClass,
                $index,
                $package['order_sn'] ?? '',
                $package['t_order_sn'] ?? '-',
                $package['cale_weight'] ?? $package['weight'] ?? 0,
                $package['unit_price'] ?? 0,
                $package['calculated_amount'] ?? 0
            );
            $index++;
        }
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>集运订单对账单 - Consolidated Shipping Invoice</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
    <style>
        body { font-family: 'Inter', 'Noto Sans SC', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen antialiased">
    <div class="max-w-6xl mx-auto p-8">
        <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-gray-200">
            <!-- Header -->
            <div class="bg-blue-50 p-8 border-b border-gray-200 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                        <span class="material-icons text-4xl">inventory_2</span>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">集运订单对账单</h1>
                        <p class="text-sm text-gray-500 mt-1">Consolidated Shipping Invoice</p>
                    </div>
                </div>
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {$payStatusClass}">
                        <span class="w-2 h-2 mr-2 bg-red-500 rounded-full"></span>
                        {$payStatusText}
                    </span>
                </div>
            </div>
            
            <!-- Info Cards -->
            <div class="p-8 grid grid-cols-3 gap-6">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">账单编号 (Bill No.)</p>
                    <p class="text-lg font-semibold text-gray-900">{$statement['statement_no']}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">客户 ID (User ID)</p>
                    <p class="text-lg font-semibold text-gray-900">{$statement['member_id']}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">生成时间 (Created At)</p>
                    <p class="text-lg font-semibold text-gray-900">{$statement['create_time']}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">订单数 (Count)</p>
                    <p class="text-lg font-semibold text-gray-900">{$statement['total_packages']} <span class="text-sm font-normal text-gray-500">个</span></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">总重量 (Total Weight)</p>
                    <p class="text-lg font-semibold text-gray-900">{$statement['total_weight']} <span class="text-sm font-normal text-gray-500">KG</span></p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                    <p class="text-xs font-medium text-blue-600 uppercase tracking-wider mb-1">总金额 (Total Amount)</p>
                    <p class="text-2xl font-bold text-blue-600">¥ {$statement['total_amount']}</p>
                </div>
            </div>
            
            <!-- Table -->
            <div class="border-t border-gray-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-xs font-semibold tracking-wide text-gray-600 uppercase border-b border-gray-200">
                            <th class="px-6 py-4">序号 (No.)</th>
                            <th class="px-6 py-4">订单编号 (Order ID)</th>
                            <th class="px-6 py-4">国际单号 (Intl No.)</th>
                            <th class="px-6 py-4 text-right">重量 (KG)</th>
                            <th class="px-6 py-4 text-right">单价 (¥/KG)</th>
                            <th class="px-6 py-4 text-right">金额 (¥)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        {$orderRows}
                    </tbody>
                    <tfoot>
                        <tr class="bg-amber-50 border-t-2 border-amber-200">
                            <td class="px-6 py-4 text-right text-sm font-bold text-gray-700 uppercase tracking-wide" colspan="5">合计 (Total)</td>
                            <td class="px-6 py-4 text-right text-xl font-bold text-blue-600">¥ {$statement['total_amount']}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Payment QR Codes -->
            <div class="p-10 bg-gray-50 border-t border-gray-200">
                <div class="text-center mb-8">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center justify-center gap-2">
                        <span class="material-icons text-blue-600">payments</span>
                        收款方式 (Payment Methods)
                    </h2>
                    <p class="text-sm text-gray-500 mt-2">请扫描下方二维码完成支付 (Please scan QR code to pay)</p>
                </div>
                <div class="flex justify-center gap-16">
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 w-80 flex flex-col items-center">
                        <div class="w-full bg-blue-500 text-white py-3 rounded-t-lg text-center font-bold mb-4">
                            推荐使用支付宝
                        </div>
                        <div class="w-48 h-48 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200">
                            <img alt="Alipay QR Code" class="w-44 h-44 object-contain" src="{$alipayQrUrl}"/>
                        </div>
                        <div class="mt-4 text-blue-500 font-bold">支付宝 (Alipay)</div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 w-80 flex flex-col items-center">
                        <div class="w-full bg-green-500 text-white py-3 rounded-t-lg text-center font-bold mb-4">
                            推荐使用微信支付
                        </div>
                        <div class="w-48 h-48 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200">
                            <img alt="WeChat Pay QR Code" class="w-44 h-44 object-contain" src="{$wechatQrUrl}"/>
                        </div>
                        <div class="mt-4 text-green-500 font-bold">微信支付 (WeChat Pay)</div>
                    </div>
                </div>
            </div>
            
            <!-- Notice -->
            <div class="bg-amber-50 border-t border-amber-100 p-6">
                <div class="flex items-start gap-3">
                    <span class="material-icons text-amber-500 mt-1">info</span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">温馨提示 (Note):</h3>
                        <p class="text-sm text-gray-600 mt-1 leading-relaxed">{$template['notice_text']}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-8 text-xs text-gray-400">© 2026 Consolidated Shipping System. All rights reserved.</div>
    </div>
</body>
</html>
HTML;
        
        return $html;
    }
}
