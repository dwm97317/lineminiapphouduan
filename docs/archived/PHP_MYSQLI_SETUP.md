# PHP MySQLi 扩展启用指南

## 问题
终端中的PHP无法使用mysqli扩展，导致数据库脚本失败：
```
Fatal error: Uncaught Error: Class 'mysqli' not found
```

## 解决方案

### 1. 检查PHP配置文件位置
```powershell
php -r "phpinfo();" | Select-String "Configuration File"
```

输出：
```
Configuration File (php.ini) Path => C:\Windows
Loaded Configuration File => D:\php7\php.ini
```

### 2. 检查当前已加载的扩展
```powershell
php -m | Select-String -Pattern "mysql|pdo"
```

修改前：
```
mysqlnd
PDO
pdo_mysql
```

### 3. 启用mysqli扩展

编辑 `D:\php7\php.ini` 文件，找到以下行：
```ini
;extension=mysqli
```

去掉前面的分号（`;`）：
```ini
extension=mysqli
```

或者使用PowerShell命令自动修改：
```powershell
(Get-Content "D:\php7\php.ini") -replace '^;extension=mysqli$', 'extension=mysqli' | Set-Content "D:\php7\php.ini"
```

### 4. 验证扩展已加载
```powershell
php -m | Select-String "mysqli"
```

输出：
```
mysqli
```

### 5. 测试数据库连接
```powershell
php Lineminiapp/web/check_warehouse_settings.php
```

成功输出：
```
Store Settings:
is_show: 1
link_mode: 20
address_mode: 20
is_change_uid: 1

LINE User:
user_id: 31960
nickName: TLLCARGO ไทย-ลาว
user_code: NULL

Warehouse:
shop_id: 167
linkman: 李四
```

## 当前PHP环境

- **PHP版本**: 7.3.9 (cli)
- **配置文件**: D:\php7\php.ini
- **已启用的MySQL扩展**:
  - mysqlnd (MySQL Native Driver)
  - PDO (PHP Data Objects)
  - pdo_mysql (PDO MySQL Driver)
  - mysqli (MySQL Improved Extension) ✅ 新启用

## 注意事项

1. **PDO vs MySQLi**: 
   - PDO支持多种数据库（MySQL, PostgreSQL, SQLite等）
   - MySQLi只支持MySQL，但提供更多MySQL特定功能
   - 两者都已启用，可以根据需要选择使用

2. **重启不需要**: 修改php.ini后，CLI模式下的PHP会立即生效，无需重启服务

3. **Web服务器**: 如果使用Apache或Nginx，需要重启Web服务器才能使配置生效

## 相关文件

- `Lineminiapp/web/check_warehouse_settings.php` - 使用mysqli检查设置
- `Lineminiapp/web/verify_warehouse_linkman.php` - 使用PDO验证仓库信息
- `Lineminiapp/web/test_line_warehouse_address.php` - 使用PDO测试LINE仓库地址

## 完成时间
2026-01-13
