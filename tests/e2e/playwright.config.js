const { devices } = require('@playwright/test');

module.exports = {
  testDir: './tests',
  fullyParallel: false, // Disable parallel for debugging
  forbidOnly: !!process.env.CI,
  retries: 0, // No retries for debugging
  workers: 1, // Single worker
  reporter: 'html',
  timeout: 30000, // 30 seconds timeout
  use: {
    baseURL: 'http://localhost/kewer',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
};
