# 账单编号生成规则研究

## 概述

账单编号是账单的唯一标识，需要保证唯一性、可读性和可追溯性。

## 编号格式设计

### 方案1: 日期 + 序号（推荐）

```
格式：ST + YYYYMMDD + 流水号(3位)
示例：ST20260228001
```

**优点**：
- ✅ 可读性好（一眼看出日期）
- ✅ 便于按日期查询和统计
- ✅ 流水号重置简单（每天从001开始）
- ✅ 长度固定（13位）

**缺点**：
- ⚠️ 每天最多999个账单（对大多数场景足够）

### 方案2: 年月 + 序号

```
格式：ST + YYYYMM + 流水号(4位)
示例：ST2026020001
```

**优点**：
- ✅ 每月最多9999个账单
- ✅ 便于按月统计

**缺点**：
- ⚠️ 不能直接看出具体日期

### 方案3: 纯序号

```
格式：ST + 全局流水号(8位)
示例：ST00000001
```

**优点**：
- ✅ 实现简单
- ✅ 容量大

**缺点**：
- ❌ 无法从编号看出时间信息
- ❌ 不便于按时间查询

## 推荐方案：日期 + 序号

```php
<?php
namespace app\store\model;

use think\Model;
use think\Db;

class Statement extends Model
{
    /**
     * 生成账单编号
     */
    public static function generateStatementNo()
    {
        $prefix = 'ST';
        $date = date('Ymd');
        
        // 使用数据库锁保证唯一性
        Db::startTrans();
        try {
            // 查询今天最大的序号
            $lastStatement = self::where('statement_no', 'like', $prefix . $date . '%')
                ->lock(true)
                ->order('statement_no', 'desc')
                ->find();
            
            if ($lastStatement) {
                // 提取序号并加1
                $lastNo = substr($lastStatement['statement_no'], -3);
                $nextNo = intval($lastNo) + 1;
            } else {
                // 今天第一个账单
                $nextNo = 1;
            }
            
            // 格式化序号（3位，不足补0）
            $statementNo = $prefix . $date . str_pad($nextNo, 3, '0', STR_PAD_LEFT);
            
            Db::commit();
            return $statementNo;
            
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
}
```

## 唯一性保证

### 1. 数据库约束

```sql
-- 添加唯一索引
ALTER TABLE yoshop_statement 
ADD UNIQUE KEY uk_statement_no (statement_no);
```

### 2. 并发控制

使用数据库锁防止并发生成重复编号：

```php
// 方案A: 悲观锁（推荐）
$lastStatement = self::where('statement_no', 'like', $prefix . $date . '%')
    ->lock(true)  // SELECT ... FOR UPDATE
    ->order('statement_no', 'desc')
    ->find();

// 方案B: 乐观锁
try {
    $statement = Statement::create([
        'statement_no' => $statementNo,
        // ... 其他字段
    ]);
} catch (\PDOException $e) {
    // 如果唯一索引冲突，重试
    if ($e->getCode() == 23000) {
        return self::generateStatementNo();
    }
    throw $e;
}
```

### 3. 重试机制

```php
public static function generateStatementNo($retryCount = 3)
{
    for ($i = 0; $i < $retryCount; $i++) {
        try {
            return self::doGenerateStatementNo();
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000 && $i < $retryCount - 1) {
                // 唯一索引冲突，重试
                usleep(100000); // 等待100ms
                continue;
            }
            throw $e;
        }
    }
    
    throw new \Exception('账单编号生成失败，请重试');
}
```

## 性能优化

### 1. 使用Redis缓存序号

```php
use think\Cache;

public static function generateStatementNo()
{
    $prefix = 'ST';
    $date = date('Ymd');
    $cacheKey = 'statement_no_seq_' . $date;
    
    // 从Redis获取并自增
    $nextNo = Cache::inc($cacheKey);
    
    // 设置过期时间（第二天凌晨）
    if ($nextNo == 1) {
        $expireTime = strtotime('tomorrow') - time();
        Cache::expire($cacheKey, $expireTime);
    }
    
    // 格式化编号
    $statementNo = $prefix . $date . str_pad($nextNo, 3, '0', STR_PAD_LEFT);
    
    return $statementNo;
}
```

**优点**：
- ✅ 性能高（无需查询数据库）
- ✅ 并发安全（Redis的INCR是原子操作）

**缺点**：
- ⚠️ 依赖Redis
- ⚠️ 如果Redis数据丢失，可能产生重复编号

### 2. 混合方案（推荐）

```php
public static function generateStatementNo()
{
    $prefix = 'ST';
    $date = date('Ymd');
    $cacheKey = 'statement_no_seq_' . $date;
    
    // 尝试从Redis获取
    if (Cache::has($cacheKey)) {
        $nextNo = Cache::inc($cacheKey);
    } else {
        // Redis不可用或首次，从数据库获取
        $lastStatement = self::where('statement_no', 'like', $prefix . $date . '%')
            ->order('statement_no', 'desc')
            ->find();
        
        $nextNo = $lastStatement ? intval(substr($lastStatement['statement_no'], -3)) + 1 : 1;
        
        // 写入Redis
        Cache::set($cacheKey, $nextNo, strtotime('tomorrow') - time());
    }
    
    // 格式化编号
    $statementNo = $prefix . $date . str_pad($nextNo, 3, '0', STR_PAD_LEFT);
    
    // 验证唯一性（数据库约束兜底）
    return $statementNo;
}
```

## 编号规则配置化

支持不同商家自定义编号规则：

