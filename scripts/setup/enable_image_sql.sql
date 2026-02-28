-- 启用入库通知的图片发送功能
-- 需要手动执行此SQL，或通过后台界面配置

-- 查看当前配置
SELECT `key`, `values` FROM yoshop_setting WHERE `key` = 'line_messaging' AND wxapp_id = 10001;

-- 说明：
-- 需要在 values JSON 中的 templates.inwarehouse 添加：
-- "send_images": "1",
-- "max_images": 3

-- 或者直接在后台配置页面：
-- http://localhost:8080/index.php?s=/store/setting.line_config/index
-- 找到"📦 包裹入库通知"
-- 勾选"启用图片发送"
-- 选择"最大图片数量": 3张
-- 点击"提交保存"
