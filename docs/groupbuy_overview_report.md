# 拼团/财务/集运 功能报告概览
Last updated: 2026-01-28 16:49

- 覆盖范围：拼团业务全链路、财务结算与提现、集运订单关联与物流
- 输出结构：功能模块、数据结构、核心流程、财务与结算、集运/物流、接口与路由、状态机与异常、风险与改进
- 证据来源：代码与规格文档，均附可点击文件链接与关键行

## 功能模块
- 拼团广场与列表
  - 集运拼团广场：按仓库/线路/热门/推荐/即刻/临近截止筛选与排序 [Logistics](file:///d:/2025profile/Lineminiapp/source/application/api/controller/sharing_origin/Logistics.php#L12-L59)
  - 集运拼团活动列表：active_type=20、状态=10、支持按截止/价格/重量排序 [Active](file:///d:/2025profile/Lineminiapp/source/application/api/model/sharing/Active.php#L94-L134)
- 拼团订单生命周期
  - 创建/管理/解散/详情与地址管理 [sharp\Order](file:///d:/2025profile/Lineminiapp/source/application/api/controller/sharp/Order.php#L35-L60) [detail](file:///d:/2025profile/Lineminiapp/source/application/api/controller/sharp/Order.php#L134-L152)
  - 后台拼团管理、发货与物流批量更新 [store apps sharing Order](file:///d:/2025profile/Lineminiapp/source/application/store/controller/apps/sharing/Order.php#L73-L102) [AddPinLog](file:///d:/2025profile/Lineminiapp/source/application/store/controller/apps/sharing/Order.php#L104-L134)
- 前端文案与页面
  - 团长中心、拼团广场、拼团详情、集运仓库、拼团规则等多语言文案 [zhHans.json](file:///d:/2025profile/Lineminiapp/web/lang/10001/zhHans.json#L955-L999) [page_pintuan](file:///d:/2025profile/Lineminiapp/web/lang/10001/zhHans.json#L1153-L1197) [page_sharing_order_detail_join](file:///d:/2025profile/Lineminiapp/web/lang/10001/zhHans.json#L1282-L1328)

## 数据结构与关键字段
- 拼团主表与项
  - 主表：sharing_tr_order，字段含状态、关联仓库/线路/地址/国家 [SharingOrder](file:///d:/2025profile/Lineminiapp/source/application/common/model/sharing/SharingOrder.php#L1-L54)
  - 订单项：sharing_tr_order_item，关联 Inpack（集运单）与主表 [SharingOrderItem(common)](file:///d:/2025profile/Lineminiapp/source/application/common/model/sharing/SharingOrderItem.php#L10-L27)
- 包裹与集运订单
  - 包裹详情与集运订单详情字段定义、状态码、来源=6（拼团预报） [PACKAGE_API_FIELDS_SPEC.md](file:///d:/2025profile/Lineminiapp/PACKAGE_API_FIELDS_SPEC.md#L167-L222)
- 状态与显示
  - 拼团订单状态文案：已付款待成团、拼团成功待发货、已发货待收货、进行中/取消/完成/失败 [Order(sharing-back)](file:///d:/2025profile/Lineminiapp/source/application/common/model/sharing-back/Order.php#L162-L177) [getOrderStatusAttr](file:///d:/2025profile/Lineminiapp/source/application/common/model/sharing-back/Order.php#L279-L288)

## 核心业务流程
- 开团与参团
  - 用户发起拼团（member_id绑定）、管理列表筛选映射、拼团项插入防重复，入团即将 Inpack.inpack_type 置为1 [create/managelist](file:///d:/2025profile/Lineminiapp/source/application/api/controller/sharp/OrderBack.php#L15-L49) [insertInpack](file:///d:/2025profile/Lineminiapp/source/application/store/model/sharing/SharingOrderItem.php#L17-L43)
- 拼团审核与移出
  - 审核通过/拒绝会更新拼团项状态；移出拼团时删除项并将 Inpack.inpack_type 置0 [verify](file:///d:/2025profile/Lineminiapp/source/application/store/controller/apps/sharing/Order.php#L224-L247) [yichu](file:///d:/2025profile/Lineminiapp/source/application/store/controller/apps/sharing/Order.php#L59-L71)
- 发货与物流
  - 发货时：拼团主单写入 inpack_id=t_order_sn，所有关联 Inpack 同步 t_order_sn 与状态=6，并推送消息与物流日志 [delivery](file:///d:/2025profile/Lineminiapp/source/application/store/controller/apps/sharing/Order.php#L73-L102) [AddPinLog](file:///d:/2025profile/Lineminiapp/source/application/store/controller/apps/sharing/Order.php#L104-L134)

## 财务与结算
- 结算与对账
  - 自动结算任务：订单完成且超出售后期限（refund_days）后，批量执行结算；拼团类型使用 OrderTypeEnum::SHARING [task behavior sharing\Order::settled](file:///d:/2025profile/Lineminiapp/source/application/task/behavior/sharing/Order.php#L170-L207)
  - 主订单自动结算（非拼团）为 MASTER 类型参照同逻辑 [task behavior Order::settled](file:///d:/2025profile/Lineminiapp/source/application/task/behavior/Order.php#L136-L173)
- 支付与资金记录
  - 集运订单支付：余额/微信/汉特等，支付成功写 BalanceLog、标记 Inpack.is_pay_type [Package支付片段](file:///d:/2025profile/Lineminiapp/source/application/api/controller/Package.php#L1745-L1780)
  - 佣金结算与提现：分销中心的结算与提现（到余额/线下），有 Capital 与 BalanceLog流水记录 [withdraw API docs](file:///d:/2025profile/Lineminiapp/docs/api/distribution_center.md#L180-L267) [DealerWithdraw](file:///d:/2025profile/Lineminiapp/source/application/store/model/dealer/Withdraw.php#L99-L190) [ShopWithdraw](file:///d:/2025profile/Lineminiapp/source/application/store/model/store/shop/Withdraw.php#L101-L194)
- 店铺分成与收益
  - 店铺查看集运单列表并计算路线分成（sr_type=1），未命中分成规则时收益为0 [api controller shop\Order](file:///d:/2025profile/Lineminiapp/source/application/api/controller/shop/Order.php#L35-L68)
  - 后台菜单含提现申请、结算记录、路线分成等管理入口 [menus](file:///d:/2025profile/Lineminiapp/source/application/store/extra/menus.php#L238-L270)

## 集运订单与拼团关系
- 关联与可视化
  - 后台拼团列表展示“共有X个集运单”，并可查看“拼单明细” [store view sharing order index](file:///d:/2025profile/Lineminiapp/source/application/store/view/apps/sharing/order/index.php#L144-L160)
  - 拼团项与包裹/集运订单明细联查，便于核对 [getItemByOrderId](file:///d:/2025profile/Lineminiapp/source/application/store/model/sharing/SharingOrderItem.php#L7-L15)
- 集运状态机（Inpack.status）
  - 待查验→待支付→待发货→拣货中→已打包→已发货→已到货→已完成→已取消→草稿 [PACKAGE_API_FIELDS_SPEC.md](file:///d:/2025profile/Lineminiapp/PACKAGE_API_FIELDS_SPEC.md#L185-L199)

## 接口与路由
- 拼团相关
  - 集运拼团广场：sharing_origin/Logistics.square [Logistics](file:///d:/2025profile/Lineminiapp/source/application/api/controller/sharing_origin/Logistics.php#L12-L59)
  - 拼团订单：lists/detail/管理/解散/发起等 [sharing_origin\Order.lists](file:///d:/2025profile/Lineminiapp/source/application/api/controller/sharing_origin/Order.php#L95-L116) [detail](file:///d:/2025profile/Lineminiapp/source/application/api/controller/sharing_origin/Order.php#L118-L137)
- 支付与结算台
  - 结算台参数设置/获取（多端复用） [api service order Checkout](file:///d:/2025profile/Lineminiapp/source/application/api/service/order/Checkout.php#L104-L122) [web service order Checkout](file:///d:/2025profile/Lineminiapp/source/application/web/service/order/Checkout.php#L104-L122)
- 包裹与物流
  - 包裹详情/集运详情/未打包列表/统计/物流轨迹等端点 [PACKAGE_API_FIELDS_SPEC.md](file:///d:/2025profile/Lineminiapp/PACKAGE_API_FIELDS_SPEC.md#L5-L14) [逻辑轨迹](file:///d:/2025profile/Lineminiapp/PACKAGE_API_FIELDS_SPEC.md#L348-L373)

## 状态机与异常场景
- 拼团订单状态
  - active_status：10=拼单中、20=拼团成功、30=拼团失败；发货与收货状态联动显示 [Order(sharing-back)](file:///d:/2025profile/Lineminiapp/source/application/common/model/sharing-back/Order.php#L162-L177) [transferDataType](file:///d:/2025profile/Lineminiapp/source/application/store/model/sharing_back/Order.php#L123-L167)
- 异常与边界
  - 未成团：可解散；已付款待成团需注意退款/转单策略
  - 审核拒绝：从拼团移出并将 Inpack.inpack_type 归零，避免重复参与 [verify/yichu](file:///d:/2025profile/Lineminiapp/source/application/store/controller/apps/sharing/Order.php#L59-L71)
  - 发货异常：t_order_sn 同步失败需回滚或重试并告警
  - 结算窗口：完成+超出售后期（refund_days）后结算，退款需要冲减佣金与流水

## 风险与改进建议
- 幂等与一致性
  - 发货批量同步 Inpack 与日志写入需幂等；失败重试与事务边界明确 [delivery/AddPinLog](file:///d:/2025profile/Lineminiapp/source/application/store/controller/apps/sharing/Order.php#L73-L134)
- 审计与对账
  - 对账口径统一：订单完成时间、售后超期窗口、退款冲减与 Capital/BalanceLog对应关系；建议增加对账报表任务
- 监控与告警
  - 监控 active_status 与发货链路；t_order_sn 写入失败、物流写入失败、支付回调异常告警
- 来源标识
  - 包裹来源=6 表示拼团预报，建议在前台与后台明显展示来源，提升排查效率 [PACKAGE_API_FIELDS_SPEC.md](file:///d:/2025profile/Lineminiapp/PACKAGE_API_FIELDS_SPEC.md#L209-L222)

## 附：实用查询与视图
- 后台视图：查看拼单明细与集运条目、发货入口与审核入口 [store view sharing order index](file:///d:/2025profile/Lineminiapp/source/application/store/view/apps/sharing_bacl/order/index.php#L269-L297)
- 语言包：拼团页、团长中心、详情与规则等文案集中 [zhHans.json page_pintuan](file:///d:/2025profile/Lineminiapp/web/lang/10001/zhHans.json#L1153-L1197)

## 引用索引
- Logistics.square: file:///D:/2025profile/Lineminiapp/source/application/api/controller/sharing_origin/Logistics.php#L20-L40
- Active.getLogisticsList: file:///D:/2025profile/Lineminiapp/source/application/api/model/sharing/Active.php#L97-L117
- sharp.Order.managelist: file:///D:/2025profile/Lineminiapp/source/application/api/controller/sharp/Order.php#L36-L56
- store.apps.sharing.Order.delivery: file:///D:/2025profile/Lineminiapp/source/application/store/controller/apps/sharing/Order.php#L74-L94
- store.apps.sharing.Order.AddPinLog: file:///D:/2025profile/Lineminiapp/source/application/store/controller/apps/sharing/Order.php#L105-L125
- SharingOrder.model: file:///D:/2025profile/Lineminiapp/source/application/common/model/sharing/SharingOrder.php#L7-L27
- Order(sharing-back).getOrderStatusAttr: file:///D:/2025profile/Lineminiapp/source/application/common/model/sharing-back/Order.php#L284-L304
- Package支付片段: file:///D:/2025profile/Lineminiapp/source/application/api/controller/Package.php#L1598-L1618
- DealerWithdraw.payToMoney: file:///D:/2025profile/Lineminiapp/source/application/store/model/dealer/Withdraw.php#L158-L178
- ShopWithdraw.payToMoney: file:///D:/2025profile/Lineminiapp/source/application/store/model/store/shop/Withdraw.php#L162-L182
- task.behavior.sharing.Order.settled: file:///D:/2025profile/Lineminiapp/source/application/task/behavior/sharing/Order.php#L178-L198
