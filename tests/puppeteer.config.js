module.exports = {
  launchOptions: {
    headless: false,
    args: [
      '--start-maximized',
      '--disable-save-password-bubble',
      '--disable-password-generation',
      '--disable-translate',
      '--no-first-run',
      '--disable-blink-features=AutomationControlled',
      '--no-sandbox',
      '--disable-setuid-sandbox'
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
