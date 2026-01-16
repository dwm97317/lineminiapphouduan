const mysql = require('mysql2/promise');

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
    console.log('\nChecking for country_id column...');
    
    if (columns.length === 0) {
      console.log('⚠️  country_id column does not exist');
      console.log('Adding country_id column...');
      
      await connection.query(`
        ALTER TABLE \`yoshop_inpack\`
        ADD COLUMN \`country_id\` int(11) NULL DEFAULT NULL COMMENT '国家ID'
        AFTER \`address_id\`
      `);
      
      console.log('✅ country_id column added successfully');
      
      const [verify] = await connection.query("SHOW COLUMNS FROM `yoshop_inpack` LIKE 'country_id'");
      console.log('\nVerification:', verify);
    } else {
      console.log('✅ country_id column already exists');
      console.log('Column details:', columns);
    }
    
    await connection.end();
    console.log('\n🎉 Database update completed!');
    process.exit(0);
  } catch (error) {
    console.error('❌ Error:', error.message);
    if (error.code) {
      console.error('Error code:', error.code);
    }
    process.exit(1);
  }
})();
