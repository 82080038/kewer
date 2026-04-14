const { test, expect } = require('@playwright/test');

test.describe('Nasabah Management Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login.php');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/dashboard/);
  });

  test('should display nasabah list page', async ({ page }) => {
    await page.goto('/pages/nasabah/index.php');
    
    await expect(page).toHaveTitle(/Data Nasabah - Kewer Koperasi/);
    await expect(page.locator('h1')).toContainText('Data Nasabah');
    await expect(page.locator('.table')).toBeVisible();
    await expect(page.locator('a[href="tambah.php"]')).toBeVisible();
  });

  test('should display nasabah statistics cards', async ({ page }) => {
    await page.goto('/pages/nasabah/index.php');
    
    await expect(page.locator('.card').filter({ hasText: 'Total Nasabah' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Aktif' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Nonaktif' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Blacklist' })).toBeVisible();
  });

  test('should search nasabah by name', async ({ page }) => {
    await page.goto('/pages/nasabah/index.php');
    
    // Enter search term
    await page.fill('input[name="search"]', 'test');
    await page.click('button:has-text("Cari")');
    
    // Wait for results to load
    await page.waitForLoadState('networkidle');
    
    // Check that search was performed (URL should contain search parameter)
    expect(page.url()).toContain('search=test');
  });

  test('should filter nasabah by status', async ({ page }) => {
    await page.goto('/pages/nasabah/index.php');
    
    // Select status filter
    await page.selectOption('select[name="status"]', 'aktif');
    await page.click('button:has-text("Cari")');
    
    // Check that filter was applied
    expect(page.url()).toContain('status=aktif');
  });

  test('should navigate to tambah nasabah page', async ({ page }) => {
    await page.goto('/pages/nasabah/index.php');
    
    await page.click('a[href="tambah.php"]');
    await expect(page).toHaveURL(/pages\/nasabah\/tambah\.php/);
    await expect(page.locator('h1')).toContainText('Tambah Nasabah');
  });

  test('should create new nasabah successfully', async ({ page }) => {
    await page.goto('/pages/nasabah/tambah.php');
    
    // Fill form
    await page.fill('input[name="nama"]', 'Test Nasabah E2E');
    await page.fill('input[name="ktp"]', '1234567890123456');
    await page.fill('input[name="telp"]', '081234567890');
    await page.fill('textarea[name="alamat"]', 'Alamat Test E2E');
    await page.selectOption('select[name="jenis_usaha"]', 'Pedagang Sayur');
    await page.fill('input[name="lokasi_pasar"]', 'Pasar Test E2E');
    
    // Submit form
    await page.click('button[type="submit"]');
    
    // Check for success message
    await expect(page.locator('.alert-success')).toBeVisible();
    await expect(page.locator('.alert-success')).toContainText('Nasabah berhasil ditambahkan');
  });

  test('should show validation errors for invalid data', async ({ page }) => {
    await page.goto('/pages/nasabah/tambah.php');
    
    // Submit empty form
    await page.click('button[type="submit"]');
    
    // Should show validation errors (HTML5 validation)
    await expect(page.locator('input[name="nama"]:invalid')).toBeVisible();
    await expect(page.locator('input[name="ktp"]:invalid')).toBeVisible();
    await expect(page.locator('input[name="telp"]:invalid')).toBeVisible();
  });

  test('should show error for invalid KTP format', async ({ page }) => {
    await page.goto('/pages/nasabah/tambah.php');
    
    // Fill with invalid KTP
    await page.fill('input[name="nama"]', 'Test Nasabah');
    await page.fill('input[name="ktp"]', '123'); // Invalid format
    await page.fill('input[name="telp"]', '081234567890');
    
    await page.click('button[type="submit"]');
    
    // Should show error message
    await expect(page.locator('.alert-danger')).toBeVisible();
    await expect(page.locator('.alert-danger')).toContainText('Format KTP tidak valid');
  });

  test('should show error for invalid phone format', async ({ page }) => {
    await page.goto('/pages/nasabah/tambah.php');
    
    // Fill with invalid phone
    await page.fill('input[name="nama"]', 'Test Nasabah');
    await page.fill('input[name="ktp"]', '1234567890123456');
    await page.fill('input[name="telp"]', '123'); // Invalid format
    
    await page.click('button[type="submit"]');
    
    // Should show error message
    await expect(page.locator('.alert-danger')).toBeVisible();
    await expect(page.locator('.alert-danger')).toContainText('Format telepon tidak valid');
  });

  test('should navigate back from tambah page', async ({ page }) => {
    await page.goto('/pages/nasabah/tambah.php');
    
    await page.click('a:has-text("Kembali")');
    await expect(page).toHaveURL(/pages\/nasabah\/index\.php/);
  });

  test('should display nasabah detail page', async ({ page }) => {
    // First create a nasabah
    await page.goto('/pages/nasabah/tambah.php');
    await page.fill('input[name="nama"]', 'Test Nasabah Detail');
    await page.fill('input[name="ktp"]', '1234567890123457');
    await page.fill('input[name="telp"]', '081234567891');
    await page.click('button[type="submit"]');
    
    // Go back to list
    await page.click('a:has-text("Kembali")');
    
    // Find and click detail button (assuming there's at least one nasabah)
    const detailButton = page.locator('a[href*="detail.php"]').first();
    if (await detailButton.isVisible()) {
      await detailButton.click();
      await expect(page).toHaveURL(/pages\/nasabah\/detail\.php/);
      await expect(page.locator('h1')).toContainText('Detail Nasabah');
    }
  });
});
