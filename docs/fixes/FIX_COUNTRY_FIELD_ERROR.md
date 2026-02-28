# 修复 country 字段错误

## 问题描述
错误信息：`Builder.php line 115 数据表字段不存在:[country]`

发生位置：`source/application/api/controller/Package.php` 第717行

## 根本原因
代码尝试向 `yoshop_inpack` 表写入 `country` 字段，但当前数据库中该表缺少此字段。

## 解决方案

### 方案1：添加 country 字段到数据库（推荐）

执行以下SQL：

```sql
ALTER TABLE `yoshop_inpack`
ADD COLUMN `country` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci
NULL DEFAULT NULL
COMMENT '目标国家（可废弃，通过address获取）'
AFTER `volume`;
```

**优点**：
- 保持代码逻辑不变
- 数据完整
- 符合原始SQL定义

### 方案2：移除代码中的 country 字段

如果确认不需要 `country` 字段，修改 `source/application/api/controller/Package.php`：

**删除第717行**：
```php
'country' => $address['country'],
```

**优点**：
- 不需要修改数据库
- 代码更简洁（因为注释说明该字段可废弃）

**缺点**：
- 可能影响其他依赖该字段的逻辑
- 需要全面检查代码中是否有使用 `country` 字段的地方

## 推荐步骤

1. **先检查数据库实际结构**：
   ```sql
   SHOW COLUMNS FROM `yoshop_inpack`;
   ```

2. **如果数据库确实缺少 `country` 字段**：
   - 执行方案1的SQL添加字段
   - 或者执行方案2删除代码中的字段引用

3. **测试验证**：
   - 重新测试打包功能
   - 确认错误已解决

## 检查命令

查看当前表结构：
```sql
DESC `yoshop_inpack`;
```

查看完整建表语句：
```sql
SHOW CREATE TABLE `yoshop_inpack`;
```
