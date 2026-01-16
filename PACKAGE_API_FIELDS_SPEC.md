# 包裹详情 API 字段说明

> 本文档供前端开发参考，详细说明后端包裹相关API返回的字段结构

## 一、API 端点概览

| API | 方法 | 说明 |
|-----|------|-----|
| `/api/package/details` | POST | 单个包裹详情 |
| `/api/package/details_pack` | POST | 集运订单详情 |
| `/api/package/unpack` | GET | 未打包包裹列表 |
| `/api/package/logicist` | POST | 物流轨迹查询 |
| `/api/package/packTotal` | GET | 包裹统计 |

---

## 二、包裹详情 API (`/api/package/details`)

### 请求参数

```json
{
  "id": 12345,
  "method": ["edit"]
}
```

### 响应字段

```typescript
interface PackageDetail {
  // ========== 基础信息 ==========
  id: number;                    // 包裹ID (主键)
  order_sn: string;              // 订单号
  express_num: string;           // 快递单号
  express_name: string;          // 快递公司名称
  express_id: number;            // 快递公司ID
  usermark: string;              // 唛头 (用户标识码)
  
  // ========== 状态信息 ==========
  status: number;                // 包裹状态 (见状态码表)
  is_take: number;               // 认领状态: 1=待认领, 2=已认领, 3=已丢弃, 4=退件
  is_pay: number;                // 支付状态: 0=未支付, 1=已支付
  is_verify: number;             // 查验状态: 1=已查验, 2=待查验
  is_scan: number;               // 扫码出库: 1=未扫码, 2=已扫码
  
  // ========== 仓库/线路 ==========
  storage_id: number;            // 仓库ID
  country_id: number;            // 目的国家ID
  line_id: number;               // 线路ID
  address_id: number;            // 收货地址ID
  
  // ========== 费用信息 ==========
  price: number;                 // 申报价值
  free: number;                  // 运费
  pack_free: number;             // 打包费
  real_payment: number;          // 实际支付金额
  free_total: number;            // 总费用 (free + pack_free)
  
  // ========== 尺寸重量 ==========
  weight: number;                // 重量 (kg)
  length: number;                // 长 (cm)
  width: number;                 // 宽 (cm)
  height: number;                // 高 (cm)
  volume: number;                // 体积 (cm³)
  volumeweight: number;          // 体积重
  
  // ========== 备注信息 ==========
  remark: string;                // 用户备注
  admin_remark: string;          // 仓库备注
  
  // ========== 关联数据 ==========
  shop: string;                  // 物品分类名称 (逗号分隔)
  shop_ids: string;              // 物品分类ID (逗号分隔)
  address?: UserAddress;         // 收货地址对象
  line?: LineInfo;               // 线路信息对象
  country?: CountryInfo;         // 国家信息对象
  storage?: StorageInfo;         // 仓库信息对象
  packageimage?: PackageImage[]; // 包裹图片数组
}
```


---

## 三、集运订单详情 API (`/api/package/details_pack`)

### 请求参数

```json
{
  "id": 12345,
  "method": ["edit"],
  "coupon_id": 100  // 可选，优惠券ID
}
```

### 响应字段

