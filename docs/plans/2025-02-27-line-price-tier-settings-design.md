# 线路价格阶梯配置功能设计文档

## 概述

为拼团系统添加可视化的线路价格阶梯配置功能，让管理员可以为不同物流线路设置不同的价格策略，并控制拼多多风格的紧迫感数据显示。

## 需求背景

当前拼团系统使用硬编码的价格阶梯，无法灵活调整。需要提供后台管理界面，让管理员可以：
1. 为不同物流线路配置独立的价格阶梯
2. 控制是否显示紧迫感数据（浏览数、最近加入等）
3. 在拼团设置和线路管理两个入口都能配置

## 设计方案

### 方案选择：混合方案

- 拼团设置页面：显示概览和快捷入口
- 独立管理页面：提供完整的价格阶梯 CRUD 功能
- 优点：既保持设置页面简洁，又提供完整的配置能力

### 数据库设计

#### 新增表：yoshop_line_price_tier

```sql
CREATE TABLE `yoshop_line_price_tier` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `line_id` int(11) NOT NULL COMMENT '线路ID',
  `min_weight` decimal(10,2) NOT NULL COMMENT '最小重量(kg)',
  `price_per_kg` decimal(10,2) NOT NULL COMMENT '每公斤价格',
  `tier_name` varchar(50) DEFAULT NULL COMMENT '阶梯名称(如:基础价、优惠价)',
  `sort` int(11) DEFAULT 100 COMMENT '排序',
  `wxapp_id` int(11) NOT NULL COMMENT '应用ID',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_line_id` (`line_id`),
  KEY `idx_wxapp_id` (`wxapp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='线路价格阶梯表';
```

#### 扩展配置：yoshop_sharing_setting

在 values JSON 字段中添加：
- `show_view_count`: 是否显示浏览数（1=显示，0=不显示）
- `show_recent_joins`: 是否显示最近加入（1=显示，0=不显示）
- `show_urgency_timer`: 是否显示紧迫倒计时（1=显示，0=不显示）

### 后端架构

#### 新增文件

1. **控制器**：`source/application/store/controller/apps/sharing/LinePriceTier.php`
   - `index()` - 线路价格管理页面
   - `getTiers()` - AJAX 获取指定线路的价格阶梯
   - `saveTiers()` - AJAX 保存价格阶梯
   - `deleteTier()` - AJAX 删除单个阶梯

2. **模型**：`source/application/store/model/sharing/LinePriceTier.php`
   - 基础 CRUD 操作
   - `getByLineId($lineId)` - 获取指定线路的所有阶梯
   - `saveBatch($lineId, $tiers)` - 批量保存阶梯

3. **通用模型**：`source/application/common/model/sharing/LinePriceTier.php`
   - 基础模型定义

#### 修改文件

1. **控制器**：`source/application/store/controller/apps/sharing/Setting.php`
   - 添加线路价格配置概览数据
   - 添加紧迫感开关的保存逻辑

2. **API 控制器**：`source/application/api/controller/sharing_origin/Logistics.php`
   - `square()` - 集成价格阶梯查询和紧迫感开关
   - `detail()` - 集成价格阶梯查询和紧迫感开关

### 前端界面

#### 修改：拼团设置页面（basic.php）

添加两个新区域：

**紧迫感显示设置**
```html
<div class="am-form-group">
    <label class="am-u-sm-3 am-u-lg-2 am-form-label">紧迫感显示设置</label>
    <div class="am-u-sm-9 am-u-end">
        <label class="am-checkbox-inline">
            <input type="checkbox" name="share[show_view_count]" value="1"> 显示浏览人数
        </label>
        <label class="am-checkbox-inline">
            <input type="checkbox" name="share[show_recent_joins]" value="1"> 显示最近加入提示
        </label>
        <label class="am-checkbox-inline">
            <input type="checkbox" name="share[show_urgency_timer]" value="1"> 显示紧迫倒计时
        </label>
        <div class="help-block">
            <small>开启后将在拼团广场显示相关数据，增强用户参与紧迫感</small>
        </div>
    </div>
</div>
```

**线路价格配置概览**
```html
<div class="am-form-group">
    <label class="am-u-sm-3 am-u-lg-2 am-form-label">线路价格配置</label>
    <div class="am-u-sm-9 am-u-end">
        <div class="am-alert am-alert-secondary">
            <p>已配置线路：<strong id="configured-lines-count">0</strong> 条</p>
            <p>未配置线路：<strong id="unconfigured-lines-count">0</strong> 条</p>
        </div>
        <a href="<?= url('apps.sharing.line_price_tier/index') ?>" class="am-btn am-btn-secondary">
            <i class="am-icon-cog"></i> 详细配置
        </a>
    </div>
</div>
```

#### 新增：线路价格管理页面（line_price/index.php）

表格式界面，支持：
- 下拉选择线路
- 动态添加/删除价格阶梯行
- 输入：最小重量、单价、阶梯名称
- AJAX 保存和删除

### 业务逻辑

#### 价格计算流程

```
1. 获取拼团的 line_id
2. 查询 yoshop_line_price_tier 表（按 min_weight 升序）
3. 遍历阶梯，找到 current_weight >= min_weight 的最大阶梯
4. 返回该阶梯的 price_per_kg 作为当前价格
5. 返回所有阶梯供前端显示进度
```

#### 紧迫感数据控制

```
1. 从 yoshop_sharing_setting 读取开关配置
2. 如果 show_view_count = 1，返回真实浏览统计
3. 如果 show_recent_joins = 1，返回最近10分钟加入人数
4. 如果 show_urgency_timer = 1，返回倒计时数据
5. 如果关闭，不返回对应字段
```

### API 修改

#### /sharing_origin.logistics/square

添加逻辑：
```php
// 获取价格阶梯配置
$priceTiers = LinePriceTier::where('line_id', $item['line_id'])
    ->where('wxapp_id', $this->getWxappId())
    ->order('min_weight', 'asc')
    ->select();

// 计算当前价格
$currentPrice = $this->calculatePrice($current_weight, $priceTiers);

// 检查紧迫感开关
$setting = Setting::getItem('sharp', $this->getWxappId());
if ($setting['show_view_count']) {
    $item['view_count'] = $this->getViewCount($item['order_id']);
}
```

#### /sharing_origin.logistics/detail

同样的逻辑应用到详情接口。

### 实施步骤

1. ✅ 设计方案评审通过
2. ⏳ 使用 MCP 创建数据库表
3. ⏳ 开发后端模型和控制器
4. ⏳ 开发前端管理界面
5. ⏳ 修改 API 接口
6. ⏳ 测试功能
7. ⏳ 部署上线

### 测试要点

1. 价格阶梯配置的增删改查
2. 不同线路的价格独立性
3. 价格计算的准确性
4. 紧迫感开关的生效
5. 前端显示的正确性

### 风险和注意事项

1. **数据迁移**：现有拼团如果没有配置价格阶梯，需要提供默认值
2. **性能**：价格查询需要优化，考虑缓存
3. **兼容性**：API 修改需要保持向后兼容
4. **权限**：确保只有管理员可以配置价格

---

**文档创建时间**：2025-02-27  
**设计者**：Kiro AI  
**状态**：已批准，待实施
