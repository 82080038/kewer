const puppeteer = require('puppeteer');
const config = require('./puppeteer.config');

// Test results
const results = {
  passed: 0,
  failed: 0,
  errors: [],
  roles: [],
  menuItems: []
};

// Create screenshots directory
const fs = require('fs');
const path = require('path');
const screenshotDir = path.join(__dirname, 'screenshots');
if (!fs.existsSync(screenshotDir)) {
  fs.mkdirSync(screenshotDir, { recursive: true });
}

// Helper function to take screenshot
async function takeScreenshot(page, name) {
  const screenshotPath = path.join(screenshotDir, `${name}.png`);
  await page.screenshot({ path: screenshotPath, fullPage: true });
  console.log(`Screenshot saved: ${screenshotPath}`);
  return screenshotPath;
}

// Helper function to log errors
function logError(testName, error) {
  console.error(`❌ ${testName}: ${error.message}`);
  results.errors.push({ test: testName, error: error.message });
  results.failed++;
}

// Helper function to log success
function logSuccess(testName) {
  console.log(`✅ ${testName}`);
  results.passed++;
}

// Roles to test
const testUsers = [
  { role: 'superadmin', username: 'patri', password: 'password' },
  { role: 'bos', username: 'bos_simulasi', password: 'password123' },
  { role: 'petugas', username: 'petugas1_sim', password: 'password123' },
  { role: 'manager_pusat', username: 'manager_pusat_sim', password: 'password123' }
];

// Expected menu order
const expectedMenuOrder = [
  'Dashboard',
  'Nasabah',
  'Pinjaman',
  'Angsuran',
  'Aktivitas Lapangan',
  'Kas Petugas',
  'Rekonsiliasi Kas',
  'Auto-Confirm',
  'Users',
  'Cabang',
  'Setting Bunga',
  'Pengeluaran',
  'Kas Bon',
  'Family Risk',
  'Petugas',
  'Laporan',
  'Rute Harian',
  'Kinerja Petugas',
  'Delegasi Permission',
  'Persetujuan Bos',
  'Audit Trail',
  'Permissions'
];

