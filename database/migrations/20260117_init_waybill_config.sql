-- --------------------------------------------------------
-- 初始化面单配置数据
-- 创建时间: 2026-01-17
-- --------------------------------------------------------

-- 中通快递面单配置
INSERT INTO `yoshop_setting` (`store_id`, `key`, `values`, `describe`, `update_time`) 
VALUES (
  10001, 
  'waybill_config_zhongtong', 
  '{
    "fields": {
      "sender_name": true,
      "sender_phone": true,
      "sender_address": true,
      "receiver_name": true,
      "receiver_phone": true,
      "receiver_address": true,
      "item_name": true,
      "weight": true,
      "volume": false,
      "remark": false,
      "quantity": true
    },
    "company_fields": {
      "site_code": "",
      "site_name": ""
    },
    "print_params": {
      "paper_size": "76x130",
      "orientation": "portrait",
      "scale": 100
    }
  }', 
  '中通快递面单配置', 
  UNIX_TIMESTAMP()
) ON DUPLICATE KEY UPDATE 
  `values` = VALUES(`values`),
  `update_time` = UNIX_TIMESTAMP();

-- 顺丰快递面单配置
INSERT INTO `yoshop_setting` (`store_id`, `key`, `values`, `describe`, `update_time`) 
VALUES (
  10001, 
  'waybill_config_shunfeng', 
  '{
    "fields": {
      "sender_name": true,
      "sender_phone": true,
      "sender_address": true,
      "receiver_name": true,
      "receiver_phone": true,
      "receiver_address": true,
      "item_name": true,
      "weight": true,
      "volume": false,
      "remark": false,
      "quantity": true
    },
    "company_fields": {
      "monthly_card": "",
      "payment_method": "1"
    },
    "print_params": {
      "paper_size": "76x130",
      "orientation": "portrait",
      "scale": 100
    }
  }', 
  '顺丰快递面单配置', 
  UNIX_TIMESTAMP()
) ON DUPLICATE KEY UPDATE 
  `values` = VALUES(`values`),
  `update_time` = UNIX_TIMESTAMP();

-- 快递API配置（示例，需要根据实际情况填写）
INSERT INTO `yoshop_setting` (`store_id`, `key`, `values`, `describe`, `update_time`) 
VALUES (
  10001, 
  'express_api_config', 
  '{
    "zhongtong": {
      "api_url": "https://api.zhongtong.com",
      "api_key": "",
      "api_secret": "",
      "company_code": "ZTO"
    },
    "shunfeng": {
      "api_url": "https://api.sf-express.com",
      "api_key": "",
      "api_secret": "",
      "company_code": "SF"
    }
  }', 
  '快递API配置', 
  UNIX_TIMESTAMP()
) ON DUPLICATE KEY UPDATE 
  `describe` = VALUES(`describe`),
  `update_time` = UNIX_TIMESTAMP();
