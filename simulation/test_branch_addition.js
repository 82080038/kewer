/**
 * Test branch addition specifically
 * This will help debug why branch addition is failing
 */

const puppeteer = require('puppeteer');

const config = {
  baseUrl: 'http://localhost/kewer',
  headless: false,
  credentials: {
    testbos: { username: 'testbos', password: 'password123' }
  },
  samosirData: {
    provinsi: 'Sumatera Utara',
    kabupaten: 'Samosir',
    kecamatan: 'Pangururan',
    desa: 'Pangururan I',
    kode_pos: '22392'
  }
};

async function testBranchAddition() {
  console.log('=== Testing Branch Addition ===\n');
  
  const browser = await puppeteer.launch({
    headless: config.headless,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const page = await browser.newPage();
  
  try {
    // Login
    console.log('1. Logging in as testbos...');
    const loginUrl = `${config.baseUrl}/login.php?test_login=true&username=${config.credentials.testbos.username}&password=${config.credentials.testbos.password}`;
    await page.goto(loginUrl, { waitUntil: 'networkidle2', timeout: 15000 });
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    const currentUrl = page.url();
    console.log(`   Current URL: ${currentUrl}`);
    
    if (!currentUrl.includes('dashboard.php')) {
      throw new Error('Login failed');
    }
    console.log('   ✓ Login successful\n');
    
    // Navigate to branch addition page
    console.log('2. Navigating to branch addition page...');
    await page.goto(`${config.baseUrl}/pages/cabang/tambah.php`, { waitUntil: 'networkidle2', timeout: 30000 });
    console.log('   ✓ Page loaded\n');
    
    // Check for form elements
    console.log('3. Checking form elements...');
    const kodeInput = await page.$('input[name="kode_cabang"]');
    const namaInput = await page.$('input[name="nama_cabang"]');
    const telpInput = await page.$('input[name="telp"]');
    const emailInput = await page.$('input[name="email"]');
    const alamatInput = await page.$('textarea[name="alamat"]');
    const provinceSelect = await page.$('select[name="province_id"]');
    
    console.log(`   kode_cabang input: ${kodeInput ? '✓' : '✗'}`);
    console.log(`   nama_cabang input: ${namaInput ? '✓' : '✗'}`);
    console.log(`   telp input: ${telpInput ? '✓' : '✗'}`);
    console.log(`   email input: ${emailInput ? '✓' : '✗'}`);
    console.log(`   alamat textarea: ${alamatInput ? '✓' : '✗'}`);
    console.log(`   province_id select: ${provinceSelect ? '✓' : '✗'}\n`);
    
    if (!kodeInput || !namaInput) {
      throw new Error('Required form elements not found');
    }
    
    // Fill the form
    console.log('4. Filling the form...');
    const branchCode = `SMS-${Date.now().toString().slice(-4)}`;
    console.log(`   Branch code: ${branchCode}`);
    
    await page.type('input[name="kode_cabang"]', branchCode);
    console.log('   ✓ kode_cabang filled');
    
    await page.type('input[name="nama_cabang"]', 'Cabang Samosir Pangururan');
    console.log('   ✓ nama_cabang filled');
    
    await page.type('input[name="telp"]', '081234567890');
    console.log('   ✓ telp filled');
    
    await page.type('input[name="email"]', 'samosir@kewer.com');
    console.log('   ✓ email filled');
    
    await page.type('textarea[name="alamat"]', 'Jl. Raya Pangururan No. 123');
    console.log('   ✓ alamat filled');
    
    // Check province dropdown options
    console.log('\n5. Checking province dropdown...');
    const provinceOptions = await page.evaluate(() => {
      const select = document.querySelector('select[name="province_id"]');
      if (!select) return [];
      return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
    });
    
    console.log(`   Found ${provinceOptions.length} province options`);
    if (provinceOptions.length > 0) {
      console.log('   All options:');
      provinceOptions.forEach(opt => {
        console.log(`     - value: "${opt.value}", text: "${opt.text}"`);
      });
      
      // Find the correct province value for Sumatera Utara
      const sumutOption = provinceOptions.find(o => o.text.toLowerCase().includes('sumatera') || o.text.toLowerCase().includes('utara'));
      if (sumutOption) {
        console.log(`   Found Sumatera Utara option with value: "${sumutOption.value}"`);
        
        // Select by value instead of text
        if (provinceSelect) {
          await page.select('select[name="province_id"]', sumutOption.value);
          console.log(`   ✓ Selected province by value: ${sumutOption.value}`);
          await new Promise(resolve => setTimeout(resolve, 1000));
          
          // Check if kabupaten dropdown is populated
          console.log('\n6. Checking kabupaten dropdown after province selection...');
          const kabupatenOptions = await page.evaluate(() => {
            const select = document.querySelector('select[name="regency_id"]');
            if (!select) return [];
            return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
          });
          
          console.log(`   Found ${kabupatenOptions.length} kabupaten options`);
          if (kabupatenOptions.length > 1) { // More than just the default option
            console.log('   All kabupaten options:');
            kabupatenOptions.forEach(opt => {
              console.log(`     - value: "${opt.value}", text: "${opt.text}"`);
            });
            
            // Try to select Samosir
            const samosirOption = kabupatenOptions.find(o => o.text.toLowerCase().includes('samosir'));
            if (samosirOption) {
              console.log(`   Found Samosir option with value: "${samosirOption.value}"`);
              await page.select('select[name="regency_id"]', samosirOption.value);
              console.log(`   ✓ Selected kabupaten by value: ${samosirOption.value}`);
              await new Promise(resolve => setTimeout(resolve, 1000));
              
              // Check if kecamatan dropdown is populated
              console.log('\n6b. Checking kecamatan dropdown after kabupaten selection...');
              const kecamatanOptions = await page.evaluate(() => {
                const select = document.querySelector('select[name="district_id"]');
                if (!select) return [];
                return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
              });
              
              console.log(`   Found ${kecamatanOptions.length} kecamatan options`);
              if (kecamatanOptions.length > 1) {
                console.log('   First few kecamatan options:');
                kecamatanOptions.slice(0, 5).forEach(opt => {
                  console.log(`     - value: "${opt.value}", text: "${opt.text}"`);
                });
                
                // Try to select Pangururan
                const pangururanOption = kecamatanOptions.find(o => o.text.toLowerCase().includes('pangururan'));
                if (pangururanOption) {
                  console.log(`   Found Pangururan option with value: "${pangururanOption.value}"`);
                  await page.select('select[name="district_id"]', pangururanOption.value);
                  console.log(`   ✓ Selected kecamatan by value: ${pangururanOption.value}`);
                  await new Promise(resolve => setTimeout(resolve, 1000));
                  
                  // Check if desa dropdown is populated
                  console.log('\n6c. Checking desa dropdown after kecamatan selection...');
                  const desaOptions = await page.evaluate(() => {
                    const select = document.querySelector('select[name="village_id"]');
                    if (!select) return [];
                    return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
                  });
                  
                  console.log(`   Found ${desaOptions.length} desa options`);
                  if (desaOptions.length > 1) {
                    console.log('   First few desa options:');
                    desaOptions.slice(0, 5).forEach(opt => {
                      console.log(`     - value: "${opt.value}", text: "${opt.text}"`);
                    });
                    
                    // Try to select Pangururan I
                    const pangururanIOption = desaOptions.find(o => o.text.toLowerCase().includes('pangururan i') || o.text.toLowerCase().includes('pangururan 1'));
                    if (pangururanIOption) {
                      console.log(`   Found Pangururan I option with value: "${pangururanIOption.value}"`);
                      await page.select('select[name="village_id"]', pangururanIOption.value);
                      console.log(`   ✓ Selected desa by value: ${pangururanIOption.value}`);
                    } else {
                      console.log('   - Pangururan I option not found, using first available option');
                      await page.select('select[name="village_id"]', desaOptions[1].value);
                      console.log(`   ✓ Selected desa: ${desaOptions[1].text}`);
                    }
                  } else {
                    console.log('   ✗ No desa options found (only default option)');
                  }
                } else {
                  console.log('   - Pangururan option not found, using first available option');
                  await page.select('select[name="district_id"]', kecamatanOptions[1].value);
                  console.log(`   ✓ Selected kecamatan: ${kecamatanOptions[1].text}`);
                }
              } else {
                console.log('   ✗ No kecamatan options found (only default option)');
              }
            } else {
              console.log('   - Samosir option not found, using first available kabupaten');
              await page.select('select[name="regency_id"]', kabupatenOptions[1].value);
              console.log(`   ✓ Selected kabupaten: ${kabupatenOptions[1].text}`);
            }
          } else {
            console.log('   ✗ No kabupaten options found (only default option)');
          }
        }
      } else {
        console.log('   ✗ Sumatera Utara option not found');
      }
    } else {
      console.log('   ✗ No province options found');
    }
    
    // Select status
    console.log('\n7. Selecting status...');
    const statusSelect = await page.$('select[name="status"]');
    if (statusSelect) {
      await page.select('select[name="status"]', 'aktif');
      console.log('   ✓ Status set to aktif');
    } else {
      console.log('   ✗ Status select not found');
    }
    
    // Check for is_headquarters checkbox
    console.log('\n8. Checking is_headquarters checkbox...');
    const isHeadquartersCheckbox = await page.$('input[name="is_headquarters"]');
    if (isHeadquartersCheckbox) {
      await page.evaluate(el => el.checked = false, isHeadquartersCheckbox);
      console.log('   ✓ is_headquarters unchecked');
    } else {
      console.log('   - is_headquarters checkbox not found (may not be visible for this role)');
    }
    
    // Take screenshot before submit
    console.log('\n9. Taking screenshot before submit...');
    await page.screenshot({ path: 'simulation/logs/branch_before_submit.png' });
    console.log('   ✓ Screenshot saved\n');
    
    // Submit form
    console.log('10. Submitting form...');
    await page.click('button[type="submit"]');
    console.log('   ✓ Submit button clicked');
    
    // Wait for navigation
    console.log('\n11. Waiting for navigation...');
    try {
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 });
      console.log('   ✓ Navigation completed');
    } catch (error) {
      console.log('   ✗ Navigation timeout');
      
      // Check if we're still on the same page
      const stillOnFormPage = page.url().includes('tambah.php');
      if (stillOnFormPage) {
        console.log('   Still on tambah.php page - checking for errors...');
        
        const errorAlert = await page.$('.alert-danger');
        if (errorAlert) {
          const errorText = await page.evaluate(el => el.textContent, errorAlert);
          console.log(`   ✗ Error found: ${errorText}`);
        } else {
          console.log('   No error alert found on page');
        }
      }
      
      throw error;
    }
    
    // Check final URL
    const finalUrl = page.url();
    console.log(`\n12. Final URL: ${finalUrl}`);
    
    if (finalUrl.includes('index.php')) {
      console.log('   ✓ Branch added successfully (redirected to index)');
    } else if (finalUrl.includes('tambah.php')) {
      console.log('   ✗ Still on tambah page - checking for error message...');
      
      const errorAlert = await page.$('.alert-danger');
      if (errorAlert) {
        const errorText = await page.evaluate(el => el.textContent, errorAlert);
        console.log(`   ✗ Error message: ${errorText}`);
      } else {
        console.log('   No error alert found on page');
      }
      
      // Check for success message
      const successAlert = await page.$('.alert-success');
      if (successAlert) {
        const successText = await page.evaluate(el => el.textContent, successAlert);
        console.log(`   ✓ Success message: ${successText}`);
      } else {
        console.log('   No success alert found on page');
      }
    } else {
      console.log(`   ? Unexpected URL: ${finalUrl}`);
    }
    
    // Take screenshot after submit
    console.log('\n13. Taking screenshot after submit...');
    await page.screenshot({ path: 'simulation/logs/branch_after_submit.png' });
    console.log('   ✓ Screenshot saved\n');
    
  } catch (error) {
    console.error('\n✗ Test failed:', error.message);
    
    // Take error screenshot
    await page.screenshot({ path: 'simulation/logs/branch_error.png' });
    console.log('Error screenshot saved');
    
  } finally {
    console.log('\nClosing browser...');
    await browser.close();
  }
}

testBranchAddition().catch(error => {
  console.error('Fatal error:', error);
  process.exit(1);
});
