# 电子面单 API 配置保存 - 最终修复

## 问题诊断

用户报告错误：
```json
{code: 0, msg: "配置数据格式错误", url: "", data: []}
```

发送的数据：
```json
{"zhongtong":{"api_url":"123123",...},"shunfeng":{...}}
```

## 根本原因

1. **empty() 函数问题**：`empty($config)` 可能会误判某些有效值
2. **URL 编码问题**：POST 数据可能被 URL 编码，需要先解码
3. **错误信息不够详细**：无法判断具体哪个环节出错

## 最终解决方案

### 修改后的 saveApiConfig 方法

```php
public function saveApiConfig()
{
    $config = $this->request->post('config');
    
    // 1. 精确检查空值（不使用 empty）
    if ($config === null || $config === '') {
        return $this->renderError('参数错误：配置数据为空');
    }

    // 2. 处理字符串类型的配置
    if (is_string($config)) {
        // 先 URL 解码（以防数据被编码）
        $config = urldecode($config);
        
        // 再 JSON 解码
        $decodedConfig = json_decode($config, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->renderError('JSON 解析失败：' . json_last_error_msg());
        }
        $config = $decodedConfig;
    }
    
    // 3. 验证数组类型
    if (!is_array($config)) {
        return $this->renderError('配置数据格式错误：必须是数组，当前类型：' . gettype($config));
    }
    
    // 4. 验证必要字段
    if (!isset($config['zhongtong']) || !isset($config['shunfeng'])) {
        return $this->renderError('配置数据不完整：缺少 zhongtong 或 shunfeng 配置');
    }

    // 5. 使用 StoreSettingModel::edit() 保存
    try {
        $model = new StoreSettingModel();
        $result = $model->edit('waybill', $config);
        
        if ($result) {
            return $this->renderSuccess([], '保存成功');
        }
        
        $error = $model->getError();
        return $this->renderError($error ?: '保存失败');
        
    } catch (\Exception $e) {
        return $this->renderError('保存失败：' . $e->getMessage());
    }
}
```

## 数据处理流程

```
POST 数据
    ↓
检查是否为 null 或空字符串
    ↓
如果是字符串：
    ├─ URL 解码
    └─ JSON 解码
    ↓
验证是数组类型
    ↓
验证包含必要字段
    ↓
StoreSettingModel::edit()
    ↓
保存到数据库
    ↓
返回结果
```

## 支持的数据格式

### 格式 1: JSON 字符串
```
POST: config={"zhongtong":{...},"shunfeng":{...}}
```
✅ 可以处理

### 格式 2: URL 编码的 JSON
```
POST: config=%7B%22zhongtong%22%3A%7B...
```
✅ 可以处理（先 urldecode 再 json_decode）

### 格式 3: PHP 数组（自动解析）
```
POST: config[zhongtong][api_url]=123&config[zhongtong][api_key]=456...
```
✅ 可以处理（PHP 自动解析为数组）

## 验证步骤

### 1. 在浏览器中测试

1. 打开配置页面
2. 填写 API 配置
3. 打开浏览器控制台（F12）
4. 点击保存按钮
5. 查看 Network 标签中的请求详情

**预期结果：**
```json
{code: 1, msg: "保存成功", url: "", data: []}
```

### 2. 检查数据库

```sql
SELECT 
    `key`,
    JSON_EXTRACT(`values`, '$.zhongtong.api_url') as zt_url,
    JSON_EXTRACT(`values`, '$.shunfeng.api_key') as sf_key,
    update_time
FROM yoshop_setting 
WHERE `key` = 'waybill' 
ORDER BY update_time DESC 
LIMIT 1;
```

应该看到刚保存的数据。

### 3. 检查缓存

配置保存后会自动清除缓存，可以立即读取到新配置。

## 错误信息说明

### "参数错误：配置数据为空"
- 原因：没有收到 config 参数
- 检查：前端是否正确发送了数据

### "JSON 解析失败：..."
- 原因：JSON 格式不正确
- 检查：前端发送的 JSON 字符串是否合法

### "配置数据格式错误：必须是数组，当前类型：..."
- 原因：解码后不是数组
- 检查：前端发送的数据结构

### "配置数据不完整：缺少 zhongtong 或 shunfeng 配置"
- 原因：数组中缺少必要的键
- 检查：前端是否发送了完整的配置

## 调试方法

如果仍有问题，在控制器方法开头添加日志：

```php
public function saveApiConfig()
{
    $config = $this->request->post('config');
    
    // 临时调试日志
    file_put_contents(
        'runtime/log/api_config_debug.log',
        date('Y-m-d H:i:s') . " - 原始数据:\n" .
        "类型: " . gettype($config) . "\n" .
        "内容: " . var_export($config, true) . "\n" .
        str_repeat('-', 50) . "\n",
        FILE_APPEND
    );
    
    // ... 其余代码
}
```

然后查看 `runtime/log/api_config_debug.log` 文件。

## 前端兼容性说明

当前前端代码：
```javascript
$.post(url, {
    config: JSON.stringify(config)
}, function(result) { ... });
```

这会发送 JSON 字符串，后端会正确处理。

如果需要直接发送对象：
```javascript
$.post(url, {
    config: config  // 不使用 JSON.stringify
}, function(result) { ... });
```

后端也能正确处理（PHP 会自动解析为数组）。

## 总结

修复后的方法：
- ✅ 支持多种数据格式
- ✅ 详细的错误信息
- ✅ 正确的数据验证
- ✅ 自动缓存清理
- ✅ 异常捕获和处理

现在应该可以正常保存 API 配置了！
