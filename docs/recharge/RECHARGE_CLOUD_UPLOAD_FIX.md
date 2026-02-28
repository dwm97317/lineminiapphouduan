# 充值图片云端上传修复

## 问题
充值图片上传使用了本地存储,而不是像包裹录入一样上传到云端(阿里云OSS)。

## 解决方案
参考 `ApiPost.php` 中的Base64图片上传实现,使用 `StorageDriver` 上传到云端。

## 实现步骤

### 1. 使用StorageDriver替代本地文件保存

**之前的实现** (本地存储):
```php
// 直接保存到本地
$fullPath = ROOT_PATH . 'web/uploads/recharge/' . date('Ymd') . '/';
mkdir($fullPath, 0755, true);
file_put_contents($fullPath . $fileName, $imageData);
```

**新的实现** (云端存储):
```php
// 1. 创建临时文件
$tempPath = 'uploads/' . time() . rand(10000, 99999) . '_' . $index . '.' . $imageType;
file_put_contents($tempPath, $imageData);

// 2. 使用StorageDriver上传到云端
$storageConfig = SettingModel::getItem('storage', $this->wxapp_id);
$StorageDriver = new \app\common\library\storage\Driver($storageConfig);
$StorageDriver->setUploadFileByReal($tempPath);

if (!$StorageDriver->put()) {
    @unlink($tempPath);
    return $this->renderError('图片上传失败: ' . $StorageDriver->getError());
}

// 3. 获取上传后的文件信息
$fileName = $StorageDriver->getFileName();
$fileInfo = $StorageDriver->getFileInfo();

// 4. 删除临时文件
@unlink($tempPath);
```

### 2. 使用addUploadFile方法保存文件记录

参考 `ApiPost.php` 的实现:

```php
private function addUploadFile($fileName, $fileInfo, $fileType)
{
    // 存储引擎
    $storageConfig = SettingModel::getItem('storage', $this->wxapp_id);
    $storage = $storageConfig['default'];
    // 存储域名
    $fileUrl = isset($storageConfig['engine'][$storage]['domain'])
        ? $storageConfig['engine'][$storage]['domain'] : '';
    
    // 添加文件库记录
    $model = new \app\api\model\UploadFile();
    $model->add([
        'storage' => $storage,  // 'aliyun' 或 'local'
        'file_url' => $fileUrl,  // 阿里云OSS域名
        'file_name' => $fileName,  // 云端文件名
        'file_size' => $fileInfo['size'],
        'file_type' => $fileType,
        'extension' => pathinfo($fileInfo['name'], PATHINFO_EXTENSION),
        'is_user' => 1
    ]);
    return $model;
}
```

### 3. 完整流程

```php
public function apply()
{
    // 1. 验证数据
    // ...
    
    // 2. 获取存储配置
    $storageConfig = SettingModel::getItem('storage', $this->wxapp_id);
    
    // 3. 处理每张图片
    $imageIds = [];
    foreach ($data['screenshots'] as $index => $base64Image) {
        // 3.1 解析Base64
        $imageData = base64_decode(...);
        
        // 3.2 创建临时文件
        $tempPath = 'uploads/' . time() . rand(10000, 99999) . '_' . $index . '.' . $imageType;
        file_put_contents($tempPath, $imageData);
        
        // 3.3 上传到云端
        $StorageDriver = new \app\common\library\storage\Driver($storageConfig);
        $StorageDriver->setUploadFileByReal($tempPath);
        $StorageDriver->put();
        
        // 3.4 获取文件信息
        $fileName = $StorageDriver->getFileName();
        $fileInfo = $StorageDriver->getFileInfo();
        
        // 3.5 删除临时文件
        @unlink($tempPath);
        
        // 3.6 保存文件记录
        $uploadFile = $this->addUploadFile($fileName, $fileInfo, 'image');
        $imageIds[] = $uploadFile->file_id;
    }
    
    // 4. 保存Certificate记录
    $certificateModel->add([
        'imageIds' => $imageIds,
        // ...
    ]);
}
```

## 数据库记录对比

### 本地存储 (之前)
```
yoshop_upload_file:
- storage: 'local'
- file_url: ''
- file_name: 'recharge/20260115/xxx.jpg'
```

### 云端存储 (现在)
```
yoshop_upload_file:
- storage: 'aliyun'
- file_url: 'https://thosszhuanyun.oss-accelerate.aliyuncs.com'
- file_name: '202601151820150ad284154.jpg'
```

## 后台显示

### UploadFile模型的file_path虚拟属性
```php
public function getFilePathAttr($value, $data)
{
    if ($data['storage'] === 'local') {
        return self::$base_url . 'uploads/' . $data['file_name'];
    }
    // 云端存储
    return $data['file_url'] . '/' . $data['file_name'];
}
```

### 最终URL
- **本地**: `http://localhost/uploads/recharge/20260115/xxx.jpg`
- **云端**: `https://thosszhuanyun.oss-accelerate.aliyuncs.com/202601151820150ad284154.jpg`

## 优势

1. **一致性**: 与包裹录入使用相同的上传方式
2. **可靠性**: 使用云端存储,不依赖本地磁盘
3. **可扩展性**: 支持多种存储引擎(阿里云OSS、本地等)
4. **性能**: 云端CDN加速,图片加载更快

## 参考文件

- `Lineminiapp/source/application/api/controller/ApiPost.php` - 包裹录入图片上传参考
- `Lineminiapp/source/application/api/controller/Recharge.php` - 充值控制器
- `Lineminiapp/source/application/common/library/storage/Driver.php` - 存储驱动
- `Lineminiapp/source/application/api/model/UploadFile.php` - 文件上传模型

## 测试

1. 访问 `https://localhost:9000/mine/recharge`
2. 填写充值信息并上传图片
3. 提交后检查数据库 `yoshop_upload_file` 表
4. 确认 `storage` 字段为 `'aliyun'`
5. 访问后台 `/store/setting.certificate/index` 查看图片

## 状态
✅ **已修复** - 充值图片现在上传到云端,与包裹录入保持一致
