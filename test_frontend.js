const puppeteer = require('puppeteer');
const fs = require('fs');

// Login credentials
const loginCredentials = {
    username: 'admin',
    password: 'admin123'
};

// Pages to test - navigation paths from dashboard (only pages in dashboard nav)
const pages = [
    { name: 'Nasabah', navSelector: 'a[href="pages/nasabah/index.php"]', tableId: 'nasabahTable' },
    { name: 'Pinjaman', navSelector: 'a[href="pages/pinjaman/index.php"]', tableId: 'pinjamanTable' },
    { name: 'Angsuran', navSelector: 'a[href="pages/angsuran/index.php"]', tableId: 'angsuranTable' },
    { name: 'Cabang', navSelector: 'a[href="pages/cabang/index.php"]', tableId: 'cabangTable' }
];

const results = [];

async function login(page) {
    console.log('\n=== Logging in ===');
    await page.goto('http://localhost/kewer/login.php', { waitUntil: 'networkidle2' });
    
    await page.type('input[name="username"]', loginCredentials.username);
    await page.type('input[name="password"]', loginCredentials.password);
    await page.click('button[type="submit"]');
    
    await page.waitForNavigation({ waitUntil: 'networkidle2' });
    console.log('✓ Login successful');
}

async function testPage(page, pageInfo) {
    console.log(`\n=== Testing ${pageInfo.name} ===`);
    const result = {
        page: pageInfo.name,
        url: '',
        dataTable: false,
        sweetAlert2: false,
        select2: false,
        flatpickr: false,
        consoleErrors: [],
        loadTime: 0
    };

    try {
        const startTime = Date.now();
        
        // Go back to dashboard first
        await page.goto('http://localhost/kewer/dashboard.php', { waitUntil: 'networkidle2', timeout: 30000 });
        
        // Navigate to page using dashboard menu
        await page.click(pageInfo.navSelector);
        await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 });
        
        result.url = page.url();
        result.loadTime = Date.now() - startTime;
        console.log(`Page loaded in ${result.loadTime}ms`);

        // Wait for page to be ready
        await new Promise(resolve => setTimeout(resolve, 3000));

        // Check for console errors
        page.on('console', msg => {
            if (msg.type() === 'error') {
                result.consoleErrors.push(msg.text());
            }
        });

        // Check if CDN scripts are loaded
        const scriptsLoaded = await page.evaluate(() => {
            const scripts = Array.from(document.scripts);
            return scripts.map(s => s.src).filter(src => src !== '');
        });
        console.log('Scripts loaded:', scriptsLoaded.slice(0, 5), '...');
        
        // Check page content
        const pageContent = await page.evaluate(() => {
            return {
                title: document.title,
                bodyText: document.body.innerText.substring(0, 200),
                scriptCount: document.scripts.length,
                hasTable: document.querySelector('table') !== null
            };
        });
        console.log('Page info:', pageContent);

        // Test DataTable.js
        try {
            const dataTable = await page.evaluate((tableId) => {
                // Check if DataTable is available
                if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
                    return { loaded: false, reason: 'jQuery or DataTable not loaded' };
                }
                
                // Check if table exists and is initialized
                const table = $(`#${tableId}`);
                if (table.length === 0) {
                    return { loaded: false, reason: 'Table not found' };
                }
                
                // Check if DataTable is initialized
                const api = table.DataTable();
                if (!api) {
                    return { loaded: false, reason: 'DataTable not initialized' };
                }
                
                return { loaded: true, hasWrapper: $(`#${tableId}_wrapper`).length > 0 };
            }, pageInfo.tableId);
            
            if (dataTable.loaded) {
                result.dataTable = true;
                console.log('✓ DataTable.js loaded');
                
                // Check for DataTable features
                const pagination = await page.$('.dataTables_paginate');
                const search = await page.$('.dataTables_filter');
                const lengthMenu = await page.$('.dataTables_length');
                
                if (pagination) console.log('  ✓ Pagination working');
                if (search) console.log('  ✓ Search working');
                if (lengthMenu) console.log('  ✓ Length menu working');
            } else {
                console.log(`✗ DataTable.js not found: ${dataTable.reason}`);
            }
        } catch (e) {
            console.log('✗ DataTable.js error:', e.message);
        }

        // Test SweetAlert2
        try {
            const sweetAlert = await page.evaluate(() => {
                return typeof Swal !== 'undefined';
            });
            if (sweetAlert) {
                result.sweetAlert2 = true;
                console.log('✓ SweetAlert2 loaded');
            } else {
                console.log('✗ SweetAlert2 not found');
            }
        } catch (e) {
            console.log('✗ SweetAlert2 error:', e.message);
        }

        // Test Select2
        try {
            const select2Loaded = await page.evaluate(() => {
                return typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined';
            });
            
            const select2Container = await page.$('.select2-container');
            
            if (select2Loaded && select2Container) {
                result.select2 = true;
                console.log('✓ Select2 loaded');
            } else if (select2Loaded && !select2Container) {
                console.log('✗ Select2 loaded but no select boxes found');
            } else {
                console.log('✗ Select2 not found');
            }
        } catch (e) {
            console.log('✗ Select2 error:', e.message);
        }

        // Test Flatpickr
        try {
            const flatpickr = await page.evaluate(() => {
                return typeof flatpickr !== 'undefined';
            });
            if (flatpickr) {
                result.flatpickr = true;
                console.log('✓ Flatpickr loaded');
                
                // Check for flatpickr inputs
                const flatpickrInputs = await page.$$('.flatpickr-input');
                if (flatpickrInputs.length > 0) {
                    console.log(`  ✓ ${flatpickrInputs.length} Flatpickr inputs found`);
                }
            } else {
                console.log('✗ Flatpickr not found (may not have date inputs)');
            }
        } catch (e) {
            console.log('✗ Flatpickr error:', e.message);
        }

        // Check for any console errors
        if (result.consoleErrors.length > 0) {
            console.log(`⚠ ${result.consoleErrors.length} console errors found`);
            result.consoleErrors.forEach(err => console.log(`  - ${err}`));
        } else {
            console.log('✓ No console errors');
        }

    } catch (error) {
        console.log(`✗ Page error: ${error.message}`);
        result.error = error.message;
    }

    results.push(result);
    return result;
}

