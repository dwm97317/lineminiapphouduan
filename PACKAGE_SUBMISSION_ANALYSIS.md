# Lineminiapp 后端提交打包实现分析

## 概述
Lineminiapp 后端使用 ThinkPHP 框架实现，提交打包功能主要在 `Package` 控制器中实现。

## 核心文件
- **控制器**: `source/application/api/controller/Package.php`
- **模型**: 
  - `source/application/api/model/Package.php` (包裹模型)
  - `source/application/api/model/Inpack.php` (集运订单模型)
  - `source/application/api/model/InpackService.php` (打包服务模型)

## 提交打包的主要方法

### 1. `postPack()` - 标准提交打包
**位置**: Package.php 第 766 行

**功能**: 用户选择多个包裹进行打包，创建集运订单

**流程**:
```
1. 接收参数
   - packids: 包裹ID列表（逗号分隔）
   - line_id: 线路ID
   - pack_ids: 打包服务ID
   - address_id: 收货地址ID
   - waitreceivedmoney: 代收货款
   - remark: 备注

2. 数据验证
   - 验证包裹是否存在
   - 验证包裹是否属于同一仓库
   - 验证地址信息
   - 验证线路信息

3. 创建集运订单 (Inpack)
   - 生成订单号 (order_sn)
   - 设置初始状态为 1
   - 关联包裹ID、地址、线路等信息
   - 初始化运费、重量等字段

4. 处理打包服务
   - 调用 InpackServiceModel->doservice()
   - 关联打包服务到集运订单

5. 更新包裹状态
   - 将包裹状态更新为 5 (已打包)
   - 关联包裹到集运订单 (inpack_id)
   - 更新包裹的线路、地址等信息

6. 物流信息记录
   - 添加物流轨迹记录
   - 更新包裹的订单号

7. 费用计算
   - 如果开启自动计费，调用 getpackfree()

8. 通知仓管员
   - 查找仓库的员工
   - 发送打包通知消息

9. 返回成功
```

**关键代码片段**:
```php
// 创建集运订单
$inpackOrder = [
  'order_sn' => createSn(),
  'remark' => $remark,
  'pack_ids' => $ids,
  'pack_services_id' => $pack_ids,
  'storage_id' => $pack[0]['storage_id'],
  'address_id' => $address_id,
  'member_id' => $this->user['user_id'],
  'status' => 1,
  'line_id' => $line_id,
  'unpack_time' => getTime(),
  'created_time' => getTime(),
  'updated_time' => getTime(),
];

$inpack = (new Inpack())->insertGetId($inpackOrder);

// 更新包裹状态
(new PackageModel())->whereIn('id',$idsArr)->update([
    'status' => 5,
    'line_id' => $line_id,
    'pack_service' => $pack_ids,
    'address_id' => $address_id,
    'inpack_id' => $inpack
]);
```

### 2. `quickPackageItAll()` - 快速批量打包
**位置**: Package.php 第 76 行

**功能**: 仓管员快速录入多个包裹并打包

**流程**:
```
1. 接收参数
   - packids: 快递单号列表（逗号分隔）
   - pack_ids: 打包服务ID
   - weight, length, width, height: 尺寸信息
   - remark: 备注

2. 创建集运订单
   - 生成订单号
   - 设置状态为 1
   - 记录尺寸和重量信息

3. 处理包裹
   - 遍历快递单号列表
   - 如果包裹已存在：
     * 检查是否已被打包（status > 4）
     * 更新入库时间和状态为 8
   - 如果包裹不存在：
     * 创建新包裹记录
     * 设置状态为 8（已入库）

4. 处理打包服务
   - 关联打包服务

5. 返回成功
```

**特点**:
- 适用于仓管员快速录单
- 可以处理未预报的包裹
- 自动创建包裹记录

### 3. `fastPack()` - 仓管员快速录单
**位置**: Package.php 第 850 行

**功能**: 仓管员为用户快速创建单个包裹并打包

**流程**:
```
1. 验证仓管员权限
   - 检查用户是否为仓管员

2. 创建包裹记录
   - 生成快递单号（createJysn）
   - 设置状态为 4
   - 记录尺寸和重量

3. 创建集运订单
   - 关联刚创建的包裹
   - 设置支付方式

4. 处理打包服务

5. 更新物流信息

6. 通知仓管员

7. 返回成功
```

## 数据库表结构

### Inpack 表（集运订单）
主要字段：
- `id`: 主键
- `order_sn`: 订单号
- `member_id`: 用户ID
- `pack_ids`: 包裹ID列表（逗号分隔）
- `storage_id`: 仓库ID
- `address_id`: 收货地址ID
- `line_id`: 线路ID
- `status`: 状态（1-待查验, 2-待支付, 3-已支付, 4-下架, 5-打包, 6-转运中, 7-已到货, 8-已签收）
- `free`: 运费
- `pack_free`: 打包费
- `other_free`: 其他费用
- `weight`: 重量
- `cale_weight`: 计费重量
- `volume`: 体积
- `pack_services_id`: 打包服务ID
- `remark`: 备注
- `unpack_time`: 提交打包时间
- `is_pay`: 是否支付
- `pay_time`: 支付时间

