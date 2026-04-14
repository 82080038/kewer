const { test, expect } = require('@playwright/test');

test.describe('Authentication Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should display login page', async ({ page }) => {
    await expect(page).toHaveTitle(/Login - Kewer Koperasi/);
    await expect(page.locator('h1')).toContainText('Login');
    await expect(page.locator('input[name="username"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('should show error for invalid credentials', async ({ page }) => {
    await page.fill('input[name="username"]', 'invalid');
    await page.fill('input[name="password"]', 'invalid');
    await page.click('button[type="submit"]');
    
    await expect(page.locator('.alert-danger')).toBeVisible();
    await expect(page.locator('.alert-danger')).toContainText('Username atau password salah');
  });

  test('should login successfully with valid credentials', async ({ page }) => {
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    
    await expect(page).toHaveURL(/dashboard/);
    await expect(page.locator('h1')).toContainText('Dashboard');
    await expect(page.locator('.navbar-text')).toContainText('Administrator');
  });

  test('should logout successfully', async ({ page }) => {
    // Login first
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    
    // Then logout
    await page.click('a[href="logout.php"]');
    
    await expect(page).toHaveURL(/login/);
    await expect(page.locator('h1')).toContainText('Login');
  });

  test('should redirect to login if not authenticated', async ({ page }) => {
    await page.goto('/dashboard.php');
    await expect(page).toHaveURL(/login/);
  });

  test('should remember user session', async ({ page }) => {
    // Login
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    
    // Navigate to another page
    await page.goto('/pages/nasabah/index.php');
    await expect(page).toHaveURL(/pages\/nasabah/);
    
    // Go back to dashboard - should still be logged in
    await page.goto('/dashboard.php');
    await expect(page).toHaveURL(/dashboard/);
    await expect(page.locator('.navbar-text')).toContainText('Administrator');
  });
});