```typescript
interface InpackDetail {
  // ========== 基础信息 ==========
  id: number;                    // 集运订单ID
  order_sn: string;              // 集运订单号
  pack_ids: string;              // 包含的包裹ID (逗号分隔)
  t_order_sn: string;            // 国际运单号
  
  // ========== 状态信息 ==========
  status: number;                // 订单状态 (见状态码表)
  is_pay: number;                // 支付状态: 1=已支付, 2=未支付
  is_pay_type: number;           // 支付方式: 0=后台, 1=微信, 2=余额, 3=汉特, 4=OMIPAY, 5=现金
  pay_type: number;              // 付款类型: 0=立即发货, 1=货到付款, 2=月结
  
  // ========== 仓库/线路 ==========
  storage_id: number;            // 发货仓库ID
  address_id: number;            // 收货地址ID
  line_id: number;               // 线路ID
  country_id: number;            // 目的国家ID
  
  // ========== 费用信息 ==========
  free: number;                  // 基础运费
  pack_free: number;             // 打包服务费
  other_free: number;            // 其他费用 (海关等)
  free_total: number;            // 总费用 (free + pack_free + other_free)
  user_coupon_id: number;        // 使用的优惠券ID
  user_coupon_money: number;     // 优惠券抵扣金额
  fyouhui_total: number;         // 优惠后总价
  
  // ========== 尺寸重量 ==========
  weight: number;                // 实际重量 (kg)
  cale_weight: number;           // 计费重量 (kg)
  volume: number;                // 体积重 (kg)
  length: number;                // 长 (cm)
  width: number;                 // 宽 (cm)
  height: number;                // 高 (cm)
  
  // ========== 备注信息 ==========
  remark: string;                // 备注
  
  // ========== 关联数据 ==========
  address?: UserAddress;         // 收货地址对象
  line?: LineInfo;               // 线路信息对象
  image?: ImageInfo;             // 线路图片
  item: PackageItem[];           // 包含的包裹列表
}

// 包裹列表项
interface PackageItem {
  id: number;                    // 包裹ID
  express_num: string;           // 快递单号
  express_name: string;          // 快递公司
  price: number;                 // 申报价值
  weight: number;                // 重量
  height: number;                // 高
  length: number;                // 长
  width: number;                 // 宽
  entering_warehouse_time: string; // 入库时间
  remark: string;                // 备注
  class_name: string;            // 物品分类名称
  packageimage: PackageImage[];  // 包裹图片
}
```


---

## 四、状态码定义

### 4.1 包裹状态 (Package.status)

| 值 | 状态 | 说明 |
|---|------|-----|
| 1 | 待入库 | 用户已预报，等待仓库收货 |
| 2 | 已入库 | 仓库已收到包裹 |
| 3 | 已上架 | 包裹已上架，准备打包 |
| 4 | 待打包 | 等待打包处理 |
| 5 | 待支付 | 打包完成，等待支付 |
| 6 | 已支付 | 用户已支付 |
| 7 | 加入批次 | 已扫码入批次 |
| 8 | 已打包 | 打包完成 |
| 9 | 已发货 | 已发出 |
| 10 | 已收货 | 用户已签收 |
| 11 | 已完成 | 订单完成 |

### 4.2 集运订单状态 (Inpack.status)

| 值 | 状态 | 说明 |
|---|------|-----|
| 1 | 待查验 | 等待仓库查验 |
| 2 | 待支付 | 查验完成，等待支付 |
| 3 | 待发货 | 已支付，等待发货 |
| 4 | 拣货中 | 正在拣货 |
| 5 | 已打包 | 打包完成 |
| 6 | 已发货 | 已发出国际物流 |
| 7 | 已到货 | 到达目的地 |
| 8 | 已完成 | 用户签收完成 |
| 9 | 已取消 | 订单取消 |
| 10 | 草稿 | 草稿状态 |

### 4.3 认领状态 (Package.is_take)

| 值 | 状态 |
|---|------|
| 1 | 待认领 |
| 2 | 已认领 |
| 3 | 已丢弃 |
| 4 | 退件 |

### 4.4 包裹来源 (Package.source)

| 值 | 来源 |
|---|------|
| 1 | 小程序录入 |
| 2 | 平台录入 |
| 3 | 代购同步 |
| 4 | 批量导入 |
| 5 | PC端预报 |
| 6 | 拼团预报 |
| 7 | 预约取件 |
| 8 | 仓管录入 |
| 9 | API录入 |


---

## 五、关联对象结构

### 5.1 收货地址 (UserAddress)

```typescript
interface UserAddress {
  address_id: number;            // 地址ID
  name: string;                  // 收件人姓名
  phone: string;                 // 手机号
  tel_code: string;              // 区号
  province_id: number;           // 省份ID
  city_id: number;               // 城市ID
  region_id: number;             // 区域ID
  country_id: number;            // 国家ID
  detail: string;                // 详细地址
  street: string;                // 街道
  door: string;                  // 门牌号
  code: string;                  // 邮编
  email: string;                 // 邮箱
  clearancecode: string;         // 清关码
  identitycard: string;          // 身份证号
}
```

### 5.2 线路信息 (LineInfo)

