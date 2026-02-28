-- 清理重复的 LINE 用户
-- 只保留每个 line_openid 最早创建的用户记录

-- 1. 首先查看重复的 LINE 用户
SELECT 
    line_openid,
    COUNT(*) as count,
    MIN(user_id) as first_user_id,
    MIN(create_time) as first_create_time
FROM yoshop_user
WHERE line_openid IS NOT NULL AND line_openid != ''
GROUP BY line_openid
HAVING COUNT(*) > 1;

-- 2. 删除重复的用户（保留最早的）
-- 注意：执行前请先备份数据库！
-- DELETE FROM yoshop_user
-- WHERE user_id IN (
--     SELECT user_id FROM (
--         SELECT u1.user_id
--         FROM yoshop_user u1
--         INNER JOIN (
--             SELECT line_openid, MIN(user_id) as min_user_id
--             FROM yoshop_user
--             WHERE line_openid IS NOT NULL AND line_openid != ''
--             GROUP BY line_openid
--             HAVING COUNT(*) > 1
--         ) u2 ON u1.line_openid = u2.line_openid
--         WHERE u1.user_id > u2.min_user_id
--     ) AS duplicate_users
-- );

-- 3. 查看特定 LINE 用户的所有记录
SELECT 
    user_id,
    nickName,
    line_openid,
    open_id,
    mobile,
    create_time,
    last_login_time
FROM yoshop_user
WHERE line_openid = 'Ud4e37d68c438cc70350957039add98d8'
ORDER BY create_time ASC;

-- 4. 如果要手动删除特定的重复记录（保留 user_id 最小的）
-- 请先确认要保留的 user_id，然后删除其他的
-- 例如：保留 user_id = 15027，删除其他的
-- DELETE FROM yoshop_user 
-- WHERE line_openid = 'Ud4e37d68c438cc70350957039add98d8' 
-- AND user_id != 15027;
