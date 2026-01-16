const mysql = require('C:/Users/weiming/AppData/Roaming/npm/node_modules/@benborla29/mcp-server-mysql/node_modules/mysql2/promise');

(async () => {
  try {
    console.log('Connecting to database...');
    const connection = await mysql.createConnection({
      host: '103.119.1.84',
      port: 3306,
      user: 'root',
      password: 'cJGzwZTDCLHzWXN4',
      database: 'xinsuju'
    });
    
    console.log('✅ Connected to database successfully');
    
    const [columns] = await connection.query("SHOW COLUMNS FROM `yoshop_inpack` LIKE 'country_id'");
    
    if (columns.length === 0) {
      console.log('⚠️  country_id column does not exist, adding it...');
      await connection.query(`
        ALTER TABLE \`yoshop_inpack\`
        ADD COLUMN \`country_id\` int(11) NULL DEFAULT NULL COMMENT '国家ID'
        AFTER \`address_id\`
      `);
      console.log('✅ country_id column added successfully');
      
      const [verify] = await connection.query("SHOW COLUMNS FROM `yoshop_inpack` LIKE 'country_id'");
      console.log('Verification:', verify);
    } else {
      console.log('✅ country_id column already exists');
      console.log('Column details:', columns[0]);
    }
    
    await connection.end();
    console.log('\n🎉 Database update completed!');
    process.exit(0);
  } catch (error) {
    console.error('❌ Error:', error.message);
    console.error('Code:', error.code);
    process.exit(1);
  }
})();