```typescript
interface LineInfo {
  id: number;                    // 线路ID
  name: string;                  // 线路名称
  limitationofdelivery: string;  // 时效说明
  image_id: number;              // 图片ID
  image?: ImageInfo;             // 图片对象
}
```

### 5.3 仓库信息 (StorageInfo)

```typescript
interface StorageInfo {
  shop_id: number;               // 仓库ID
  shop_name: string;             // 仓库名称
  province_id: number;           // 省份ID
  city_id: number;               // 城市ID
  region_id: number;             // 区域ID
}
```

### 5.4 国家信息 (CountryInfo)

```typescript
interface CountryInfo {
  id: number;                    // 国家ID
  title: string;                 // 国家名称
  title_en: string;              // 英文名称
  code: string;                  // 国家代码
}
```

### 5.5 包裹图片 (PackageImage)

```typescript
interface PackageImage {
  id: number;                    // 图片记录ID
  package_id: number;            // 包裹ID
  image_id: number;              // 文件ID
  file: FileInfo;                // 文件详情
}

interface FileInfo {
  file_id: number;               // 文件ID
  file_name: string;             // 文件名
  file_path: string;             // 文件路径
  file_url: string;              // 完整URL
}
```


---

## 六、包裹列表 API

### 6.1 未打包列表 (`/api/package/unpack`)

**请求**: GET (需要token)

**响应字段**:

```typescript
interface UnpackListItem {
  id: number;                    // 包裹ID
  order_sn: string;              // 订单号
  express_num: string;           // 快递单号
  weight: number;                // 重量
  storage_id: number;            // 仓库ID
  country_id: number;            // 国家ID
  created_time: string;          // 创建时间
  remark: string;                // 备注
  source: number;                // 来源
  country?: CountryInfo;         // 国家信息
  storage?: StorageInfo;         // 仓库信息
}
```

### 6.2 包裹统计 (`/api/package/packTotal`)

**请求**: GET (需要token)

**响应**:

```typescript
interface PackTotal {
  no_pay: number;                // 待支付数量 (status=2)
  verify: number;                // 待查验数量 (status=1)
  no_send: number;               // 待发货数量 (status=3,4,5)
  send: number;                  // 已发货数量 (status=6,7)
  complete: number;              // 已完成数量 (status=8)
}
```

---

## 七、物流轨迹 API (`/api/package/logicist`)

### 请求参数

```json
{
  "code": ["SF1234567890"]  // 快递单号/国际单号/订单号
}
```

### 响应字段

```typescript
interface LogisticsResponse {
  logic: LogisticsItem[];        // 物流轨迹列表
}

interface LogisticsItem {
  id: number;                    // 记录ID
  order_sn: string;              // 关联订单号
  express_num: string;           // 快递单号
  content: string;               // 物流内容
  created_time: string;          // 时间
  location: string;              // 位置
}
```


---

## 八、数据库完整字段参考

### 8.1 yoshop_package 表 (包裹表)

| 字段名 | 类型 | 说明 |
|-------|------|-----|
| id | int(11) | 主键ID |
| inpack_id | int(11) | 所属集运订单ID |
| batch_id | int(11) | 批次ID |
| order_sn | varchar(255) | 订单号 |
| member_id | int(11) | 用户ID |
| member_name | varchar(255) | 用户姓名 |
| express_id | int(11) | 快递公司ID |
| express_name | varchar(255) | 快递公司名称 |
| express_num | varchar(255) | 快递单号 |
| origin_express_num | varchar(255) | 原始单号 |
| usermark | varchar(30) | 唛头 |
| status | tinyint(3) | 状态 |
| storage_id | int(11) | 仓库ID |
| price | decimal(10,2) | 申报价值 |
| admin_remark | varchar(255) | 仓库备注 |
| remark | varchar(255) | 用户备注 |
| country_id | int(11) | 国家ID |
| real_payment | decimal(10,2) | 实付金额 |
| line_id | int(11) | 线路ID |
| line_name | varchar(255) | 线路名称 |
| wxapp_id | int(11) | 商家ID |
| pay_type | tinyint(3) | 支付方式 |
| free | varchar(255) | 运费 |
| volumeweight | double(12,4) | 体积重 |
| weight | double(12,3) | 重量 |
| length | double | 长 |
| width | double | 宽 |
| height | double | 高 |
| volume | float(10,4) | 体积 |
| num | int(11) | 数量 |
| address_id | int(11) | 地址ID |
| visit_free | decimal(11,2) | 上门服务费 |
| pack_free | decimal(10,2) | 打包费 |
| pack_service | varchar(255) | 包装服务ID |
| pack_attr | varchar(255) | 包裹属性 |
| goods_attr | varchar(255) | 物品属性 |
| source | tinyint(255) | 来源 |
| is_take | tinyint(3) | 认领状态 |
| is_pay | tinyint(3) | 支付状态 |
| is_delete | tinyint(255) | 删除标记 |
| is_verify | tinyint(3) | 查验状态 |
| is_scan | tinyint(3) | 扫码出库状态 |
| pay_time | datetime | 支付时间 |
| entering_warehouse_time | datetime | 入库时间 |
| updated_time | datetime | 更新时间 |
| created_time | datetime | 创建时间 |

