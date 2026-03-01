# 文件存储策略研究

## 概述

账单系统需要存储Excel文件和二维码图片，需要确定合理的文件存储策略。

## 需求回顾

- Excel文件存储在 `./uploads/statements/` 目录
- 文件命名：`账单编号_用户ID.xlsx`（如：`ST20260128001_31398.xlsx`）
- 保留历史版本（重新生成时使用时间戳区分）
- 无权限控制
- 不需要过期清理

## 目录结构

```
./uploads/
├── statements/              # 账单Excel文件
│   ├── ST20260128001_31398.xlsx
│   ├── ST20260128001_31398_20260128120000.xlsx  # 历史版本
│   └── ST20260128002_31966.xlsx
├── qrcode/                  # 收款二维码
│   ├── alipay_31398.png
│   └── wechat_31398.png
└── logo/                    # 商家LOGO
    └── logo_31398.png
```

## 文件命名规范

### 1. 账单Excel文件

```php
class Statement
{
    /**
     * 生成Excel文件名
     */
    public static function generateExcelFileName($statementNo, $memberId, $withTimestamp = false)
    {
        $fileName = $statementNo . '_' . $memberId;
        
        if ($withTimestamp) {
            $fileName .= '_' . date('YmdHis');
        }
        
        return $fileName . '.xlsx';
    }
    
    /**
     * 获取Excel文件路径
     */
    public static function getExcelPath($statementNo, $memberId, $withTimestamp = false)
    {
        $fileName = self::generateExcelFileName($statementNo, $memberId, $withTimestamp);
        return './uploads/statements/' . $fileName;
    }
}
```

### 2. 二维码文件

```php
class FinanceConfig
{
    /**
     * 获取二维码路径
     */
    public static function getQrCodePath($memberId, $type = 'alipay')
    {
        return './uploads/qrcode/' . $type . '_' . $memberId . '.png';
    }
}
```

### 3. LOGO文件

```php
class FinanceConfig
{
    /**
     * 获取LOGO路径
     */
    public static function getLogoPath($memberId)
    {
        return './uploads/logo/logo_' . $memberId . '.png';
    }
}
```

## 文件版本管理

### 1. 保留历史版本

当重新生成账单时，保留旧版本：

```php
class ExcelService
{
    public function generateStatementExcel($statement, $packages, $template)
    {
        $statementNo = $statement['statement_no'];
        $memberId = $statement['member_id'];
        
        // 检查是否已存在文件
        $currentPath = Statement::getExcelPath($statementNo, $memberId);
        
        if (file_exists($currentPath)) {
            // 重命名为历史版本
            $historyPath = Statement::getExcelPath($statementNo, $memberId, true);
            rename($currentPath, $historyPath);
        }
        
        // 生成新文件
        $spreadsheet = $this->createSpreadsheet($statement, $packages, $template);
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($currentPath);
        
        return $currentPath;
    }
}
```

### 2. 查询历史版本

```php
class Statement
{
    /**
     * 获取所有版本的Excel文件
     */
    public function getExcelVersions()
    {
        $pattern = './uploads/statements/' . $this->statement_no . '_' . $this->member_id . '*.xlsx';
        $files = glob($pattern);
        
        // 按修改时间排序
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        return array_map(function($file) {
            return [
                'path' => $file,
                'name' => basename($file),
                'size' => filesize($file),
                'time' => filemtime($file)
            ];
        }, $files);
    }
}
```

## 文件上传处理

### 1. 二维码上传

```php
class FinanceConfigService
{
    /**
     * 上传二维码
     */
    public function uploadQrCode($file, $memberId, $type = 'alipay')
    {
        // 验证文件
        if (!$file->isValid()) {
            throw new \Exception('文件上传失败');
        }
        
        // 验证文件类型
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!in_array($file->getMime(), $allowedTypes)) {
            throw new \Exception('只支持PNG和JPG格式');
        }
        
        // 验证文件大小（最大2MB）
        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new \Exception('文件大小不能超过2MB');
        }
        
        // 保存文件
        $savePath = './uploads/qrcode/';
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true);
        }
        
        $fileName = $type . '_' . $memberId . '.png';
        $file->move($savePath, $fileName);
        
        return $savePath . $fileName;
    }
}
```

### 2. LOGO上传

```php
class FinanceConfigService
{
    /**
     * 上传LOGO
     */
    public function uploadLogo($file, $memberId)
    {
        // 验证文件
        if (!$file->isValid()) {
            throw new \Exception('文件上传失败');
        }
        
        // 验证文件类型
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!in_array($file->getMime(), $allowedTypes)) {
            throw new \Exception('只支持PNG和JPG格式');
        }
        
        // 验证文件大小（最大1MB）
        if ($file->getSize() > 1024 * 1024) {
            throw new \Exception('文件大小不能超过1MB');
        }
        
        // 保存文件
        $savePath = './uploads/logo/';
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true);
        }
        
        $fileName = 'logo_' . $memberId . '.png';
        $file->move($savePath, $fileName);
        
        return $savePath . $fileName;
    }
}
```

