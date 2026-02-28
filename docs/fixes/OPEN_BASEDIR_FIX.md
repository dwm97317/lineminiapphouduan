# open_basedir 限制修复指南

## 错误信息
```
Warning: require(): open_basedir restriction in effect. 
File(/www/wwwroot/longthai.itaoth.com/source/thinkphp/start.php) is not within the allowed path(s): 
(/www/wwwroot/longthai.itaoth.com/web/:/tmp/)
```

## 问题原因

PHP 的 `open_basedir` 安全限制阻止了访问 `web/` 目录之外的文件。

**当前配置**: 只允许访问 `/www/wwwroot/longthai.itaoth.com/web/` 和 `/tmp/`
**实际需要**: 访问 `/www/wwwroot/longthai.itaoth.com/source/`

---

## 解决方案

### 方案 1: 修改 open_basedir（推荐）⭐

#### 使用宝塔面板

1. **登录宝塔面板**
   ```
   http://你的服务器IP:8888
   ```

2. **网站** → 找到 `longthai.itaoth.com` → 点击 **设置**

3. **网站目录** 标签

4. 找到 **防跨站攻击(open_basedir)**

5. **修改为**:
   ```
   /www/wwwroot/longthai.itaoth.com/:/tmp/:/proc/
   ```
   
   或者直接**关闭** open_basedir（不推荐，安全性较低）

6. **保存**

7. **重启 PHP-FPM**
   - 软件商店 → PHP 7.2 → 服务 → 重启

#### 手动修改 php.ini

如果没有宝塔面板：

```bash
# 1. 找到 php.ini 文件
php -i | grep php.ini

# 2. 编辑 php.ini
vi /www/server/php/72/etc/php.ini

# 3. 找到 open_basedir 行，修改为：
open_basedir = /www/wwwroot/longthai.itaoth.com/:/tmp/:/proc/

# 4. 保存并重启 PHP-FPM
systemctl restart php-fpm
```

---

### 方案 2: 调整项目结构

#### 选项 A: 移动 source 到 web 内

```bash
# SSH 连接到服务器
cd /www/wwwroot/longthai.itaoth.com

# 移动 source 目录
mv source web/source

# 修改 web/index.php
vi web/index.php
```

修改 `web/index.php` 第 24 行:
```php
// 修改前
require __DIR__ . '/../source/application/../thinkphp/start.php';

// 修改后
require __DIR__ . '/source/application/../thinkphp/start.php';
```

#### 选项 B: 创建符号链接

```bash
cd /www/wwwroot/longthai.itaoth.com/web
ln -s ../source source
```

---

### 方案 3: 修改网站根目录（最佳实践）⭐⭐

#### 宝塔面板配置

1. **网站** → `longthai.itaoth.com` → **设置**

2. **网站目录** → **运行目录** 修改为:
   ```
   /
   ```
   （根目录，不是 `/web`）

3. **保存**

4. **修改伪静态规则**（如果需要）

#### 修改 .htaccess

在 `/www/wwwroot/longthai.itaoth.com/.htaccess`:

```apache
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On

  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ web/index.php/$1 [QSA,PT,L]
</IfModule>
```

这样访问 `https://longthai.itaoth.com/index.php?s=api/` 会自动路由到 `web/index.php`

---

## 验证修复

### 1. 检查 open_basedir 配置

创建测试文件 `/www/wwwroot/longthai.itaoth.com/web/test_openbasedir.php`:

```php
<?php
echo "open_basedir: " . ini_get('open_basedir') . "\n";
echo "\n";

// 测试访问 source 目录
$file = __DIR__ . '/../source/thinkphp/start.php';
echo "测试文件: $file\n";

if (file_exists($file)) {
    echo "✅ 可以访问 source 目录\n";
} else {
    echo "❌ 无法访问 source 目录\n";
}
?>
```

访问: `https://longthai.itaoth.com/web/test_openbasedir.php`

### 2. 测试 API

访问: `https://longthai.itaoth.com/web/index.php?s=api/user/index`

应该返回 JSON 数据，而不是错误。

---

## 推荐配置

### 最佳实践配置

```
网站根目录: /www/wwwroot/longthai.itaoth.com
运行目录: /web
open_basedir: /www/wwwroot/longthai.itaoth.com/:/tmp/:/proc/
```

### 目录结构

```
/www/wwwroot/longthai.itaoth.com/
├── source/              # 后端代码
│   ├── application/
│   └── thinkphp/
├── web/                 # 网站根目录（对外访问）
│   ├── index.php       # 入口文件
│   └── static/
├── runtime/            # 运行时文件
└── .htaccess          # 重写规则
```

---

## 常见问题

### Q1: 修改后仍然报错？

**A**: 需要重启 PHP-FPM:
```bash
# 宝塔面板
软件商店 → PHP 7.2 → 服务 → 重启

# 命令行
systemctl restart php-fpm
```

### Q2: 不知道 PHP 版本？

**A**: 
```bash
php -v
```

或在宝塔面板查看：网站 → 设置 → PHP 版本

### Q3: 找不到 open_basedir 设置？

**A**: 可能在 `.user.ini` 文件中:
```bash
cat /www/wwwroot/longthai.itaoth.com/web/.user.ini
```

修改这个文件，然后重启 PHP-FPM。

### Q4: 关闭 open_basedir 安全吗？

**A**: 不太安全，但如果是独立服务器（不是共享主机），影响较小。建议使用方案 1 正确配置路径。

---

## 快速命令

### 检查当前配置
```bash
php -r "echo ini_get('open_basedir');"
```

### 查看 PHP 配置文件位置
```bash
php --ini
```

### 重启 PHP-FPM
```bash
# CentOS/RHEL
systemctl restart php-fpm

# Ubuntu/Debian
service php7.2-fpm restart

# 宝塔面板
bt restart php-fpm-72
```

---

## 联系支持

如果以上方案都无法解决，请联系服务器管理员或主机提供商。

**需要提供的信息**:
- 服务器类型（宝塔/LNMP/其他）
- PHP 版本
- 错误日志
- 当前 open_basedir 配置

---

## 修复后的 API 地址

修复后，前端应该访问：

```
https://longthai.itaoth.com/web/index.php?s=api/
```

或者如果配置了伪静态：

```
https://longthai.itaoth.com/index.php?s=api/
```

记得更新前端配置文件 `src/config/config.js`！