### 8.2 yoshop_inpack 表 (集运订单表)

| 字段名 | 类型 | 说明 |
|-------|------|-----|
| id | int(11) | 主键ID |
| order_sn | varchar(255) | 集运订单号 |
| batch_id | int(11) | 批次ID |
| remark | varchar(255) | 备注 |
| usermark | varchar(30) | 唛头 |
| pack_ids | text | 包裹ID列表 |
| pack_services_id | varchar(50) | 包装服务ID |
| waitreceivedmoney | decimal(10,2) | 待收款 |
| address_id | int(11) | 收货地址ID |
| jaddress_id | int(11) | 寄件地址ID |
| status | tinyint(3) | 状态 |
| member_id | int(11) | 用户ID |
| storage_id | int(11) | 发货仓库ID |
| shop_id | int(11) | 收货仓库ID |
| wxapp_id | int(11) | 商家ID |
| line_id | int(11) | 线路ID |
| real_payment | decimal(10,2) | 实付金额 |
| total_free | decimal(10,2) | 总费用 |
| free | decimal(10,2) | 基础运费 |
| pack_free | decimal(10,2) | 服务费 |
| other_free | decimal(10,2) | 其他费用 |
| user_coupon_money | decimal(11,2) | 优惠券金额 |
| discount_price | decimal(10,2) | 折扣价 |
| weight | double(10,2) | 实际重量 |
| cale_weight | double(10,2) | 计费重量 |
| volume | double(10,2) | 体积重 |
| country | varchar(255) | 目标国家 |
| length | double | 长 |
| width | double | 宽 |
| height | double | 高 |
| is_pay | tinyint(3) | 支付状态 |
| is_pay_type | tinyint(3) | 支付方式 |
| t_name | varchar(255) | 承运商名称 |
| t_number | varchar(50) | 物流商编号 |
| t_order_sn | varchar(255) | 国际运单号 |
| source | tinyint(3) | 来源 |
| discount | decimal(10,2) | 折扣 |
| user_coupon_id | int(11) | 优惠券ID |
| logistics | varchar(255) | 物流信息 |
| is_delete | tinyint(3) | 删除标记 |
| is_exceed | tinyint(3) | 超时标记 |
| is_settled | tinyint(3) | 结算状态 |
| pay_type | tinyint(3) | 付款类型 |
| inpack_type | tinyint(3) | 订单类型 |
| unpack_time | datetime | 提交打包时间 |
| pick_time | datetime | 拣货完成时间 |
| pay_time | datetime | 支付时间 |
| receipt_time | datetime | 签收时间 |
| shoprk_time | datetime | 到货入库时间 |
| settle_time | datetime | 结算时间 |
| exceed_date | int(11) | 超时时间 |
| sendout_time | datetime | 发货时间 |
| created_time | datetime | 创建时间 |
| updated_time | datetime | 更新时间 |
| pay_order | varchar(255) | 支付订单号 |
| t2_name | varchar(255) | 转单承运商 |
| t2_number | varchar(50) | 转单物流商编号 |
| t2_order_sn | varchar(255) | 转单号 |
| transfer | int(11) | 物流类型: 0=自有, 1=17track |
| take_code | varchar(30) | 取货码 |
| is_focus_image | tinyint(3) | 图片上传状态 |
| third | text | 第三方订单信息 |


