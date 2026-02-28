# MCP MySQL Server 配置文件

## 数据库连接信息
- Host: 103.119.1.84
- Port: 3306
- Username: root
- Password: cJGzwZTDCLHzWXN4
- Database: xinsuju

## Claude Desktop 配置文件

**Windows 路径**:
```
C:\Users\{YOUR_USERNAME}\AppData\Roaming\Claude\claude_desktop_config.json
```

## 完整配置示例

```json
{
  "mcpServers": {
    "mysql-xinsuju": {
      "command": "node",
      "args": [
        "C:\\Users\\weiming\\AppData\\Roaming\\npm\\node_modules\\@benborla29\\mcp-server-mysql\\dist\\index.js"
      ],
      "env": {
        "DB_HOST": "103.119.1.84",
        "DB_PORT": "3306",
        "DB_USER": "root",
        "DB_PASSWORD": "cJGzwZTDCLHzWXN4",
        "DB_DATABASE": "xinsuju"
      }
    }
  }
}
```

## 替代方案：直接使用命令行MySQL

### 检查表结构
```bash
mysql -h 103.119.1.84 -P 3306 -u root -p xinsuju
```

然后在MySQL命令行中执行：
```sql
SHOW COLUMNS FROM `yoshop_inpack`;
```

### 添加country_id字段
```sql
ALTER TABLE `yoshop_inpack`
ADD COLUMN `country_id` int(11) NULL DEFAULT NULL COMMENT '国家ID'
AFTER `address_id`;
```

## 验证字段已添加

```sql
SHOW COLUMNS FROM `yoshop_inpack` LIKE 'country_id';
```

## 当前修复状态

### ✅ 已完成的修复

1. **前端Toast组件** - `src/utils/toast.jsx`
   - 修复崩溃问题
   - SVG图标改为HTML字符串

2. **前端错误处理** - `src/utils/request.js`
   - 改进错误信息传递
   - 添加HTML错误解析

3. **后端PHP代码** - `source/application/api/controller/Package.php`
   - 添加 `country_id` 字段赋值
   - 从地址获取国家ID

### ⚠️ 待完成

- 执行SQL添加 `country_id` 字段到数据库

## 手动执行SQL（推荐）

**方法1：使用MySQL客户端**
1. 打开Navicat/DBeaver/phpMyAdmin
2. 连接到：103.119.1.84:3306 / xinsuju / root
3. 执行以下SQL：

```sql
ALTER TABLE `yoshop_inpack`
ADD COLUMN `country_id` int(11) NULL DEFAULT NULL COMMENT '国家ID'
AFTER `address_id`;
```

**方法2：使用命令行**
```bash
mysql -h 103.119.1.84 -P 3306 -u root -p
# 输入密码后：
USE xinsuju;
ALTER TABLE `yoshop_inpack` ADD COLUMN `country_id` int(11) NULL DEFAULT NULL COMMENT '国家ID' AFTER `address_id`;
```

**方法3：使用已准备好的SQL文件**
```bash
mysql -h 103.119.1.84 -P 3306 -u root -p xinsuju < D:\2025profile\Lineminiapp\add_country_id_to_inpack.sql
```
