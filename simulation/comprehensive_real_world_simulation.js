/**
 * Comprehensive Real-World Simulation for Kewer Application
 * 
 * This script simulates a complete 3-month real-world scenario for a koperasi pasar
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://localhost/kewer/';
const SCREENSHOT_DIR = path.join(__dirname, '../tests/screenshots/simulation');

if (!fs.existsSync(SCREENSHOT_DIR)) {
    fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

const CREDENTIALS = {
    superadmin: { username: 'patri', password: 'password' }
};

const SIMULATION_DATA = {
    bos: {
        username: 'bos_simulasi',
        password: 'password123',
        nama: 'Bos Simulasi',
        email: 'bos@kewer.id',
        telp: '6281234567890'
    },
    manager_pusat: {
        username: 'manager_pusat_sim',
        password: 'password123',
        nama: 'Manager Pusat Simulasi',
        email: 'manager.pusat@kewer.id',
        telp: '6281234567891',
        gaji: 5000000,
        limit_kasbon: 1000000
    },
    petugas: [
        {
            username: 'petugas1_sim',
            password: 'password123',
            nama: 'Petugas Lapangan 1',
            email: 'petugas1@kewer.id',
            telp: '6281234567892',
            gaji: 3000000,
            limit_kasbon: 500000
        }
    ],
    nasabah: [
        {
            kode: 'NSB001',
            nama: 'Budi Santoso',
            alamat: 'Pasar Induk Blok A No. 10',
            ktp: '3201010101010001',
            telp: '6281111111111',
            jenis_usaha: 'Pedagang Sayur',
            lokasi_pasar: 'Pasar Induk'
        }
    ]
};

async function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

async function takeScreenshot(page, name) {
    const filePath = path.join(SCREENSHOT_DIR, name + '.png');
    await page.screenshot({ path: filePath, fullPage: true });
    console.log('Screenshot saved: ' + filePath);
}

async function loginAs(page, username, password) {
    console.log('Logging in as ' + username + '...');
    await page.goto(BASE_URL + 'login.php', { waitUntil: 'networkidle2', timeout: 60000 });
    await page.waitForSelector('input[name="username"]', { timeout: 10000 });
    
    await page.type('input[name="username"]', username);
    await page.type('input[name="password"]', password);
    await page.click('button[type="submit"]');
    
    await sleep(3000);
    
    console.log(username + ' logged in successfully');
    await takeScreenshot(page, 'login-' + username);
}

async function createUser(page, userData, role) {
    console.log('Creating user: ' + userData.username + ' (' + role + ')...');
    
    await page.goto(BASE_URL + 'pages/users/tambah.php');
    await sleep(500);
    
    await page.type('input[name="username"]', userData.username);
    await page.type('input[name="password"]', userData.password);
    await page.type('input[name="nama"]', userData.nama);
    await page.type('input[name="email"]', userData.email);
    
    await page.select('select[name="role"]', role);
    
    if (userData.gaji) {
        await page.type('input[name="gaji"]', userData.gaji.toString());
    }
    if (userData.limit_kasbon) {
        await page.type('input[name="limit_kasbon"]', userData.limit_kasbon.toString());
    }
    
    await page.click('button[type="submit"]');
    await sleep(2000);
    
    console.log('User ' + userData.username + ' created');
    await takeScreenshot(page, 'created-user-' + userData.username);
}

async function createNasabah(page, nasabahData) {
    console.log('Creating nasabah: ' + nasabahData.nama + '...');
    
    await page.goto(BASE_URL + 'pages/nasabah/tambah.php');
    await sleep(500);
    
    await page.type('input[name="kode_nasabah"]', nasabahData.kode);
    await page.type('input[name="nama"]', nasabahData.nama);
    await page.type('textarea[name="alamat"]', nasabahData.alamat);
    await page.type('input[name="ktp"]', nasabahData.ktp);
    await page.type('input[name="telp"]', nasabahData.telp);
    await page.type('input[name="jenis_usaha"]', nasabahData.jenis_usaha);
    await page.type('input[name="lokasi_pasar"]', nasabahData.lokasi_pasar);
    
    await page.click('button[type="submit"]');
    await sleep(2000);
    
    console.log('Nasabah ' + nasabahData.nama + ' created');
    await takeScreenshot(page, 'created-nasabah-' + nasabahData.kode);
}

async function runSimulation() {
    console.log('=== Starting Comprehensive Real-World Simulation ===\n');
    
    const browser = await puppeteer.launch({
        headless: false,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    const page = await browser.newPage();
    await page.setViewport({ width: 1366, height: 768 });
    
    try {
        console.log('\n=== PHASE 1: SETUP ===');
        await loginAs(page, CREDENTIALS.superadmin.username, CREDENTIALS.superadmin.password);
        
        console.log('\n=== PHASE 2: ORGANIZATIONAL STRUCTURE ===');
        await createUser(page, SIMULATION_DATA.bos, 'bos');
        await createUser(page, SIMULATION_DATA.manager_pusat, 'manager_pusat');
        
        for (const petugas of SIMULATION_DATA.petugas) {
            await createUser(page, petugas, 'petugas');
        }
        
        console.log('\n=== PHASE 3: NASABAH ONBOARDING ===');
        for (const nasabah of SIMULATION_DATA.nasabah) {
            await createNasabah(page, nasabah);
        }
        
        console.log('\n=== PHASE 4: DASHBOARD VERIFICATION ===');
        await page.goto(BASE_URL + 'dashboard.php');
        await sleep(2000);
        await takeScreenshot(page, 'dashboard-final');
        
        console.log('\n=== SIMULATION COMPLETED SUCCESSFULLY ===');
        console.log('Screenshots saved to: ' + SCREENSHOT_DIR);
        
    } catch (error) {
        console.error('Simulation failed:', error);
        await takeScreenshot(page, 'error-screenshot');
    } finally {
        await browser.close();
    }
}

runSimulation().catch(console.error);
