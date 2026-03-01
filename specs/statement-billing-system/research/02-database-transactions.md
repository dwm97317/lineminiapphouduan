# 数据库事务和锁机制研究

## 概述

账单系统需要保证数据一致性，特别是在账单生成和支付状态同步时。

## ThinkPHP 5.0 事务支持

### 1. 基本事务

```php
use think\Db;

Db::startTrans();
try {
    // 操作1
    Db::table('yoshop_statement')->insert($data);
    
    // 操作2
    Db::table('yoshop_package')
        ->where('id', 'in', $ids)
        ->update(['statement_id' => $statementId]);
    
    Db::commit();
} catch (\Exception $e) {
    Db::rollback();
    throw $e;
}
```

### 2. 模型事务

```php
use think\Db;

Db::startTrans();
try {
    $statement = Statement::create($data);
    
    Package::where('id', 'in', $ids)
        ->update(['statement_id' => $statement->id]);
    
    Db::commit();
} catch (\Exception $e) {
    Db::rollback();
    throw $e;
}
```

## 数据库锁

### 1. 悲观锁（FOR UPDATE）

用于防止并发生成账单时订单被重复归档：

```php
// 锁定订单
$packages = Db::table('yoshop_package')
    ->where('id', 'in', $packageIds)
    ->where('statement_id', null)
    ->where('is_delete', 0)
    ->lock(true)  // SELECT ... FOR UPDATE
    ->select();

if (count($packages) != count($packageIds)) {
    throw new \Exception('部分订单不可用或已归档');
}

// 继续处理...
```

### 2. ThinkPHP中的锁

```php
// 排他锁（写锁）
$packages = Package::where('id', 'in', $ids)
    ->lock(true)
    ->select();

// 共享锁（读锁）
$packages = Package::where('id', 'in', $ids)
    ->lock('lock in share mode')
    ->select();
```

### 3. 完整的并发控制示例

```php
public function createStatement($packageIds, $memberId)
{
    Db::startTrans();
    try {
        // 1. 锁定订单并验证
        $packages = Package::where('id', 'in', $packageIds)
            ->where('member_id', $memberId)
            ->where('statement_id', null)
            ->where('is_delete', 0)
            ->lock(true)
            ->select();
        
        if (count($packages) != count($packageIds)) {
            $lockedIds = array_column($packages, 'id');
            $unavailableIds = array_diff($packageIds, $lockedIds);
            throw new \Exception('订单ID ' . implode(',', $unavailableIds) . ' 不可用或已归档');
        }
        
        // 2. 创建账单
        $statement = Statement::create([
            'statement_no' => $this->generateStatementNo(),
            'member_id' => $memberId,
            // ... 其他字段
        ]);
        
        // 3. 绑定订单
        Package::where('id', 'in', $packageIds)
            ->update(['statement_id' => $statement->id]);
        
        Db::commit();
        return $statement;
        
    } catch (\Exception $e) {
        Db::rollback();
        throw $e;
    }
}
```

## 账单支付状态同步

### 1. 标记账单为已支付

```php
public function markStatementAsPaid($statementId, $remark = '')
{
    Db::startTrans();
    try {
        // 1. 更新账单
        $statement = Statement::find($statementId);
        $statement->save([
            'pay_status' => 2,
            'pay_time' => date('Y-m-d H:i:s'),
            'pay_remark' => $remark
        ]);
        
        // 2. 更新关联订单（容错处理）
        $packages = Package::where('statement_id', $statementId)
            ->where('is_delete', 0)
            ->select();
        
        $successCount = 0;
        $failedIds = [];
        
        foreach ($packages as $package) {
            try {
                $package->save([
                    'is_pay' => 1,
                    'pay_time' => date('Y-m-d H:i:s')
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $failedIds[] = $package->id;
                \think\Log::error('订单支付状态更新失败: ' . $package->id . ', ' . $e->getMessage());
            }
        }
        
        Db::commit();
        
        // 返回结果
        return [
            'success' => true,
            'success_count' => $successCount,
            'failed_count' => count($failedIds),
            'failed_ids' => $failedIds
        ];
        
    } catch (\Exception $e) {
        Db::rollback();
        throw $e;
    }
}
```

