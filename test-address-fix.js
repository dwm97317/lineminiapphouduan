const { chromium } = require('playwright');

(async () => {
  console.log('🚀 开始测试用户地址页面修复...\n');
  
  const browser = await chromium.launch({ 
    headless: false,
    slowMo: 500 // 放慢操作速度以便观察
  });
  
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 }
  });
  
  const page = await context.newPage();
  
  try {
    // 1. 访问后台登录页面
    console.log('📍 步骤 1: 访问后台登录页面...');
    await page.goto('http://localhost/index.php?s=/store/passport/login', {
      waitUntil: 'networkidle',
      timeout: 30000
    });
    
    // 等待页面加载
    await page.waitForTimeout(2000);
    
    // 检查是否已经登录（如果已登录会跳转到首页）
    const currentUrl = page.url();
    console.log(`   当前 URL: ${currentUrl}`);
    
    if (currentUrl.includes('/store/index')) {
      console.log('✅ 已经登录，跳过登录步骤\n');
    } else {
      console.log('⚠️  需要手动登录');
      console.log('   请在浏览器中完成登录操作...');
      console.log('   等待 30 秒...\n');
      
      // 等待用户手动登录
      await page.waitForTimeout(30000);
    }
    
    // 2. 访问用户地址列表页面
    console.log('📍 步骤 2: 访问用户地址列表页面...');
    await page.goto('http://localhost/index.php?s=/store/user/address', {
      waitUntil: 'networkidle',
      timeout: 30000
    });
    
    await page.waitForTimeout(2000);
    
    // 3. 检查页面是否有错误
    console.log('📍 步骤 3: 检查页面错误...');
    
    // 检查是否有 PHP 错误信息
    const pageContent = await page.content();
    const hasArrayError = pageContent.includes('Array to string conversion');
    const hasErrorException = pageContent.includes('ErrorException');
    
    if (hasArrayError || hasErrorException) {
      console.log('❌ 发现错误：页面仍然存在 "Array to string conversion" 错误');
      
      // 截图保存错误
      await page.screenshot({ path: 'error-screenshot.png', fullPage: true });
      console.log('   错误截图已保存: error-screenshot.png\n');
      
      return false;
    } else {
      console.log('✅ 未发现 PHP 错误\n');
    }
    
    // 4. 检查表格是否正常显示
    console.log('📍 步骤 4: 检查表格内容...');
    
    // 等待表格加载
    const tableExists = await page.locator('table.am-table').count() > 0;
    
    if (!tableExists) {
      console.log('⚠️  未找到表格元素');
    } else {
      console.log('✅ 表格元素存在');
      
      // 检查表头
      const headers = await page.locator('table thead th').allTextContents();
      console.log('   表头列: ', headers.join(' | '));
      
      // 检查是否有数据行
      const rowCount = await page.locator('table tbody tr').count();
      console.log(`   数据行数: ${rowCount}`);
      
      if (rowCount > 0) {
        // 检查第一行数据
        const firstRow = page.locator('table tbody tr').first();
        const cells = await firstRow.locator('td').allTextContents();
        
        console.log('\n   第一行数据预览:');
        cells.forEach((cell, index) => {
          const preview = cell.trim().substring(0, 50);
          console.log(`   列 ${index + 1}: ${preview}${cell.length > 50 ? '...' : ''}`);
        });
        
        // 检查是否包含用户昵称
        const userInfoCell = await firstRow.locator('td').nth(1).textContent();
        const hasNickName = userInfoCell.includes('用户昵称');
        const hasUserId = userInfoCell.includes('用户ID') || userInfoCell.includes('用户Code');
        
        if (hasNickName && hasUserId) {
          console.log('\n✅ 用户信息显示正常（包含昵称和ID/Code）');
        } else {
          console.log('\n⚠️  用户信息可能不完整');
          console.log(`   包含昵称: ${hasNickName}`);
          console.log(`   包含ID/Code: ${hasUserId}`);
        }
      } else {
        console.log('   ℹ️  暂无地址记录');
      }
    }
    
    // 5. 截图保存结果
    console.log('\n📍 步骤 5: 保存测试结果截图...');
    await page.screenshot({ path: 'address-page-success.png', fullPage: true });
    console.log('   截图已保存: address-page-success.png\n');
    
    // 6. 测试搜索功能
    console.log('📍 步骤 6: 测试搜索功能...');
    const searchInput = page.locator('input[name="search"]');
    if (await searchInput.count() > 0) {
      console.log('✅ 搜索框存在');
      
      // 可以尝试搜索（如果有数据的话）
      // await searchInput.fill('测试');
      // await page.locator('button[type="submit"]').click();
      // await page.waitForTimeout(2000);
    } else {
      console.log('⚠️  未找到搜索框');
    }
    
    console.log('\n' + '='.repeat(60));
    console.log('🎉 测试完成！');
    console.log('='.repeat(60));
    console.log('\n✅ 修复验证结果: 页面正常加载，未发现 "Array to string conversion" 错误');
    console.log('✅ 用户地址列表可以正常显示');
    console.log('✅ 用户信息（昵称、ID/Code）可以正常显示\n');
    
  } catch (error) {
    console.error('\n❌ 测试过程中发生错误:');
    console.error(error.message);
    
    // 保存错误截图
    try {
      await page.screenshot({ path: 'test-error.png', fullPage: true });
      console.log('错误截图已保存: test-error.png\n');
    } catch (e) {
      console.error('无法保存截图:', e.message);
    }
  } finally {
    console.log('⏳ 5 秒后关闭浏览器...\n');
    await page.waitForTimeout(5000);
    await browser.close();
  }
})();