```php
class Statement extends Model
{
    /**
     * 生成账单编号
     */
    public static function generateStatementNo($wxappId = null)
    {
        $wxappId = $wxappId ?: helper::getWxappId();
        
        // 获取编号规则配置
        $config = self::getNumberConfig($wxappId);
        
        $prefix = $config['prefix'] ?? 'ST';
        $dateFormat = $config['date_format'] ?? 'Ymd';
        $seqLength = $config['seq_length'] ?? 3;
        
        $date = date($dateFormat);
        
        // 生成序号
        $nextNo = self::getNextSequence($prefix, $date);
        
        // 格式化编号
        $statementNo = $prefix . $date . str_pad($nextNo, $seqLength, '0', STR_PAD_LEFT);
        
        return $statementNo;
    }
    
    /**
     * 获取编号规则配置
     */
    private static function getNumberConfig($wxappId)
    {
        // 从配置表或缓存获取
        return [
            'prefix' => 'ST',
            'date_format' => 'Ymd',
            'seq_length' => 3
        ];
    }
}
```

## 编号验证

```php
class Statement extends Model
{
    /**
     * 验证账单编号格式
     */
    public static function validateStatementNo($statementNo)
    {
        // 格式：ST + 8位日期 + 3位序号
        if (!preg_match('/^ST\d{8}\d{3}$/', $statementNo)) {
            return false;
        }
        
        // 验证日期有效性
        $date = substr($statementNo, 2, 8);
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);
        
        if (!checkdate($month, $day, $year)) {
            return false;
        }
        
        return true;
    }
}
```

## 编号查询优化

### 1. 添加索引

```sql
-- 账单编号索引（已有唯一索引）
ALTER TABLE yoshop_statement ADD UNIQUE KEY uk_statement_no (statement_no);

-- 日期范围查询索引
ALTER TABLE yoshop_statement ADD INDEX idx_create_time (create_time);
```

### 2. 按编号前缀查询

```php
// 查询某天的所有账单
$date = '20260228';
$statements = Statement::where('statement_no', 'like', 'ST' . $date . '%')
    ->select();

// 查询某月的所有账单
$month = '202602';
$statements = Statement::where('statement_no', 'like', 'ST' . $month . '%')
    ->select();
```

## 编号显示

### 1. 前端显示

```html
<!-- 完整编号 -->
<span class="statement-no">ST20260228001</span>

<!-- 带格式的编号 -->
<span class="statement-no">ST-2026-02-28-001</span>
```

### 2. 格式化函数

```php
class Statement extends Model
{
    /**
     * 格式化账单编号（用于显示）
     */
    public function getFormattedStatementNo()
    {
        $no = $this->statement_no;
        
        // ST20260228001 -> ST-2026-02-28-001
        return substr($no, 0, 2) . '-' . 
               substr($no, 2, 4) . '-' . 
               substr($no, 6, 2) . '-' . 
               substr($no, 8, 2) . '-' . 
               substr($no, 10, 3);
    }
}
```

## 特殊场景处理

### 1. 跨天生成

```php
// 如果在23:59:59生成账单，可能跨天
// 使用事务开始时间作为日期
Db::startTrans();
try {
    $date = date('Ymd'); // 锁定日期
    $statementNo = self::generateStatementNo($date);
    
    $statement = Statement::create([
        'statement_no' => $statementNo,
        // ...
    ]);
    
    Db::commit();
} catch (\Exception $e) {
    Db::rollback();
    throw $e;
}
```

### 2. 序号用尽

```php
// 如果一天超过999个账单
if ($nextNo > 999) {
    throw new \Exception('今日账单数量已达上限，请明天再试');
}

// 或者自动切换到4位序号
$seqLength = $nextNo > 999 ? 4 : 3;
$statementNo = $prefix . $date . str_pad($nextNo, $seqLength, '0', STR_PAD_LEFT);
```

## 测试用例

```php
class StatementNumberTest extends TestCase
{
    public function testGenerateStatementNo()
    {
        $no = Statement::generateStatementNo();
        
        // 验证格式
        $this->assertMatchesRegularExpression('/^ST\d{8}\d{3}$/', $no);
        
        // 验证日期
        $date = substr($no, 2, 8);
        $this->assertEquals(date('Ymd'), $date);
    }
    
    public function testUniqueStatementNo()
    {
        // 并发生成100个编号
        $numbers = [];
        for ($i = 0; $i < 100; $i++) {
            $numbers[] = Statement::generateStatementNo();
        }
        
        // 验证唯一性
        $this->assertEquals(100, count(array_unique($numbers)));
    }
    
    public function testValidateStatementNo()
    {
        $this->assertTrue(Statement::validateStatementNo('ST20260228001'));
        $this->assertFalse(Statement::validateStatementNo('ST20260231001')); // 无效日期
        $this->assertFalse(Statement::validateStatementNo('ST2026022800')); // 格式错误
    }
}
```

## 结论

**推荐方案**：日期 + 序号（ST + YYYYMMDD + 3位流水号）

**实施要点**：
- ✅ 数据库唯一索引保证唯一性
- ✅ 使用悲观锁防止并发冲突
- ✅ 可选Redis缓存提升性能
- ✅ 支持配置化（不同商家不同规则）
- ✅ 完善的验证和错误处理
- ⚠️ 注意跨天场景
- ⚠️ 考虑序号用尽的处理

**编号示例**：
- ST20260228001 - 2026年2月28日第1个账单
- ST20260228002 - 2026年2月28日第2个账单
- ST20260301001 - 2026年3月1日第1个账单