### 8.3 yoshop_package_item 表 (包裹物品表)

| 字段名 | 类型 | 说明 |
|-------|------|-----|
| id | int(11) | 主键ID |
| order_id | int(11) | 包裹ID |
| express_num | varchar(255) | 快递单号 |
| express_code | varchar(255) | 快递公司代码 |
| express_name | varchar(25) | 快递公司名称 |
| class_name | varchar(255) | 分类名称 |
| class_id | int(11) | 分类ID |
| class_name_en | varchar(50) | 英文品名 |
| distribution | varchar(50) | 配货信息 |
| product_num | varchar(10) | 商品数量 |
| all_price | decimal(10,2) | 商品总价 |
| customs_code | varchar(30) | 海关编码 |
| length | double(10,2) | 长度 |
| width | double(10,2) | 宽度 |
| height | double(10,2) | 高度 |
| all_weight | double(10,2) | 总重量 |
| unit_weight | float(10,2) | 单件重量 |
| volumeweight | double(10,4) | 体积重 |
| volume | double(10,4) | 体积 |
| goods_name | varchar(255) | 货物名称 |
| one_price | decimal(10,2) | 单价 |
| wxapp_id | int(11) | 商家ID |

---

## 九、前端开发示例

### 9.1 获取包裹详情

```javascript
// API 调用示例
async function getPackageDetail(id) {
  const response = await fetch('/api/package/details', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'token': userToken
    },
    body: JSON.stringify({
      id: id,
      method: ['edit']
    })
  });
  
  const result = await response.json();
  if (result.code === 1) {
    return result.data;
  }
  throw new Error(result.msg);
}
```

### 9.2 获取集运订单详情

```javascript
async function getInpackDetail(id, couponId = null) {
  const body = {
    id: id,
    method: ['edit']
  };
  
  if (couponId) {
    body.coupon_id = couponId;
  }
  
  const response = await fetch('/api/package/details_pack', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'token': userToken
    },
    body: JSON.stringify(body)
  });
  
  const result = await response.json();
  if (result.code === 1) {
    return result.data;
  }
  throw new Error(result.msg);
}
```

### 9.3 状态显示映射

```javascript
// 包裹状态映射
const PACKAGE_STATUS = {
  1: { text: '待入库', color: '#999' },
  2: { text: '已入库', color: '#1890ff' },
  3: { text: '已上架', color: '#1890ff' },
  4: { text: '待打包', color: '#faad14' },
  5: { text: '待支付', color: '#ff4d4f' },
  6: { text: '已支付', color: '#52c41a' },
  7: { text: '加入批次', color: '#722ed1' },
  8: { text: '已打包', color: '#52c41a' },
  9: { text: '已发货', color: '#13c2c2' },
  10: { text: '已收货', color: '#52c41a' },
  11: { text: '已完成', color: '#52c41a' }
};

// 集运订单状态映射
const INPACK_STATUS = {
  1: { text: '待查验', color: '#faad14' },
  2: { text: '待支付', color: '#ff4d4f' },
  3: { text: '待发货', color: '#1890ff' },
  4: { text: '拣货中', color: '#722ed1' },
  5: { text: '已打包', color: '#52c41a' },
  6: { text: '已发货', color: '#13c2c2' },
  7: { text: '已到货', color: '#52c41a' },
  8: { text: '已完成', color: '#52c41a' },
  9: { text: '已取消', color: '#999' },
  10: { text: '草稿', color: '#999' }
};
```

---

## 十、注意事项

1. **时间格式**: 所有时间字段返回格式为 `YYYY-MM-DD HH:mm:ss`
2. **金额精度**: 金额字段保留2位小数
3. **重量单位**: 默认单位为 kg，可通过系统设置调整
4. **尺寸单位**: 默认单位为 cm
5. **图片URL**: `packageimage.file.file_url` 为完整可访问URL
6. **分页**: 列表接口默认分页15条/页，最大300条

---

*文档版本: 1.0*
*创建日期: 2026-01-14*
*适用于: LINE Mini App 集运系统前端开发*
