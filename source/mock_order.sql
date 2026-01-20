-- 清理旧数据 (防止主键冲突)
DELETE FROM yoshop_user WHERE user_id IN (20001, 20002, 20003, 20004);
DELETE FROM yoshop_dealer_user WHERE user_id IN (20002, 20003, 20004);
DELETE FROM yoshop_dealer_referee WHERE user_id IN (20001, 20002, 20003);
DELETE FROM yoshop_inpack WHERE order_sn = 'TEST_ORDER_001';

-- 1. 构造用户链 (Buyer A -> B -> C -> D)
INSERT INTO yoshop_user (user_id, open_id, nickName, wxapp_id, create_time) VALUES 
(20001, 'mock_openid_A', '买家_测试', 10001, UNIX_TIMESTAMP()),
(20002, 'mock_openid_B', '一级分销商_B', 10001, UNIX_TIMESTAMP()),
(20003, 'mock_openid_C', '二级分销商_C', 10001, UNIX_TIMESTAMP()),
(20004, 'mock_openid_D', '三级分销商_D', 10001, UNIX_TIMESTAMP());

-- 2. 初始化分销商身份 (B, C, D 均为 "校园大使")
-- rating_id 10001 对应 "校园大使" (15% 佣金配置)
INSERT INTO yoshop_dealer_user (user_id, rating_id, money, total_money, wxapp_id, create_time, update_time) VALUES
(20002, 10001, 0.00, 0.00, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(20003, 10001, 0.00, 0.00, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(20004, 10001, 0.00, 0.00, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 3. 建立推荐关系 (只插入 Level 1 关系即可满足现有链式查找逻辑)
INSERT INTO yoshop_dealer_referee (user_id, dealer_id, level, wxapp_id, create_time) VALUES
(20001, 20002, 1, 10001, UNIX_TIMESTAMP()), -- A 的上级是 B
(20002, 20003, 1, 10001, UNIX_TIMESTAMP()), -- B 的上级是 C
(20003, 20004, 1, 10001, UNIX_TIMESTAMP()); -- C 的上级是 D

-- 4. 构造待结算订单
-- free = 100.00 (计算基数)
-- status = 2 (未支付/待支付)
INSERT INTO yoshop_inpack (order_sn, member_id, free, pack_free, other_free, status, is_pay, wxapp_id, created_time, updated_time) VALUES
('TEST_ORDER_001', 20001, 100.00, 0.00, 0.00, 2, 0, 10001, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
