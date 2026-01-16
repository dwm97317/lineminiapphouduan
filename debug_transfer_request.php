<?php
/**
 * 调试转单请求 - 记录实际提交的数据
 * 将此文件内容添加到 TrOrder::deliverySave() 方法开头
 */

// 在 deliverySave() 方法开头添加以下代码：
/*
public function deliverySave(){
    // === 调试代码开始 ===
    $debug_data = [
        'time' => date('Y-m-d H:i:s'),
        'raw_post' => $_POST,
        'postData' => $this->postData('delivery'),
        'request_param' => $this->request->param(),
    ];
    
    $log_file = __DIR__ . '/../../../../debug_transfer_log.txt';
    file_put_contents($log_file, 
        "=== 转单请求调试 " . date('Y-m-d H:i:s') . " ===\n" .
        print_r($debug_data, true) . "\n\n",
        FILE_APPEND
    );
    // === 调试代码结束 ===
    
    $model = (new Inpack());
    if ($model->modify($this->postData('delivery'))){
        return $this->renderSuccess('操作成功');
    } 
    return $this->renderError($model->getError() ?: '操作失败');
}
*/

echo "请将上面注释中的代码添加到以下文件：\n";
echo "文件: Lineminiapp/source/application/store/controller/TrOrder.php\n";
echo "方法: deliverySave()\n";
echo "位置: 方法开头\n\n";

echo "然后：\n";
echo "1. 在浏览器中执行转单操作\n";
echo "2. 查看生成的日志文件: Lineminiapp/debug_transfer_log.txt\n";
echo "3. 将日志内容发给我分析\n";