async function runTests() {
    console.log('Starting comprehensive frontend testing...\n');
    
    const browser = await puppeteer.launch({
        headless: false,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const page = await browser.newPage();
    
    // Set viewport
    await page.setViewport({ width: 1920, height: 1080 });

    // Login first
    await login(page);

    for (const pageInfo of pages) {
        await testPage(page, pageInfo);
    }

    await browser.close();

    // Generate report
    console.log('\n\n=== TEST RESULTS SUMMARY ===\n');
    
    let totalPassed = 0;
    let totalTests = pages.length * 4; // 4 tests per page

    results.forEach(result => {
        console.log(`\n${result.page}:`);
        console.log(`  DataTable.js: ${result.dataTable ? '✓' : '✗'}`);
        console.log(`  SweetAlert2: ${result.sweetAlert2 ? '✓' : '✗'}`);
        console.log(`  Select2: ${result.select2 ? '✓' : '✗'}`);
        console.log(`  Flatpickr: ${result.flatpickr ? '✓' : '✗'}`);
        console.log(`  Console Errors: ${result.consoleErrors.length}`);
        console.log(`  Load Time: ${result.loadTime}ms`);
        
        if (result.dataTable) totalPassed++;
        if (result.sweetAlert2) totalPassed++;
        if (result.select2) totalPassed++;
        if (result.flatpickr) totalPassed++;
    });

    console.log(`\n\nTotal: ${totalPassed}/${totalTests} tests passed`);
    
    // Save results to file
    fs.writeFileSync('test_results.json', JSON.stringify(results, null, 2));
    console.log('\nResults saved to test_results.json');
    
    return results;
}

runTests().catch(console.error);
