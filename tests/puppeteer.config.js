module.exports = {
  launchOptions: {
    headless: false,
    executablePath: '/usr/bin/google-chrome', // Adjust based on your system
    args: [
      '--start-maximized',
      '--disable-save-password-bubble',
      '--disable-password-generation',
      '--disable-translate',
      '--no-first-run',
      '--disable-blink-features=AutomationControlled'
    ],
    defaultViewport: null,
  },
  timeout: 30000,
  baseUrl: 'http://localhost/kewer',
  screenshotPath: './tests/screenshots',
  contextOptions: {
    ignoreHTTPSErrors: true,
    permissions: {}
  },
};
