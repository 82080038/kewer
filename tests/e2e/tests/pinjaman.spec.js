const { test, expect } = require('@playwright/test');

test.describe('Pinjaman Management Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login.php');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/dashboard/);
  });

  test('should display pinjaman list page', async ({ page }) => {
    await page.goto('/pages/pinjaman/index.php');
    
    await expect(page).toHaveTitle(/Data Pinjaman - Kewer Koperasi/);
    await expect(page.locator('h1')).toContainText('Data Pinjaman');
    await expect(page.locator('.table')).toBeVisible();
    await expect(page.locator('a[href="tambah.php"]')).toBeVisible();
  });

  test('should display pinjaman statistics cards', async ({ page }) => {
    await page.goto('/pages/pinjaman/index.php');
    
    await expect(page.locator('.card').filter({ hasText: 'Total' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Pengajuan' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Disetujui' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Aktif' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Lunas' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Total Plafon' })).toBeVisible();
  });

  test('should navigate to tambah pinjaman page', async ({ page }) => {
    await page.goto('/pages/pinjaman/index.php');
    
    await page.click('a[href="tambah.php"]');
    await expect(page).toHaveURL(/pages\/pinjaman\/tambah\.php/);
    await expect(page.locator('h1')).toContainText('Ajukan Pinjaman Baru');
  });

  test('should create new pinjaman successfully', async ({ page }) => {
    await page.goto('/pages/pinjaman/tambah.php');
    
    // Select nasabah (if available)
    const nasabahSelect = page.locator('select[name="nasabah_id"]');
    if (await nasabahSelect.isVisible()) {
      await nasabahSelect.selectOption({ index: 1 });
    }
    
    // Fill form
    await page.fill('input[name="plafon"]', '1000000');
    await page.fill('input[name="tenor"]', '6');
    await page.fill('input[name="bunga_per_bulan"]', '2.5');
    await page.fill('input[name="tanggal_akad"]', new Date().toISOString().split('T')[0]);
    await page.fill('textarea[name="tujuan_pinjaman"]', 'Modal usaha test E2E');
    await page.fill('textarea[name="jaminan"]', 'Test jaminan E2E');
    
    // Submit form
    await page.click('button[type="submit"]');
    
    // Check for success message
    await expect(page.locator('.alert-success')).toBeVisible();
    await expect(page.locator('.alert-success')).toContainText('Pengajuan pinjaman berhasil dibuat');
  });

  test('should show loan calculation preview', async ({ page }) => {
    await page.goto('/pages/pinjaman/tambah.php');
    
    // Fill loan details
    await page.fill('input[name="plafon"]', '1000000');
    await page.fill('input[name="tenor"]', '6');
    await page.fill('input[name="bunga_per_bulan"]', '2.5');
    
    // Wait for calculation to update
    await page.waitForTimeout(1000);
    
    // Check if preview is updated
    const preview = page.locator('#loanPreview');
    await expect(preview).toContainText('Total Pinjaman');
    await expect(preview).toContainText('Angsuran/Bulan');
  });

  test('should show validation errors for invalid data', async ({ page }) => {
    await page.goto('/pages/pinjaman/tambah.php');
    
    // Submit empty form
    await page.click('button[type="submit"]');
    
    // Should show validation errors
    await expect(page.locator('select[name="nasabah_id"]:invalid')).toBeVisible();
    await expect(page.locator('input[name="plafon"]:invalid')).toBeVisible();
    await expect(page.locator('input[name="tenor"]:invalid')).toBeVisible();
  });

  test('should validate plafon format', async ({ page }) => {
    await page.goto('/pages/pinjaman/tambah.php');
    
    // Select nasabah if available
    const nasabahSelect = page.locator('select[name="nasabah_id"]');
    if (await nasabahSelect.isVisible()) {
      await nasabahSelect.selectOption({ index: 1 });
    }
    
    // Fill with invalid plafon
    await page.fill('input[name="plafon"]', '0'); // Invalid amount
    await page.fill('input[name="tenor"]', '6');
    
    await page.click('button[type="submit"]');
    
    // Should show error message
    await expect(page.locator('.alert-danger')).toBeVisible();
    await expect(page.locator('.alert-danger')).toContainText('Plafon harus berupa angka positif');
  });

  test('should validate tenor range', async ({ page }) => {
    await page.goto('/pages/pinjaman/tambah.php');
    
    // Select nasabah if available
    const nasabahSelect = page.locator('select[name="nasabah_id"]');
    if (await nasabahSelect.isVisible()) {
      await nasabahSelect.selectOption({ index: 1 });
    }
    
    // Fill with invalid tenor
    await page.fill('input[name="plafon"]', '1000000');
    await page.fill('input[name="tenor"]', '15'); // Over maximum
    
    await page.click('button[type="submit"]');
    
    // Should show error message
    await expect(page.locator('.alert-danger')).toBeVisible();
    await expect(page.locator('.alert-danger')).toContainText('Tenor harus antara 1-12 bulan');
  });

  test('should format rupiah input correctly', async ({ page }) => {
    await page.goto('/pages/pinjaman/tambah.php');
    
    const plafonInput = page.locator('input[name="plafon"]');
    await plafonInput.fill('1000000');
    
    // Check if formatted (should have separators)
    const value = await plafonInput.inputValue();
    expect(value).toMatch(/1\.000\.000/);
  });

  test('should search pinjaman by various criteria', async ({ page }) => {
    await page.goto('/pages/pinjaman/index.php');
    
    // Search by kode
    await page.fill('input[name="search"]', 'PNJ');
    await page.click('button:has-text("Cari")');
    expect(page.url()).toContain('search=PNJ');
    
    // Search by nama
    await page.fill('input[name="search"]', 'test');
    await page.click('button:has-text("Cari")');
    expect(page.url()).toContain('search=test');
  });

  test('should filter pinjaman by status', async ({ page }) => {
    await page.goto('/pages/pinjaman/index.php');
    
    // Filter by status
    await page.selectOption('select[name="status"]', 'aktif');
    await page.click('button:has-text("Cari")');
    
    expect(page.url()).toContain('status=aktif');
  });

  test('should display pinjaman detail page', async ({ page }) => {
    await page.goto('/pages/pinjaman/index.php');
    
    // Find and click detail button (if any pinjaman exists)
    const detailButton = page.locator('a[href*="detail.php"]').first();
    if (await detailButton.isVisible()) {
      await detailButton.click();
      await expect(page).toHaveURL(/pages\/pinjaman\/detail\.php/);
      await expect(page.locator('h1')).toContainText('Detail Pinjaman');
      
      // Check for common detail elements
      await expect(page.locator('text=Informasi Pinjaman')).toBeVisible();
      await expect(page.locator('text=Progress Pembayaran')).toBeVisible();
    }
  });

  test('should approve pinjaman (if user has permission)', async ({ page }) => {
    await page.goto('/pages/pinjaman/index.php');
    
    // Look for approve button (only visible for admin/superadmin on pengajuan status)
    const approveButton = page.locator('button:has-text("Setujui")').first();
    if (await approveButton.isVisible()) {
      // Handle the confirmation dialog
      page.on('dialog', dialog => dialog.accept());
      await approveButton.click();
      
      // Should show success message or redirect
      await page.waitForTimeout(2000);
    }
  });

  test('should reject pinjaman (if user has permission)', async ({ page }) => {
    await page.goto('/pages/pinjaman/index.php');
    
    // Look for reject button (only visible for admin/superadmin on pengajuan status)
    const rejectButton = page.locator('button:has-text("Tolak")').first();
    if (await rejectButton.isVisible()) {
      // Handle the confirmation dialog
      page.on('dialog', dialog => dialog.accept());
      await rejectButton.click();
      
      // Should show success message or redirect
      await page.waitForTimeout(2000);
    }
  });

  test('should navigate back from tambah page', async ({ page }) => {
    await page.goto('/pages/pinjaman/tambah.php');
    
    await page.click('a:has-text("Kembali")');
    await expect(page).toHaveURL(/pages\/pinjaman\/index\.php/);
  });
});
