# MySQL MCP Server 安装指南

## 前置要求

1. **Node.js 18+**
2. **MySQL Server 5.7+**
3. **Git**

## 安装步骤

### 1. 安装 MCP Database Server

```bash
# 使用 npm 安装
npm install -g @executeautomation/mcp-database-server
```

### 2. 配置环境变量

创建环境变量文件或设置系统环境变量：

**Windows (PowerShell)**:
```powershell
$env:DB_HOST = "103.119.?.?.?"
$env:DB_PORT = "3306"
$env:DB_USERNAME = "root"
$env:DB_PASSWORD = "cJGzwZTDCLHzWXN4"
$env:DB_DATABASE = "xinsuju"
$env:DB_TYPE = "mysql"
```

**Windows (CMD)**:
```cmd
set DB_HOST=103.119.?.?.?
set DB_PORT=3306
set DB_USERNAME=root
set DB_PASSWORD=cJGzwZTDCLHzWXN4
set DB_DATABASE=xinsuju
set DB_TYPE=mysql
```

**Linux/Mac**:
```bash
export DB_HOST="103.119.?.?.?"
export DB_PORT="3306"
export DB_USERNAME="root"
export DB_PASSWORD="cJGzwZTDCLHzWXN4"
export DB_DATABASE="xinsuju"
export DB_TYPE="mysql"
```

### 3. 启动 MCP Server

```bash
npx @executeautomation/mcp-database-server
```

### 4. 配置 Claude Desktop

在 Claude Desktop 的配置文件中添加 MCP 服务器。

**Windows**: `%APPDATA%\Claude\claude_desktop_config.json`
**Mac**: `~/Library/Application Support/Claude/claude_desktop_config.json`
**Linux**: `~/.config/Claude/claude_desktop_config.json`

配置示例：
```json
{
  "mcpServers": {
    "mysql": {
      "command": "npx",
      "args": [
        "-y",
        "@executeautomation/mcp-database-server"
      ],
      "env": {
        "DB_HOST": "103.119.?.?.?",
        "DB_PORT": "3306",
        "DB_USERNAME": "root",
        "DB_PASSWORD": "cJGzwZTDCLHzWXN4",
        "DB_DATABASE": "xinsuju",
        "DB_TYPE": "mysql"
      }
    }
  }
}
```

### 5. 重启 Claude Desktop

配置完成后，重启 Claude Desktop 使配置生效。

### 6. 验证 MCP 可用性

重启后，检查 Claude Desktop 中是否有 MySQL 相关的 MCP 工具。

## 使用示例

配置成功后，可以通过自然语言查询数据库：

- "检查 yoshop_inpack 表是否有 country_id 字段"
- "添加 country_id 字段到 yoshop_inpack 表"
- "查询用户地址表结构"

## 故障排查

**问题**: MCP 服务器未显示
- 检查环境变量是否正确设置
- 确认 MCP 命令可以正常运行
- 查看日志文件中的错误信息

**问题**: 数据库连接失败
- 验证数据库凭据
- 检查网络连接
- 确认 MySQL 服务器允许远程连接

**问题**: 权限错误
- 确认数据库用户有足够权限
- 检查 MySQL 配置允许来自本地的连接

## 官方资源

- GitHub: https://github.com/executeautomation/mcp-database-server
- 文档: https://github.com/executeautomation/mcp-database-server#readme

## 替代方案

如果 MCP 安装困难，可以直接使用我已经创建的 SQL 文件：

**文件**: `D:\2025profile\Lineminiapp\add_country_id_to_inpack.sql`

**手动执行**:
```bash
mysql -h 103.119.?.?.? -P 3306 -u root -p xinsuju < add_country_id_to_inpack.sql
```

或使用数据库管理工具（phpMyAdmin、Navicat、DBeaver）执行 SQL。

## 验证修复

SQL 执行后，验证字段已添加：
```sql
SHOW COLUMNS FROM `yoshop_inpack` LIKE 'country_id';
```

应该看到返回的 `country_id` 字段信息。
