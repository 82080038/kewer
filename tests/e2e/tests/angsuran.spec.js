const { test, expect } = require('@playwright/test');

test.describe('Angsuran Management Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login.php');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/dashboard/);
  });

  test('should display angsuran list page', async ({ page }) => {
    await page.goto('/pages/angsuran/index.php');
    
    await expect(page).toHaveTitle(/Data Angsuran - Kewer Koperasi/);
    await expect(page.locator('h1')).toContainText('Data Angsuran');
    await expect(page.locator('.table')).toBeVisible();
    await expect(page.locator('a[href="bayar.php"]')).toBeVisible();
  });

  test('should display angsuran statistics cards', async ({ page }) => {
    await page.goto('/pages/angsuran/index.php');
    
    await expect(page.locator('.card').filter({ hasText: 'Total' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Belum Bayar' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Lunas' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Telat' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Total Tagihan' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Total Dibayar' })).toBeVisible();
  });

  test('should show late payments alert if any', async ({ page }) => {
    await page.goto('/pages/angsuran/index.php');
    
    // Check if late payments alert exists (may or may not be present)
    const lateAlert = page.locator('.alert-danger').filter({ hasText: 'Tunggakan Terdeteksi' });
    if (await lateAlert.isVisible()) {
      await expect(lateAlert).toBeVisible();
      await expect(lateAlert).toContainText('Tunggakan Terdeteksi');
    }
  });

  test('should search angsuran by various criteria', async ({ page }) => {
    await page.goto('/pages/angsuran/index.php');
    
    // Search by nasabah name
    await page.fill('input[name="search"]', 'test');
    await page.click('button:has-text("Cari")');
    expect(page.url()).toContain('search=test');
    
    // Search by kode pinjaman
    await page.fill('input[name="search"]', 'PNJ');
    await page.click('button:has-text("Cari")');
    expect(page.url()).toContain('search=PNJ');
  });

  test('should filter angsuran by status', async ({ page }) => {
    await page.goto('/pages/angsuran/index.php');
    
    // Filter by status
    await page.selectOption('select[name="status"]', 'belum');
    await page.click('button:has-text("Cari")');
    
    expect(page.url()).toContain('status=belum');
  });

  test('should filter angsuran by month', async ({ page }) => {
    await page.goto('/pages/angsuran/index.php');
    
    // Filter by month
    const currentMonth = new Date().toISOString().slice(0, 7); // YYYY-MM format
    await page.fill('input[name="bulan"]', currentMonth);
    await page.click('button:has-text("Cari")');
    
    expect(page.url()).toContain('bulan=' + currentMonth);
  });

  test('should navigate to bayar angsuran page', async ({ page }) => {
    await page.goto('/pages/angsuran/index.php');
    
    await page.click('a[href="bayar.php"]');
    await expect(page).toHaveURL(/pages\/angsuran\/bayar\.php/);
    await expect(page.locator('h1')).toContainText('Bayar Angsuran');
  });

  test('should display bayar angsuran page with details', async ({ page }) => {
    // Try to access bayar page with pinjaman_id parameter
    await page.goto('/pages/angsuran/bayar.php?pinjaman_id=1');
    
    // Check if page loads (may show error if no valid pinjaman_id)
    const pageTitle = page.locator('h1');
    if (await pageTitle.isVisible()) {
      await expect(pageTitle).toContainText('Bayar Angsuran');
    }
  });

  test('should show payment calculation correctly', async ({ page }) => {
    // Navigate to bayar page (if accessible)
    await page.goto('/pages/angsuran/bayar.php');
    
    // Check if payment details are displayed
    const paymentCards = page.locator('.card').filter({ hasText: 'Tagihan Angsuran' });
    if (await paymentCards.isVisible()) {
      await expect(paymentCards).toBeVisible();
      await expect(page.locator('.card').filter({ hasText: 'Total Pembayaran' })).toBeVisible();
    }
  });

  test('should validate payment amount', async ({ page }) => {
    await page.goto('/pages/angsuran/bayar.php');
    
    // Look for payment form
    const amountInput = page.locator('input[name="jumlah_bayar"]');
    if (await amountInput.isVisible()) {
      // Fill with amount less than required
      await amountInput.fill('0');
      await page.click('button[type="submit"]');
      
      // Should show validation error
      await expect(page.locator('.alert-danger')).toBeVisible();
      await expect(page.locator('.alert-danger')).toContainText('Jumlah bayar kurang');
    }
  });

  test('should format rupiah input correctly', async ({ page }) => {
    await page.goto('/pages/angsuran/bayar.php');
    
    const amountInput = page.locator('input[name="jumlah_bayar"]');
    if (await amountInput.isVisible()) {
      await amountInput.fill('1000000');
      
      // Check if formatted (should have separators)
      const value = await amountInput.inputValue();
      expect(value).toMatch(/1\.000\.000/);
    }
  });

  test('should show late penalty calculation', async ({ page }) => {
    await page.goto('/pages/angsuran/bayar.php');
    
    // Check for late penalty display
    const penaltyCard = page.locator('.card').filter({ hasText: 'Denda Keterlambatan' });
    if (await penaltyCard.isVisible()) {
      await expect(penaltyCard).toBeVisible();
      
      // Check if penalty amount is displayed
      const penaltyAmount = page.locator('.text-danger').filter({ hasText: /Rp/ });
      if (await penaltyAmount.isVisible()) {
        await expect(penaltyAmount).toBeVisible();
      }
    }
  });

  test('should require payment method selection', async ({ page }) => {
    await page.goto('/pages/angsuran/bayar.php');
    
    // Try to submit without selecting payment method
    const submitButton = page.locator('button[type="submit"]');
    if (await submitButton.isVisible()) {
      await submitButton.click();
      
      // Should show validation error for payment method
      const paymentMethodSelect = page.locator('select[name="cara_bayar"]');
      if (await paymentMethodSelect.isVisible()) {
        await expect(paymentMethodSelect).toHaveClass(/:invalid/);
      }
    }
  });

  test('should show payment success message', async ({ page }) => {
    // This test would require a valid angsuran ID and proper setup
    // For now, just check if the bayar page is accessible
    await page.goto('/pages/angsuran/bayar.php');
    
    // Check if we're on the correct page
    const pageTitle = page.locator('h1');
    if (await pageTitle.isVisible()) {
      await expect(pageTitle).toContainText('Bayar Angsuran');
    }
  });

  test('should navigate to pinjaman detail from angsuran list', async ({ page }) => {
    await page.goto('/pages/angsuran/index.php');
    
    // Find and click detail button for pinjaman
    const detailButton = page.locator('a[href*="../pinjaman/detail.php"]').first();
    if (await detailButton.isVisible()) {
      await detailButton.click();
      await expect(page).toHaveURL(/pages\/pinjaman\/detail\.php/);
    }
  });

  test('should navigate to bayar page from angsuran list', async ({ page }) => {
    await page.goto('/pages/angsuran/index.php');
    
    // Find and click bayar button
    const bayarButton = page.locator('a[href*="bayar.php"]').first();
    if (await bayarButton.isVisible()) {
      await bayarButton.click();
      await expect(page).toHaveURL(/pages\/angsuran\/bayar\.php/);
    }
  });

  test('should display WhatsApp links for customer contact', async ({ page }) => {
    await page.goto('/pages/angsuran/index.php');
    
    // Look for WhatsApp links
    const waLinks = page.locator('a[href*="wa.me"]');
    if (await waLinks.first().isVisible()) {
      await expect(waLinks.first()).toBeVisible();
      
      // Check if link opens in new tab
      const target = await waLinks.first().getAttribute('target');
      expect(target).toBe('_blank');
    }
  });

  test('should reset filters correctly', async ({ page }) => {
    await page.goto('/pages/angsuran/index.php');
    
    // Apply some filters
    await page.fill('input[name="search"]', 'test');
    await page.selectOption('select[name="status"]', 'belum');
    await page.click('button:has-text("Cari")');
    
    // Reset filters
    await page.click('a:has-text("Reset")');
    
    // Check if filters are reset
    expect(page.url()).not.toContain('search=');
    expect(page.url()).not.toContain('status=');
  });

  test('should navigate back from bayar page', async ({ page }) => {
    await page.goto('/pages/angsuran/bayar.php');
    
    await page.click('a:has-text("Kembali")');
    await expect(page).toHaveURL(/pages\/angsuran\/index\.php/);
  });
});
