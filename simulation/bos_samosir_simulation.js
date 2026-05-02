/**
 * Bos Operations Simulation - Kabupaten Samosir
 * 
 * This script simulates:
 * 1. Bos login and operations
 * 2. Adding a branch in Kabupaten Samosir
 * 3. Adding members (anggota) to the branch
 * 4. Simulating transactions from headquarters (pusat)
 * 5. Simulating transactions from the branch
 * 
 * Mode: Headed (visible browser)
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

// Configuration
const config = {
  baseUrl: 'http://localhost/kewer',
  headless: false, // Headed mode - visible browser
  slowMo: 0, // No slow down for speed
  credentials: {
    bos: { username: 'testbos', password: 'password123' }, // Using testbos for simulation
    admin: { username: 'patri', password: 'password' }, // Using patri as admin for simulation
    manager_pusat: { username: 'patri', password: 'password' },
    manager_cabang: { username: 'patri', password: 'password' }
  },
  samosirData: {
    provinsi: 'Sumatera Utara',
    kabupaten: 'Samosir',
    kecamatan: 'Pangururan',
    desa: 'Pangururan I',
    kode_pos: '22392'
  }
};

// Simulation state
const simulationState = {
  branchId: null,
  members: [],
  transactions: [],
  errors: []
};

// Helper functions
function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

function log(message, type = 'info') {
  const timestamp = new Date().toISOString();
  const logMessage = `[${timestamp}] [${type.toUpperCase()}] ${message}`;
  console.log(logMessage);
}

function logError(message, error) {
  log(message, 'error');
  simulationState.errors.push({
    message,
    error: error ? error.message : null,
    timestamp: new Date().toISOString()
  });
}

// Login function using test_login endpoint for automation
async function login(page, username, password) {
  try {
    log(`Logging in as ${username} using test_login endpoint`);
    
    // Use test_login GET parameter for automated testing
    const loginUrl = `${config.baseUrl}/login.php?test_login=true&username=${username}&password=${password}`;
    await page.goto(loginUrl, { waitUntil: 'networkidle2', timeout: 15000 });
    
    // Wait a moment for redirect
    await delay(500);
    
    // Verify login success
    const currentUrl = page.url();
    log(`Current URL after login: ${currentUrl}`);
    
    if (currentUrl.includes('dashboard.php') || currentUrl.includes('dashboard')) {
      log(`Login successful for ${username}`, 'success');
      return true;
    } else if (currentUrl.includes('setup_headquarters')) {
      log(`Login successful for ${username}, redirected to headquarters setup`, 'success');
      return true;
    } else {
      throw new Error(`Login failed - redirected to ${currentUrl}`);
    }
  } catch (error) {
    logError(`Login failed for ${username}`, error);
    return false;
  }
}

// Add Branch in Kabupaten Samosir
async function addBranchSamosir(page) {
  try {
    log('Adding branch in Kabupaten Samosir');
    
    await page.goto(`${config.baseUrl}/pages/cabang/tambah.php`, { waitUntil: 'networkidle2', timeout: 30000 });
    
    // Wait for form to load with increased timeout
    await page.waitForSelector('input[name="kode_cabang"]', { timeout: 20000 });
    
    // Fill branch form with Samosir data
    const branchCode = `SMS-${Date.now().toString().slice(-4)}`;
    await page.type('input[name="kode_cabang"]', branchCode);
    await page.type('input[name="nama_cabang"]', 'Cabang Samosir Pangururan');
    await page.type('input[name="telp"]', '081234567890');
    await page.type('input[name="email"]', 'samosir@kewer.com');
    
    // Address fields for Samosir
    await page.type('textarea[name="alamat"]', 'Jl. Raya Pangururan No. 123');
    
    // Check if province dropdown exists
    const provinceSelect = await page.$('select[name="province_id"]');
    if (provinceSelect) {
      // Get province options and select by value (ID) instead of text
      const provinceOptions = await page.evaluate(() => {
        const select = document.querySelector('select[name="province_id"]');
        return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
      });
      
      const sumutOption = provinceOptions.find(o => o.text.toLowerCase().includes('sumatera'));
      if (sumutOption) {
        await page.select('select[name="province_id"]', sumutOption.value);
        await delay(500);
        
        // Get kabupaten options and select by value
        const kabupatenOptions = await page.evaluate(() => {
          const select = document.querySelector('select[name="regency_id"]');
          return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
        });
        
        const samosirOption = kabupatenOptions.find(o => o.text.toLowerCase().includes('samosir'));
        if (samosirOption) {
          await page.select('select[name="regency_id"]', samosirOption.value);
          await delay(500);
          
          // Get kecamatan options and select by value
          const kecamatanOptions = await page.evaluate(() => {
            const select = document.querySelector('select[name="district_id"]');
            return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
          });
          
          const pangururanOption = kecamatanOptions.find(o => o.text.toLowerCase().includes('pangururan'));
          if (pangururanOption) {
            await page.select('select[name="district_id"]', pangururanOption.value);
            await delay(500);
            
            // Get desa options and select by value
            const desaOptions = await page.evaluate(() => {
              const select = document.querySelector('select[name="village_id"]');
              return Array.from(select.options).map(opt => ({ value: opt.value, text: opt.text }));
            });
            
            // Select first available desa if Pangururan I not found
            if (desaOptions.length > 1) {
              await page.select('select[name="village_id"]', desaOptions[1].value);
              await delay(100);
            }
          }
        }
      }
    }
    
    // Status
    const statusSelect = await page.$('select[name="status"]');
    if (statusSelect) {
      await page.select('select[name="status"]', 'aktif');
    }
    
    // DO NOT check "Jadikan Kantor Pusat" - this is a regular branch
    const isHeadquartersCheckbox = await page.$('input[name="is_headquarters"]');
    if (isHeadquartersCheckbox) {
      await page.evaluate(el => el.checked = false, isHeadquartersCheckbox);
    }
    
    // Submit form
    await page.click('button[type="submit"]');
    
    // Wait for navigation with increased timeout
    await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 });
    
    // Check for success or error messages
    const successAlert = await page.$('.alert-success');
    const errorAlert = await page.$('.alert-danger');
    
    if (errorAlert) {
      const errorText = await page.evaluate(el => el.textContent, errorAlert);
      throw new Error(`Form validation error: ${errorText}`);
    }
    
    if (successAlert) {
      const successText = await page.evaluate(el => el.textContent, successAlert);
      log(`Branch added successfully: ${branchCode} - ${successText}`, 'success');
      
      // Try to extract branch ID from success message or page content
      // If there's a link to view branches, navigate to get the ID
      const viewBranchesLink = await page.$('a[href*="index.php"]');
      if (viewBranchesLink) {
        try {
          await page.click('a[href*="index.php"]');
          await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 });
          
          // Now on index page, try to find the newly added branch
          const branchRow = await page.evaluate((branchCode) => {
            const rows = Array.from(document.querySelectorAll('tbody tr'));
            for (const row of rows) {
              const cells = row.querySelectorAll('td');
              if (cells.length > 0) {
                const codeCell = cells[0].textContent.trim();
                if (codeCell.includes(branchCode)) {
                  const editLink = cells[cells.length - 1].querySelector('a[href*="edit.php"]');
                  if (editLink) {
                    const match = editLink.href.match(/id=(\d+)/);
                    return match ? match[1] : null;
                  }
                }
              }
            }
            return null;
          }, branchCode);
          
          if (branchRow) {
            simulationState.branchId = branchRow;
            log(`Branch ID extracted: ${simulationState.branchId}`, 'success');
          } else {
            log('Could not extract branch ID from index page', 'warning');
          }
        } catch (error) {
          log(`Could not navigate to index page to extract branch ID: ${error.message}`, 'warning');
          log('Branch was added successfully but ID extraction failed - continuing without branch ID', 'warning');
        }
      }
      
      return true;
    }
    
    throw new Error('Form submission failed - no success message found');
  } catch (error) {
    logError('Add branch failed', error);
    return false;
  }
}

// Add Member (Petugas/Karyawan) to Branch
async function addMember(page, memberData) {
  try {
    log(`Adding member: ${memberData.nama}`);
    
    await page.goto(`${config.baseUrl}/pages/petugas/tambah.php`, { waitUntil: 'networkidle2', timeout: 30000 });
    
    // Wait for form to load
    await page.waitForSelector('input[name="nama"]', { timeout: 20000 });
    
    // Check if there are any error messages already on the page
    const existingError = await page.$('.alert-danger');
    if (existingError) {
      const errorText = await page.evaluate(el => el.textContent, existingError);
      log(`Page has existing error: ${errorText}`, 'warning');
    }
    
    // Fill member form
    await page.type('input[name="username"]', memberData.username);
    await page.type('input[name="password"]', memberData.password);
    await page.type('input[name="confirm_password"]', memberData.password); // Confirm password
    await page.type('input[name="nama"]', memberData.nama);
    await page.type('input[name="email"]', memberData.email);
    
    // Select role
    await page.select('select[name="role"]', memberData.role);
    await delay(100);
    
    // Select branch (Samosir branch)
    if (simulationState.branchId) {
      await page.select('select[name="cabang_id"]', simulationState.branchId);
      await delay(100);
    } else {
      log('No branch ID available, skipping branch selection', 'warning');
    }
    
    // Submit form
    await page.click('button[type="submit"]');
    
    // Wait for navigation with increased timeout
    await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 });
    
    // Check if we're still on the same page (indicates validation error)
    const currentUrl = page.url();
    if (currentUrl.includes('tambah.php')) {
      // Check for error messages
      const errorAlert = await page.$('.alert-danger');
      if (errorAlert) {
        const errorText = await page.evaluate(el => el.textContent, errorAlert);
        throw new Error(`Form validation error: ${errorText}`);
      }
      throw new Error('Form submission failed - still on tambah.php page');
    }
    
    log(`Member added successfully: ${memberData.nama}`, 'success');
    simulationState.members.push(memberData);
    
    return true;
  } catch (error) {
    logError(`Add member failed for ${memberData.nama}`, error);
    return false;
  }
}

// Simulate Transaction from Pusat (Headquarters)
async function simulatePusatTransaction(page, transactionData) {
  try {
    log(`Simulating pusat transaction: ${transactionData.type}`);
    
    // Login as manager_pusat
    await login(page, config.credentials.manager_pusat.username, config.credentials.manager_pusat.password);
    
    await page.goto(`${config.baseUrl}/dashboard.php`, { waitUntil: 'networkidle2' });
    
    // Navigate based on transaction type
    if (transactionData.type === 'nasabah') {
      // Create nasabah from pusat
      await page.goto(`${config.baseUrl}/pages/nasabah/tambah.php`, { waitUntil: 'networkidle2' });
      
      await page.waitForSelector('input[name="nama"]', { timeout: 10000 });
      await page.type('input[name="nama"]', transactionData.nama);
      await page.type('input[name="ktp"]', transactionData.ktp);
      await page.type('input[name="telp"]', transactionData.telepon);
      await page.type('textarea[name="alamat"]', transactionData.alamat);
      await page.select('select[name="jenis_usaha"]', transactionData.jenis_usaha);
      
      await page.click('button[type="submit"]');
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 });
      
      log(`Pusat transaction completed: Created nasabah ${transactionData.nama}`, 'success');
      
    } else if (transactionData.type === 'pinjaman') {
      // Create pinjaman from pusat
      await page.goto(`${config.baseUrl}/pages/pinjaman/tambah.php`, { waitUntil: 'networkidle2' });
      
      await page.waitForSelector('select[name="nasabah_id"]', { timeout: 10000 });
      await page.select('select[name="nasabah_id"]', transactionData.nasabah_id.toString());
      await page.type('input[name="plafon"]', transactionData.plafon.toString());
      await page.type('input[name="tenor"]', transactionData.tenor.toString());
      await page.type('input[name="bunga_per_bulan"]', '2.5');
      await page.type('input[name="tanggal_akad"]', new Date().toISOString().split('T')[0]);
      await page.type('textarea[name="tujuan_pinjaman"]', transactionData.tujuan);
      await page.type('textarea[name="jaminan"]', 'Tanpa jaminan');
      
      await page.click('button[type="submit"]');
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 });
      
      log(`Pusat transaction completed: Created pinjaman Rp ${transactionData.plafon}`, 'success');
    }
    
    simulationState.transactions.push({
      source: 'pusat',
      type: transactionData.type,
      data: transactionData,
      timestamp: new Date().toISOString()
    });
    
    return true;
  } catch (error) {
    logError(`Pusat transaction failed`, error);
    return false;
  }
}

// Simulate Transaction from Cabang (Samosir Branch)
async function simulateCabangTransaction(page, transactionData) {
  try {
    log(`Simulating cabang transaction: ${transactionData.type}`);
    
    // Login as manager_cabang (assigned to Samosir branch)
    await login(page, config.credentials.manager_cabang.username, config.credentials.manager_cabang.password);
    
    await page.goto(`${config.baseUrl}/dashboard.php`, { waitUntil: 'networkidle2' });
    
    // Navigate based on transaction type
    if (transactionData.type === 'nasabah') {
      // Create nasabah from cabang
      await page.goto(`${config.baseUrl}/pages/nasabah/tambah.php`, { waitUntil: 'networkidle2' });
      
      await page.waitForSelector('input[name="nama"]', { timeout: 10000 });
      await page.type('input[name="nama"]', transactionData.nama);
      await page.type('input[name="ktp"]', transactionData.ktp);
      await page.type('input[name="telp"]', transactionData.telepon);
      await page.type('textarea[name="alamat"]', transactionData.alamat);
      await page.select('select[name="jenis_usaha"]', transactionData.jenis_usaha);
      
      await page.click('button[type="submit"]');
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 });
      
      log(`Cabang transaction completed: Created nasabah ${transactionData.nama}`, 'success');
      
    } else if (transactionData.type === 'pinjaman') {
      // Create pinjaman from cabang
      await page.goto(`${config.baseUrl}/pages/pinjaman/tambah.php`, { waitUntil: 'networkidle2' });
      
      await page.waitForSelector('select[name="nasabah_id"]', { timeout: 10000 });
      await page.select('select[name="nasabah_id"]', transactionData.nasabah_id.toString());
      await page.type('input[name="plafon"]', transactionData.plafon.toString());
      await page.type('input[name="tenor"]', transactionData.tenor.toString());
      await page.type('input[name="bunga_per_bulan"]', '2.5');
      await page.type('input[name="tanggal_akad"]', new Date().toISOString().split('T')[0]);
      await page.type('textarea[name="tujuan_pinjaman"]', transactionData.tujuan);
      await page.type('textarea[name="jaminan"]', 'Tanpa jaminan');
      
      await page.click('button[type="submit"]');
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 });
      
      log(`Cabang transaction completed: Created pinjaman Rp ${transactionData.plafon}`, 'success');
      
    } else if (transactionData.type === 'angsuran') {
      // Payment from cabang
      await page.goto(`${config.baseUrl}/pages/angsuran/bayar.php`, { waitUntil: 'networkidle2' });
      
      await page.waitForSelector('select[name="angsuran_id"]', { timeout: 10000 });
      await page.select('select[name="angsuran_id"]', transactionData.angsuran_id.toString());
      await page.type('input[name="jumlah_bayar"]', transactionData.jumlah.toString());
      await page.type('input[name="tanggal_bayar"]', new Date().toISOString().split('T')[0]);
      await page.select('select[name="metode_pembayaran"]', 'tunai');
      
      await page.click('button[type="submit"]');
      await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 });
      
      log(`Cabang transaction completed: Paid angsuran Rp ${transactionData.jumlah}`, 'success');
    }
    
    simulationState.transactions.push({
      source: 'cabang',
      type: transactionData.type,
      data: transactionData,
      timestamp: new Date().toISOString()
    });
    
    return true;
  } catch (error) {
    logError(`Cabang transaction failed`, error);
    return false;
  }
}

// Generate random data
function generateKTP() {
  return Math.floor(1000000000000000 + Math.random() * 9000000000000000).toString();
}

function generatePhone() {
  return '0' + Math.floor(80000000000 + Math.random() * 19999999999).toString();
}

// Main simulation function
async function runSimulation() {
  log('=== Starting Bos Operations Simulation - Kabupaten Samosir ===');
  log('Mode: Headed (visible browser)');
  log('');
  
  const browser = await puppeteer.launch({
    headless: config.headless,
    slowMo: config.slowMo,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--start-maximized']
  });
  
  const page = await browser.newPage();
  
  try {
    // Step 1: Login as Bos
    log('Step 1: Login as Bos');
    const bosLoginSuccess = await login(page, config.credentials.bos.username, config.credentials.bos.password);
    
    if (!bosLoginSuccess) {
      throw new Error('Bos login failed');
    }
    
    await delay(500);
    
    // Step 2: Add Branch in Kabupaten Samosir
    log('Step 2: Adding branch in Kabupaten Samosir');
    const branchSuccess = await addBranchSamosir(page);
    
    if (!branchSuccess) {
      throw new Error('Failed to add Samosir branch');
    }
    
    await delay(500);
    
    // Step 3: Add Members to Branch
    log('Step 3: Adding members to Samosir branch');
    
    const members = [
      {
        nama: 'Budi Santoso',
        username: 'budi_samosir',
        password: 'password123',
        email: 'budi@kewer.com',
        role: 'manager_cabang'
      },
      {
        nama: 'Siti Aminah',
        username: 'siti_samosir',
        password: 'password123',
        email: 'siti@kewer.com',
        role: 'admin_cabang'
      },
      {
        nama: 'Rahmat Hidayat',
        username: 'rahmat_samosir',
        password: 'password123',
        email: 'rahmat@kewer.com',
        role: 'petugas_cabang'
      }
    ];
    
    for (const member of members) {
      const memberSuccess = await addMember(page, member);
      if (memberSuccess) {
        log(`Member added: ${member.nama}`, 'success');
      }
      await delay(300);
    }
    
    // Step 4: Simulate Transactions from Pusat
    log('Step 4: Simulating transactions from Pusat (Headquarters)');
    
    const pusatTransactions = [
      {
        type: 'nasabah',
        nama: 'PT. Samosir Maju',
        ktp: generateKTP(),
        telepon: generatePhone(),
        alamat: 'Jl. Raya Pangururan No. 100',
        jenis_usaha: 'Toko Bangunan'
      },
      {
        type: 'nasabah',
        nama: 'Ibu Maria Simanjuntak',
        ktp: generateKTP(),
        telepon: generatePhone(),
        alamat: 'Dusun I, Pangururan',
        jenis_usaha: 'Warung Makan'
      }
    ];
    
    for (const transaction of pusatTransactions) {
      const success = await simulatePusatTransaction(page, transaction);
      if (success) {
        log(`Pusat transaction completed: ${transaction.type}`, 'success');
      }
      await delay(300);
    }
    
    // Step 5: Simulate Transactions from Cabang
    log('Step 5: Simulating transactions from Cabang Samosir');
    
    const cabangTransactions = [
      {
        type: 'nasabah',
        nama: 'Pak Toba Sinaga',
        ktp: generateKTP(),
        telepon: generatePhone(),
        alamat: 'Desa Pangururan II',
        jenis_usaha: 'Toko Kelontong'
      },
      {
        type: 'nasabah',
        nama: 'Boru Siregar',
        ktp: generateKTP(),
        telepon: generatePhone(),
        alamat: 'Jl. Wisata Pangururan',
        jenis_usaha: 'Warung Kopi'
      }
    ];
    
    for (const transaction of cabangTransactions) {
      const success = await simulateCabangTransaction(page, transaction);
      if (success) {
        log(`Cabang transaction completed: ${transaction.type}`, 'success');
      }
      await delay(300);
    }
    
    // Final summary
    log('');
    log('=== Simulation Summary ===');
    log(`Branch ID: ${simulationState.branchId}`);
    log(`Members added: ${simulationState.members.length}`);
    log(`Total transactions: ${simulationState.transactions.length}`);
    log(`Pusat transactions: ${simulationState.transactions.filter(t => t.source === 'pusat').length}`);
    log(`Cabang transactions: ${simulationState.transactions.filter(t => t.source === 'cabang').length}`);
    log(`Errors: ${simulationState.errors.length}`);
    log('');
    log('Simulation completed successfully!', 'success');
    
    // Save simulation report
    const report = {
      branchId: simulationState.branchId,
      members: simulationState.members,
      transactions: simulationState.transactions,
      errors: simulationState.errors,
      timestamp: new Date().toISOString()
    };
    
    const reportDir = path.join(__dirname, 'logs');
    if (!fs.existsSync(reportDir)) {
      fs.mkdirSync(reportDir, { recursive: true });
    }
    
    const reportFile = path.join(reportDir, `bos_samosir_simulation_${new Date().toISOString().split('T')[0]}.json`);
    fs.writeFileSync(reportFile, JSON.stringify(report, null, 2));
    log(`Report saved to: ${reportFile}`, 'success');
    
    // Keep browser open for 5 seconds for review
    log('Browser will remain open for 5 seconds for review...');
    await delay(5000);
    
  } catch (error) {
    logError('Simulation failed', error);
    log('Browser will remain open for 30 seconds for review...');
    await delay(30000);
  } finally {
    await browser.close();
  }
}

// Run simulation
runSimulation().catch(error => {
  logError('Fatal error', error);
  process.exit(1);
});