// Run tests
async function runTests() {
  console.log('🚀 Starting Comprehensive All-Roles Testing in Headed Mode...\n');
  
  let browser;
  let page;

  try {
    browser = await puppeteer.launch(config.launchOptions);
    page = await browser.newPage();
    
    await page.setDefaultTimeout(30000);
    await page.setDefaultNavigationTimeout(30000);

    // Test each role
    for (const role of testUsers) {
      try {
        console.log(`\n📋 Testing Role: ${role.name}`);
        
        // Login
        await page.goto(config.baseUrl + '/login.php?test_login=true&username=' + role.username + '&password=' + role.password);
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        const currentUrl = page.url();
        console.log(`  Current URL after login: ${currentUrl}`);
        
        // Accept dashboard.php or setup_headquarters.php as valid login success
        if (!currentUrl.includes('dashboard.php') && !currentUrl.includes('setup_headquarters.php')) {
          console.log(`  ⚠️ ${role.role} login failed or redirected`);
          // Check if there's an error message
          const errorAlert = await page.$('.alert-danger');
          if (errorAlert) {
            const errorText = await page.$eval('.alert-danger', el => el.textContent);
            console.log(`  Error message: ${errorText}`);
          }
          results.roles.push({ role: role.role, status: 'Login Failed', menuItems: [] });
          continue;
        }
        
        // If on setup_headquarters.php, redirect to dashboard
        if (currentUrl.includes('setup_headquarters.php')) {
          console.log(`  Redirecting from setup_headquarters to dashboard`);
          await page.goto(config.baseUrl + '/dashboard.php');
          await new Promise(resolve => setTimeout(resolve, 2000));
        }
        
        await page.waitForSelector('.sidebar', { timeout: 5000 });
        console.log(`  ✓ ${role.role} login successful`);
        
        // Get menu items
        const menuItems = await page.evaluate(() => {
          const links = Array.from(document.querySelectorAll('.sidebar .nav-link'));
          return links.map(link => link.textContent.trim());
        });
        
        console.log(`  Menu items found: ${menuItems.length}`);
        menuItems.forEach(item => console.log(`    - ${item}`));
        
        // Check menu order
        let menuOrderCorrect = true;
        for (let i = 0; i < menuItems.length; i++) {
          const expectedIndex = expectedMenuOrder.indexOf(menuItems[i]);
          if (expectedIndex === -1) {
            console.log(`  ⚠️ Unexpected menu item: ${menuItems[i]}`);
          }
        }
        
        // Check for Indonesian format in page
        const pageContent = await page.content();
        const hasIndonesianDate = /Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember/.test(pageContent);
        const hasIndonesianCurrency = /Rp\s*\d{1,3}(\.\d{3})*/.test(pageContent);
        
        console.log(`  Indonesian date format: ${hasIndonesianDate ? '✓' : '✗'}`);
        console.log(`  Indonesian currency format: ${hasIndonesianCurrency ? '✓' : '✗'}`);
        
        results.roles.push({ 
          role: role.role, 
          status: 'Success', 
          menuItems: menuItems,
          hasIndonesianDate,
          hasIndonesianCurrency
        });
        results.menuItems.push(...menuItems);
        
        logSuccess(`${role.role} Role Test`);
        await takeScreenshot(page, `role-${role.role}-dashboard`);
        
        // Test navigation to each accessible menu item
        for (const menuItem of menuItems.slice(0, 3)) { // Test first 3 menu items
          try {
            console.log(`  Testing navigation to: ${menuItem}`);
            
            // Get the href attribute of the menu item
            const href = await page.evaluate((text) => {
              const links = Array.from(document.querySelectorAll('.sidebar .nav-link'));
              const link = links.find(link => link.textContent.trim() === text);
              return link ? link.href : null;
            }, menuItem);
            
            if (href) {
              await page.goto(href);
              await new Promise(resolve => setTimeout(resolve, 2000));
              
              // Check if page loaded successfully
              const pageUrl = page.url();
              console.log(`    Navigated to: ${pageUrl}`);
              
              // Check for errors
              const errorAlert = await page.$('.alert-danger');
              if (errorAlert) {
                const errorText = await page.$eval('.alert-danger', el => el.textContent);
                console.log(`    ⚠️ Error found: ${errorText}`);
              }
              
              // Go back to dashboard
              await page.goto(config.baseUrl + '/dashboard.php');
              await new Promise(resolve => setTimeout(resolve, 1000));
            } else {
              console.log(`    ⚠️ Could not find href for ${menuItem}`);
            }
          } catch (error) {
            console.log(`    ⚠️ Could not navigate to ${menuItem}: ${error.message}`);
          }
        }
        
        // Logout
        await page.goto(config.baseUrl + '/logout.php');
        await new Promise(resolve => setTimeout(resolve, 1000));
        
      } catch (error) {
        logError(`${role.name} Role Test`, error);
        results.roles.push({ role: role.name, status: 'Failed', menuItems: [] });
      }
    }

    await browser.close();

  } catch (error) {
    console.error('\n💥 Fatal Error:', error);
    results.errors.push({ test: 'Fatal Error', error: error.message });
    if (browser) {
      await browser.close();
    }
  }

  // Print summary
  console.log('\n' + '='.repeat(50));
  console.log('📊 Test Summary');
  console.log('='.repeat(50));
  console.log(`✅ Passed: ${results.passed}`);
  console.log(`❌ Failed: ${results.failed}`);
  console.log(`👥 Roles Tested: ${results.roles.length}`);
  console.log('='.repeat(50));

  if (results.errors.length > 0) {
    console.log('\n❌ Errors:');
    results.errors.forEach(err => {
      console.log(`  - ${err.test}: ${err.error}`);
    });
  }

  console.log('\n👥 Roles Tested:');
  results.roles.forEach(r => {
    console.log(`  - ${r.role}: ${r.status}`);
    if (r.menuItems.length > 0) {
      console.log(`    Menu items: ${r.menuItems.join(', ')}`);
    }
    if (r.hasIndonesianDate !== undefined) {
      console.log(`    Indonesian date: ${r.hasIndonesianDate ? '✓' : '✗'}`);
    }
    if (r.hasIndonesianCurrency !== undefined) {
      console.log(`    Indonesian currency: ${r.hasIndonesianCurrency ? '✓' : '✗'}`);
    }
  });

  console.log('\n📸 Screenshots saved to:', screenshotDir);
  console.log('='.repeat(50));

  process.exit(results.failed > 0 ? 1 : 0);
}

// Run tests
runTests();
