# MCP 配置指南

## 当前状态

检测到已安装的MySQL MCP服务器：
- `@benborla29/mcp-server-mysql@2.0.7`
- `@malove86/mcp-mysql-server@0.2.4`

## 配置步骤

### 1. 找到 Claude Desktop 配置文件

**Windows**:
```
%APPDATA%\Claude\claude_desktop_config.json
```

**Mac**:
```
~/Library/Application Support/Claude/claude_desktop_config.json
```

**Linux**:
```
~/.config/Claude/claude_desktop_config.json
```

### 2. 添加 MCP 服务器配置

使用以下任一MCP服务器的配置：

#### 选项 1: @benborla29/mcp-server-mysql

```json
{
  "mcpServers": {
    "mysql": {
      "command": "node",
      "args": [
        "C:\\Users\\{YOUR_USERNAME}\\AppData\\Roaming\\npm\\node_modules\\@benborla29\\mcp-server-mysql\\dist\\index.js"
      ],
      "env": {
        "DB_HOST": "103.119.?.?.?",
        "DB_PORT": "3306",
        "DB_USER": "root",
        "DB_PASSWORD": "cJGzwZTDCLHzWXN4",
        "DB_DATABASE": "xinsuju"
      }
    }
  }
}
```

#### 选项 2: @malove86/mcp-mysql-server

```json
{
  "mcpServers": {
    "mysql": {
      "command": "node",
      "args": [
        "C:\\Users\\{YOUR_USERNAME}\\AppData\\Roaming\\npm\\node_modules\\@malove86\\mcp-mysql-server\\dist\\index.js"
      ],
      "env": {
        "DB_HOST": "103.119.?.?.?",
        "DB_PORT": "3306",
        "DB_USER": "root",
        "DB_PASSWORD": "cJGzwZTDCLHzWXN4",
        "DB_DATABASE": "xinsuju"
      }
    }
  }
}
```

**注意**: 将 `{YOUR_USERNAME}` 替换为你的实际用户名。

### 3. 查找实际安装路径

运行以下命令查找MCP服务器的实际路径：

```bash
npm root -g
```

然后在输出目录中找到：
- `node_modules/@benborla29/mcp-server-mysql/`
- `node_modules/@malove86/mcp-mysql-server/`

### 4. 验证 MCP 服务器

1. 保存配置文件
2. 重启 Claude Desktop
3. 打开新对话
4. 尝试使用数据库查询功能

## 快速替代方案

如果MCP配置困难，我已经准备好了修复文件：

### SQL修复文件
```
D:\2025profile\Lineminiapp\add_country_id_to_inpack.sql
```

### PHP代码已修复
```
D:\2025profile\Lineminiapp\source\application\api\controller\Package.php
```

### 执行SQL

在数据库管理工具中执行以下SQL：

```sql
ALTER TABLE `yoshop_inpack`
ADD COLUMN `country_id` int(11) NULL DEFAULT NULL COMMENT '国家ID'
AFTER `address_id`;
```

### 使用命令行

```bash
mysql -h 103.119.?.?.? -P 3306 -u root -p xinsuju
```

然后在MySQL命令行中执行：
```sql
USE xinsuju;
ALTER TABLE `yoshop_inpack`
ADD COLUMN `country_id` int(11) NULL DEFAULT NULL COMMENT '国家ID'
AFTER `address_id`;
```

### 验证修复

```sql
SHOW COLUMNS FROM `yoshop_inpack` LIKE 'country_id';
```

如果看到结果，说明字段添加成功。

## 测试功能

执行SQL后，重新测试打包功能应该不再报错。