### 2. 批量更新优化

如果订单数量很多，可以使用批量更新：

```php
// 批量更新（更快，但无法容错）
$affectedRows = Package::where('statement_id', $statementId)
    ->where('is_delete', 0)
    ->update([
        'is_pay' => 1,
        'pay_time' => date('Y-m-d H:i:s')
    ]);
```

## 事务隔离级别

MySQL默认隔离级别是 REPEATABLE READ，适合大多数场景。

```sql
-- 查看当前隔离级别
SELECT @@transaction_isolation;

-- 设置隔离级别（如果需要）
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
```

## 死锁处理

### 1. 避免死锁

- 按相同顺序锁定资源
- 缩短事务时间
- 避免在事务中执行耗时操作（如：生成Excel）

### 2. 死锁检测

```php
try {
    Db::startTrans();
    // ... 操作
    Db::commit();
} catch (\PDOException $e) {
    Db::rollback();
    
    // 检查是否是死锁
    if ($e->getCode() == '40001' || strpos($e->getMessage(), 'Deadlock') !== false) {
        // 重试或记录日志
        \think\Log::error('检测到死锁: ' . $e->getMessage());
    }
    
    throw $e;
}
```

## 最佳实践

### 1. 事务范围最小化

```php
// ❌ 错误：事务中包含耗时操作
Db::startTrans();
try {
    $statement = Statement::create($data);
    Package::where('id', 'in', $ids)->update(['statement_id' => $statement->id]);
    
    // ❌ 不要在事务中生成Excel
    $excelPath = $this->generateExcel($statement);
    
    Db::commit();
} catch (\Exception $e) {
    Db::rollback();
}

// ✅ 正确：事务外生成Excel
Db::startTrans();
try {
    $statement = Statement::create($data);
    Package::where('id', 'in', $ids)->update(['statement_id' => $statement->id]);
    Db::commit();
} catch (\Exception $e) {
    Db::rollback();
    throw $e;
}

// 事务外生成Excel
$excelPath = $this->generateExcel($statement);
$statement->save(['excel_path' => $excelPath]);
```

### 2. 错误处理

```php
Db::startTrans();
try {
    // 业务逻辑
    Db::commit();
    return ['success' => true];
} catch (\Exception $e) {
    Db::rollback();
    
    // 记录日志
    \think\Log::error('账单生成失败: ' . $e->getMessage());
    
    // 返回友好错误
    return ['success' => false, 'message' => '账单生成失败，请重试'];
}
```

### 3. 日志记录

```php
// 记录关键操作
\think\Log::info('开始生成账单', [
    'member_id' => $memberId,
    'package_ids' => $packageIds
]);

Db::startTrans();
try {
    // ... 操作
    Db::commit();
    
    \think\Log::info('账单生成成功', [
        'statement_id' => $statement->id
    ]);
} catch (\Exception $e) {
    Db::rollback();
    
    \think\Log::error('账单生成失败', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    throw $e;
}
```

## 性能考虑

### 1. 索引优化

确保锁定查询使用索引：

```sql
-- 添加索引
ALTER TABLE yoshop_package 
ADD INDEX idx_statement_member (statement_id, member_id, is_delete);
```

### 2. 批量操作

```php
// ✅ 批量更新（一次SQL）
Package::where('id', 'in', $ids)->update(['statement_id' => $statementId]);

// ❌ 循环更新（N次SQL）
foreach ($ids as $id) {
    Package::where('id', $id)->update(['statement_id' => $statementId]);
}
```

## 结论

ThinkPHP 5.0 的事务和锁机制足够支持账单系统的需求：
- ✅ 支持标准的事务操作
- ✅ 支持悲观锁（FOR UPDATE）
- ✅ 错误处理机制完善
- ⚠️ 需要注意事务范围最小化
- ⚠️ 需要合理使用锁避免死锁

**推荐方案**：
1. 账单生成使用事务 + 悲观锁
2. 支付状态同步使用事务 + 容错处理
3. Excel生成放在事务外
4. 完善的日志记录
