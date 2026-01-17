<?php
/**
 * 推荐奖励系统 - 阶段2文件验证脚本
 * 验证所有必需的文件是否已创建
 */

echo "=== 推荐奖励系统 - 阶段2文件验证 ===\n\n";

$baseDir = __DIR__ . '/source/application';

// 定义需要检查的文件
$files = [
    // Library
    'Library' => [
        'common/library/referral/ReferralCodeGenerator.php',
    ],
    
    // Models
    'Models' => [
        'common/model/UserReferralCode.php',
        'common/model/ReferralRelation.php',
        'common/model/ReferralReward.php',
        'common/model/ReferralTaskConfig.php',
        'common/model/ReferralRewardConfig.php',
        'common/model/ReferralSystemConfig.php',
        'common/model/ReferralLeaderboard.php',
    ],
    
    // Services
    'Services' => [
        'common/service/referral/ReferralService.php',
        'common/service/referral/TaskVerificationService.php',
        'common/service/referral/RewardService.php',
        'common/service/referral/ExpirationService.php',
        'common/service/referral/LeaderboardService.php',
    ],
];

$totalFiles = 0;
$existingFiles = 0;
$missingFiles = [];

foreach ($files as $category => $fileList) {
    echo "【{$category}】\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($fileList as $file) {
        $totalFiles++;
        $fullPath = $baseDir . '/' . $file;
        
        if (file_exists($fullPath)) {
            $existingFiles++;
            $fileSize = filesize($fullPath);
            $fileSizeKB = round($fileSize / 1024, 2);
            echo "✓ {$file} ({$fileSizeKB} KB)\n";
        } else {
            echo "✗ {$file} (不存在)\n";
            $missingFiles[] = $file;
        }
    }
    
    echo "\n";
}

// 统计信息
echo "=== 统计信息 ===\n";
echo str_repeat("-", 50) . "\n";
echo "总文件数: {$totalFiles}\n";
echo "已创建: {$existingFiles}\n";
echo "缺失: " . count($missingFiles) . "\n";
echo "完成度: " . round(($existingFiles / $totalFiles) * 100, 2) . "%\n\n";

if (count($missingFiles) > 0) {
    echo "缺失的文件:\n";
    foreach ($missingFiles as $file) {
        echo "  - {$file}\n";
    }
    echo "\n";
}

// 检查类定义
echo "=== 类定义检查 ===\n";
echo str_repeat("-", 50) . "\n";

$classChecks = [
    'ReferralCodeGenerator' => $baseDir . '/common/library/referral/ReferralCodeGenerator.php',
    'UserReferralCode' => $baseDir . '/common/model/UserReferralCode.php',
    'ReferralRelation' => $baseDir . '/common/model/ReferralRelation.php',
    'ReferralReward' => $baseDir . '/common/model/ReferralReward.php',
    'ReferralService' => $baseDir . '/common/service/referral/ReferralService.php',
    'TaskVerificationService' => $baseDir . '/common/service/referral/TaskVerificationService.php',
    'RewardService' => $baseDir . '/common/service/referral/RewardService.php',
];

foreach ($classChecks as $className => $filePath) {
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        if (strpos($content, "class {$className}") !== false) {
            echo "✓ {$className} 类定义存在\n";
        } else {
            echo "✗ {$className} 类定义未找到\n";
        }
    } else {
        echo "✗ {$className} 文件不存在\n";
    }
}

echo "\n=== 阶段2文件验证完成 ===\n";

if ($existingFiles == $totalFiles) {
    echo "\n✅ 所有文件已成功创建！\n";
    echo "\n下一步:\n";
    echo "1. 确保阶段1的数据库表已创建\n";
    echo "2. 开始阶段3: 后端API开发\n";
} else {
    echo "\n⚠️ 还有 " . count($missingFiles) . " 个文件缺失\n";
}
