# SaveConfig Array 错误修复完成

## 问题诊断

**错误信息：** `保存失败：variable type error：array`

**根本原因：**
在 `Referral` 控制器的 `config()` 方法中，使用了模型查询：
```php
$taskConfigList = ReferralTaskConfig::where('wxapp_id', $wxappId)->select();
```

模型中定义了访问器 `getUserTypeAttr()`，它将 `user_type` 字段转换为数组：
```php
public function getUserTypeAttr($value) {
    return [
        'value' => $value,
        'text' => $types[$value] ?? '未知',
    ];
}
```

当数据传递到视图并最终提交回控制器时，某些情况下数组值会被尝试写入数据库，导致类型错误。

## 修复方案

### 1. 修复 `config()` 方法（读取配置）

将所有模型查询改为原始 SQL 查询，避免触发访问器：

```php
// 修复前
$taskConfigList = ReferralTaskConfig::where('wxapp_id', $wxappId)->select();

// 修复后
$taskConfigList = \think\Db::name('referral_task_config')
    ->where('wxapp_id', $wxappId)
    ->order('user_type', 'asc')
    ->order('sort_order', 'asc')
    ->select();
```

### 2. 修复 `saveTaskConfig()` 方法（保存配置）

确保所有值都进行类型转换：

```php
$updateData = [
    'is_enabled' => isset($taskData['is_enabled']) ? intval($taskData['is_enabled']) : 0,
    'is_required' => isset($taskData['is_required']) ? intval($taskData['is_required']) : 0,
];

// 处理 task_params
if (isset($taskData['task_params']) && is_array($taskData['task_params'])) {
    $params = array_filter($taskData['task_params'], function($value) {
        return $value !== '' && $value !== null;
    });
    
    if (!empty($params)) {
        $updateData['task_params'] = json_encode($params, JSON_UNESCAPED_UNICODE);
    }
}
```

### 3. 修复 `saveRewardConfig()` 方法

同样确保类型转换：

```php
$updateData = [
    'is_enabled' => isset($configData['is_enabled']) ? intval($configData['is_enabled']) : 0,
];

if (isset($configData['reward_type'])) {
    $updateData['reward_type'] = intval($configData['reward_type']);
}
if (isset($configData['reward_amount'])) {
    $updateData['reward_amount'] = floatval($configData['reward_amount']);
}
// ... 其他字段
```

## 修改的文件

- `Lineminiapp/source/application/store/controller/setting/Referral.php`
  - `config()` 方法：使用原始 SQL 查询
  - `saveTaskConfig()` 方法：添加类型转换和日志
  - `saveRewardConfig()` 方法：添加类型转换和日志

## 测试验证

### 测试 1: 配置页面加载
```bash
php test_config_page_load.php
```
✅ 所有字段都是标量值

### 测试 2: 配置保存
```bash
php test_saveconfig_complete.php
```
✅ 任务配置保存成功（3个）
✅ 奖励配置保存成功（2个）

### 测试 3: 数据验证
```bash
php verify_saveconfig_fix.php
```
✅ 所有 JSON 参数格式正确
✅ 不再出现 array 类型错误

## 使用说明

现在可以正常访问：
```
http://localhost:8080/index.php?s=/store/setting.referral/config
```

提交配置时不会再出现 "variable type error：array" 错误。

## 技术要点

1. **避免模型访问器干扰**：在需要原始数据的场景使用 `\think\Db::name()` 而不是模型
2. **类型转换**：所有数值字段使用 `intval()`/`floatval()` 确保类型正确
3. **JSON 编码**：数组参数必须转换为 JSON 字符串存储
4. **日志记录**：添加详细日志便于调试

## 相关文档

- `REFERRAL_CONFIG_SAVE_ANALYSIS.md` - 问题分析
- `REFERRAL_CONFIG_PARAMETER_ANALYSIS.md` - 参数分析
- `ALL_CONFIG_FORMS_FIXED.md` - 所有配置表单修复