### Package 表（包裹）
主要字段：
- `id`: 主键
- `order_sn`: 包裹单号
- `express_num`: 快递单号
- `member_id`: 用户ID
- `storage_id`: 仓库ID
- `inpack_id`: 集运订单ID
- `status`: 状态（1-待认领, 2-已预报, 3-已入库, 4-待打包, 5-已打包, 6-已支付, 7-已拣货, 8-已打包待发货, 9-已发货, 10-已签收, -1-问题件）
- `express_id`: 快递公司ID
- `express_name`: 快递公司名称
- `line_id`: 线路ID
- `address_id`: 地址ID
- `weight`: 重量
- `price`: 申报价值
- `entering_warehouse_time`: 入库时间

## 状态流转

### 包裹状态流转
```
1 (待认领) → 2 (已预报) → 3 (已入库) → 4 (待打包) → 5 (已打包) 
→ 6 (已支付) → 7 (已拣货) → 8 (已打包待发货) → 9 (已发货) → 10 (已签收)
```

### 集运订单状态流转
```
1 (待查验) → 2 (待支付) → 3 (已支付) → 4 (下架) → 5 (打包) 
→ 6 (转运中) → 7 (已到货) → 8 (已签收)
```

## 关键业务逻辑

### 1. 订单号生成
- 使用 `createSn()` 生成标准订单号
- 使用 `createSnByUserIdCid()` 根据用户ID和国家ID生成订单号
- 使用 `createNewOrderSn()` 生成自定义格式订单号

### 2. 费用计算
- 运费 (free): 根据线路和重量计算
- 打包费 (pack_free): 根据打包服务计算
- 其他费用 (other_free): 额外服务费用
- 总费用 = free + pack_free + other_free

### 3. 打包服务处理
通过 `InpackServiceModel->doservice()` 方法处理：
- 关联打包服务到集运订单
- 计算打包服务费用

### 4. 物流信息记录
使用 `Logistics` 模型记录：
- 包裹的每个状态变更
- 订单号关联
- 时间戳记录

### 5. 消息通知
- 通知仓管员有新的打包订单
- 使用 `Message::send()` 发送模板消息
- 支持新旧两种模板消息格式

## API 接口

### 提交打包接口
**URL**: `/api/package/postPack`
**方法**: POST
**参数**:
```json
{
  "packids": "1,2,3",
  "line_id": 1,
  "pack_ids": "1,2",
  "address_id": 1,
  "waitreceivedmoney": 0,
  "remark": "备注信息"
}
```

**返回**:
```json
{
  "code": 1,
  "msg": "打包包裹提交成功",
  "data": null
}
```

### 快速批量打包接口
**URL**: `/api/package/quickPackageItAll`
**方法**: POST
**参数**:
```json
{
  "packids": "SF123456,SF789012",
  "pack_ids": "1,2",
  "weight": 1.5,
  "length": 30,
  "width": 20,
  "height": 10,
  "remark": "备注"
}
```

## 与前端的对接

### 前端需要提供的数据
1. **包裹选择**: 用户选择的包裹ID列表
2. **线路选择**: 用户选择的运输线路
3. **地址选择**: 用户选择的收货地址
4. **打包服务**: 用户选择的打包服务（可选）
5. **备注信息**: 用户填写的备注

### 前端需要处理的状态
1. **加载状态**: 提交打包时显示加载动画
2. **成功状态**: 提交成功后跳转到订单列表
3. **错误处理**: 显示错误信息（如包裹已被打包、地址错误等）

### 前端需要调用的接口
1. `unpack()`: 获取未打包的包裹列表
2. `lineplus()`: 获取可用的线路列表
3. `postservice()`: 获取打包服务列表
4. `postPack()`: 提交打包

## 注意事项

1. **事务处理**: 所有打包操作都使用数据库事务，确保数据一致性
2. **权限验证**: 需要验证用户登录状态和包裹所有权
3. **状态检查**: 提交前需要检查包裹状态，避免重复打包
4. **仓库限制**: 只能打包同一仓库的包裹
5. **费用计算**: 支持自动计费和手动计费两种模式
6. **消息通知**: 打包成功后会通知相关仓管员

## 优化建议

1. **批量操作优化**: 使用批量更新减少数据库查询
2. **异步处理**: 消息通知可以使用队列异步处理
3. **缓存优化**: 线路、服务等常用数据可以缓存
4. **日志记录**: 增加详细的操作日志，便于追踪问题