## 文件访问

### 1. 下载Excel

```php
class Statement extends Controller
{
    /**
     * 下载账单Excel
     */
    public function downloadExcel()
    {
        $statementId = $this->request->param('statement_id');
        
        $statement = Statement::find($statementId);
        if (!$statement) {
            return $this->renderError('账单不存在');
        }
        
        $filePath = $statement['excel_path'];
        if (!file_exists($filePath)) {
            return $this->renderError('文件不存在');
        }
        
        // 下载文件
        return download($filePath, basename($filePath));
    }
}
```

### 2. 预览图片

```php
class FinanceConfig extends Controller
{
    /**
     * 预览二维码
     */
    public function previewQrCode()
    {
        $memberId = $this->request->param('member_id');
        $type = $this->request->param('type', 'alipay');
        
        $filePath = FinanceConfig::getQrCodePath($memberId, $type);
        
        if (!file_exists($filePath)) {
            return $this->renderError('文件不存在');
        }
        
        // 输出图片
        header('Content-Type: image/png');
        readfile($filePath);
        exit;
    }
}
```

## 目录权限

### 1. 创建目录

```php
class FileHelper
{
    /**
     * 确保目录存在
     */
    public static function ensureDir($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        // 检查是否可写
        if (!is_writable($path)) {
            throw new \Exception('目录不可写: ' . $path);
        }
    }
}
```

### 2. 初始化目录

```php
// 在应用启动时创建必要的目录
FileHelper::ensureDir('./uploads/statements/');
FileHelper::ensureDir('./uploads/qrcode/');
FileHelper::ensureDir('./uploads/logo/');
```

## 文件清理（可选）

虽然需求中不需要过期清理，但可以提供手动清理功能：

```php
class StatementService
{
    /**
     * 清理历史版本（保留最新N个版本）
     */
    public function cleanHistoryVersions($statementNo, $memberId, $keepCount = 5)
    {
        $pattern = './uploads/statements/' . $statementNo . '_' . $memberId . '_*.xlsx';
        $files = glob($pattern);
        
        // 按修改时间排序
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // 删除多余的版本
        $deletedCount = 0;
        for ($i = $keepCount; $i < count($files); $i++) {
            if (unlink($files[$i])) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
}
```

## 安全考虑

### 1. 文件类型验证

```php
// 验证文件扩展名
$allowedExtensions = ['png', 'jpg', 'jpeg'];
$extension = strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions)) {
    throw new \Exception('不支持的文件类型');
}

// 验证MIME类型
$allowedMimes = ['image/png', 'image/jpeg', 'image/jpg'];
if (!in_array($file->getMime(), $allowedMimes)) {
    throw new \Exception('不支持的文件类型');
}
```

### 2. 文件大小限制

```php
// 限制文件大小
$maxSize = 2 * 1024 * 1024; // 2MB
if ($file->getSize() > $maxSize) {
    throw new \Exception('文件大小不能超过2MB');
}
```

### 3. 防止路径遍历

```php
// 清理文件名
$fileName = basename($file->getOriginalName());
$fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $fileName);
```

## 性能优化

### 1. 使用CDN（可选）

如果文件访问量大，可以考虑使用CDN：

```php
class FileHelper
{
    public static function getPublicUrl($filePath)
    {
        // 如果配置了CDN
        if (config('cdn.enabled')) {
            $cdnDomain = config('cdn.domain');
            return $cdnDomain . '/' . ltrim($filePath, './');
        }
        
        // 否则使用本地路径
        return request()->domain() . '/' . ltrim($filePath, './');
    }
}
```

### 2. 文件缓存头

```php
// 设置缓存头（图片）
header('Cache-Control: public, max-age=86400'); // 1天
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
```

## 备份策略（可选）

虽然不在需求范围内，但建议定期备份：

```bash
# 定时任务备份
0 2 * * * tar -czf /backup/statements_$(date +\%Y\%m\%d).tar.gz ./uploads/statements/
```

## 结论

文件存储策略总结：
- ✅ 目录结构清晰（statements/qrcode/logo）
- ✅ 文件命名规范（账单编号_用户ID）
- ✅ 支持历史版本（时间戳区分）
- ✅ 安全验证（类型、大小、路径）
- ✅ 易于维护和扩展
- ⚠️ 需要确保目录权限正确
- ⚠️ 建议定期备份重要文件

**实施建议**：
1. 应用启动时创建必要目录
2. 上传时严格验证文件类型和大小
3. 重新生成时保留历史版本
4. 提供手动清理历史版本功能（可选）
5. 考虑使用CDN加速访问（可选）
