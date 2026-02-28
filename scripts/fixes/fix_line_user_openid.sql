-- 修复 LINE 用户的 open_id 字段
-- LINE 用户的 open_id 应该等于 line_openid

-- 1. 查看需要修复的用户
SELECT 
    user_id,
    nickName,
    line_openid,
    open_id,
    CASE 
        WHEN open_id = line_openid THEN '正确'
        ELSE '需要修复'
    END as status
FROM yoshop_user
WHERE line_openid IS NOT NULL AND line_openid != ''
ORDER BY user_id;

-- 2. 更新 LINE 用户的 open_id 字段
-- 将 open_id 设置为 line_openid
UPDATE yoshop_user
SET open_id = line_openid
WHERE line_openid IS NOT NULL 
  AND line_openid != ''
  AND open_id != line_openid;

-- 3. 验证修复结果
SELECT 
    user_id,
    nickName,
    line_openid,
    open_id,
    CASE 
        WHEN open_id = line_openid THEN '✓ 正确'
        ELSE '✗ 错误'
    END as status
FROM yoshop_user
WHERE line_openid IS NOT NULL AND line_openid != ''
ORDER BY user_id;
