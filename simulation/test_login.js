/**
 * Simple test for login functionality
 * Tests if the test_login endpoint works correctly
 */

const puppeteer = require('puppeteer');

const config = {
  baseUrl: 'http://localhost/kewer',
  headless: false,
  credentials: {
    testbos: { username: 'testbos', password: 'password123' },
    patri: { username: 'patri', password: 'password' }
  }
};

async function testLogin(username, password) {
  console.log(`Testing login for: ${username}`);
  
  const browser = await puppeteer.launch({
    headless: config.headless,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const page = await browser.newPage();
  
  try {
    const loginUrl = `${config.baseUrl}/login.php?test_login=true&username=${username}&password=${password}`;
    console.log(`Navigating to: ${loginUrl}`);
    
    await page.goto(loginUrl, { waitUntil: 'networkidle2', timeout: 15000 });
    
    // Wait a moment for redirect
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    const currentUrl = page.url();
    console.log(`Current URL after login: ${currentUrl}`);
    
    if (currentUrl.includes('dashboard.php')) {
      console.log(`✓ Login successful for ${username}`);
      return true;
    } else if (currentUrl.includes('setup_headquarters')) {
      console.log(`✓ Login successful for ${username}, redirected to headquarters setup`);
      return true;
    } else {
      console.log(`✗ Login failed for ${username} - redirected to ${currentUrl}`);
      return false;
    }
  } catch (error) {
    console.log(`✗ Login error for ${username}: ${error.message}`);
    return false;
  } finally {
    await browser.close();
  }
}

async function runTests() {
  console.log('=== Testing Login Functionality ===\n');
  
  // Test patri
  const patriResult = await testLogin(config.credentials.patri.username, config.credentials.patri.password);
  
  // Test testbos
  const testbosResult = await testLogin(config.credentials.testbos.username, config.credentials.testbos.password);
  
  console.log('\n=== Test Results ===');
  console.log(`Patri login: ${patriResult ? 'PASS' : 'FAIL'}`);
  console.log(`Testbos login: ${testbosResult ? 'PASS' : 'FAIL'}`);
  
  if (patriResult || testbosResult) {
    console.log('\n✓ At least one login method works');
  } else {
    console.log('\n✗ All login methods failed');
  }
}

runTests().catch(error => {
  console.error('Fatal error:', error);
  process.exit(1);
});
