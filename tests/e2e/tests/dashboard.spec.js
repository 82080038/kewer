const { test, expect } = require('@playwright/test');

test.describe('Dashboard Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('/login.php');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/dashboard/);
  });

  test('should display dashboard page', async ({ page }) => {
    await expect(page).toHaveTitle(/Dashboard - Kewer Koperasi/);
    await expect(page.locator('h1')).toContainText('Dashboard');
    await expect(page.locator('.navbar-brand')).toContainText('Kewer Koperasi');
  });

  test('should display user information in navbar', async ({ page }) => {
    await expect(page.locator('.navbar-text')).toContainText('Administrator');
    await expect(page.locator('a[href="logout.php"]')).toBeVisible();
  });

  test('should display sidebar navigation', async ({ page }) => {
    await expect(page.locator('.sidebar')).toBeVisible();
    await expect(page.locator('a[href="dashboard.php"]')).toBeVisible();
    await expect(page.locator('a[href="../nasabah/index.php"]')).toBeVisible();
    await expect(page.locator('a[href="../pinjaman/index.php"]')).toBeVisible();
    await expect(page.locator('a[href="../angsuran/index.php"]')).toBeVisible();
    await expect(page.locator('a[href="../petugas/index.php"]')).toBeVisible();
    await expect(page.locator('a[href="../cabang/index.php"]')).toBeVisible(); // Admin should see this
  });

  test('should display statistics cards', async ({ page }) => {
    await expect(page.locator('.card').filter({ hasText: 'Total Nasabah' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Pinjaman Aktif' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Outstanding' })).toBeVisible();
    await expect(page.locator('.card').filter({ hasText: 'Tunggakan' })).toBeVisible();
  });

  test('should display recent activities section', async ({ page }) => {
    await expect(page.locator('.card').filter({ hasText: 'Aktivitas Terkini' })).toBeVisible();
    await expect(page.locator('.card-header').filter({ hasText: 'Aktivitas Terkini' })).toBeVisible();
  });

  test('should navigate to nasabah page', async ({ page }) => {
    await page.click('a[href="../nasabah/index.php"]');
    await expect(page).toHaveURL(/pages\/nasabah\/index\.php/);
    await expect(page.locator('h1')).toContainText('Data Nasabah');
  });

  test('should navigate to pinjaman page', async ({ page }) => {
    await page.click('a[href="../pinjaman/index.php"]');
    await expect(page).toHaveURL(/pages\/pinjaman\/index\.php/);
    await expect(page.locator('h1')).toContainText('Data Pinjaman');
  });

  test('should navigate to angsuran page', async ({ page }) => {
    await page.click('a[href="../angsuran/index.php"]');
    await expect(page).toHaveURL(/pages\/angsuran\/index\.php/);
    await expect(page.locator('h1')).toContainText('Data Angsuran');
  });

  test('should navigate to petugas page', async ({ page }) => {
    await page.click('a[href="../petugas/index.php"]');
    await expect(page).toHaveURL(/pages\/petugas\/index\.php/);
    await expect(page.locator('h1')).toContainText('Data Petugas');
  });

  test('should navigate to cabang page', async ({ page }) => {
    await page.click('a[href="../cabang/index.php"]');
    await expect(page).toHaveURL(/pages\/cabang\/index\.php/);
    await expect(page.locator('h1')).toContainText('Data Cabang');
  });

  test('should display cabang selector for admin', async ({ page }) => {
    const cabangSelector = page.locator('#cabangSelector');
    if (await cabangSelector.isVisible()) {
      await expect(cabangSelector).toBeVisible();
      await expect(cabangSelector.locator('option[value=""]')).toHaveText('Semua Cabang');
    }
  });

  test('should switch cabang when selector is changed', async ({ page }) => {
    const cabangSelector = page.locator('#cabangSelector');
    if (await cabangSelector.isVisible()) {
      // Get current URL
      const currentUrl = page.url();
      
      // Select a different cabang (if available)
      const options = await cabangSelector.locator('option').count();
      if (options > 2) { // More than just "Semua Cabang" and current selection
        await cabangSelector.selectOption({ index: 2 }); // Select third option
        
        // Check if URL is updated
        await page.waitForLoadState('networkidle');
        const newUrl = page.url();
        expect(newUrl).toContain('cabang_id=');
      }
    }
  });

  test('should display formatted currency values', async ({ page }) => {
    // Check outstanding card for currency formatting
    const outstandingCard = page.locator('.card').filter({ hasText: 'Outstanding' });
    await expect(outstandingCard).toBeVisible();
    
    const outstandingValue = outstandingCard.locator('.h5');
    if (await outstandingValue.isVisible()) {
      const text = await outstandingValue.textContent();
      expect(text).toMatch(/Rp \d{1,3}(\.\d{3})*/); // Should match Rp format
    }
  });

  test('should display recent activities list', async ({ page }) => {
    const activitiesList = page.locator('.list-group');
    if (await activitiesList.isVisible()) {
      await expect(activitiesList).toBeVisible();
      
      // Check if activities have proper structure
      const activities = activitiesList.locator('.list-group-item');
      if (await activities.count() > 0) {
        await expect(activities.first()).toBeVisible();
      }
    } else {
      // Should show "Belum ada aktivitas" message
      await expect(page.locator('text=Belum ada aktivitas')).toBeVisible();
    }
  });

  test('should show proper date formatting in activities', async ({ page }) => {
    const activitiesList = page.locator('.list-group');
    if (await activitiesList.isVisible()) {
      const activities = activitiesList.locator('.list-group-item');
      if (await activities.count() > 0) {
        const firstActivity = activities.first();
        const dateText = await firstActivity.locator('.text-muted').textContent();
        
        // Check if date is formatted (should contain month name in Indonesian)
        expect(dateText).toMatch(/\d{1,2}\s+(Jan|Feb|Mar|Apr|Mei|Jun|Jul|Agu|Sep|Okt|Nov|Des)\s+\d{4}/);
      }
    }
  });

  test('should logout successfully', async ({ page }) => {
    await page.click('a[href="logout.php"]');
    await expect(page).toHaveURL(/login/);
    await expect(page.locator('h1')).toContainText('Login');
  });

  test('should maintain active navigation state', async ({ page }) => {
    // Check that dashboard link is active
    const dashboardLink = page.locator('a[href="dashboard.php"]');
    await expect(dashboardLink).toHaveClass(/active/);
    
    // Navigate to another page and check active state changes
    await page.click('a[href="../nasabah/index.php"]');
    await expect(page.locator('a[href="../nasabah/index.php"]')).toHaveClass(/active/);
    await expect(page.locator('a[href="dashboard.php"]')).not.toHaveClass(/active/);
  });

  test('should be responsive on mobile', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Check if sidebar is still accessible
    await expect(page.locator('.sidebar')).toBeVisible();
    
    // Check if navigation links are still clickable
    await expect(page.locator('a[href="../nasabah/index.php"]')).toBeVisible();
    await page.click('a[href="../nasabah/index.php"]');
    await expect(page).toHaveURL(/pages\/nasabah\/index\.php/);
  });

  test('should handle navigation correctly', async ({ page }) => {
    // Navigate through multiple pages
    await page.click('a[href="../nasabah/index.php"]');
    await expect(page).toHaveURL(/pages\/nasabah\/index\.php/);
    
    await page.click('a[href="../../dashboard.php"]');
    await expect(page).toHaveURL(/dashboard/);
    
    await page.click('a[href="../pinjaman/index.php"]');
    await expect(page).toHaveURL(/pages\/pinjaman\/index\.php/);
    
    await page.click('a[href="../../dashboard.php"]');
    await expect(page).toHaveURL(/dashboard/);
  });

  test('should show loading states properly', async ({ page }) => {
    // This test checks if the page loads without errors
    await page.goto('/dashboard.php');
    
    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');
    
    // Check that main elements are visible
    await expect(page.locator('h1')).toBeVisible();
    await expect(page.locator('.card').first()).toBeVisible();
  });

  test('should display error handling gracefully', async ({ page }) => {
    // Try to access with invalid session (simulate expired session)
    await page.context().clearCookies();
    await page.goto('/dashboard.php');
    
    // Should redirect to login
    await expect(page).toHaveURL(/login/);
  });
});
