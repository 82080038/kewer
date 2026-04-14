# Kewer Koperasi - End-to-End (E2E) Testing

This directory contains comprehensive end-to-end tests for the Kewer Koperasi system using Playwright.

## Prerequisites

1. Node.js (version 16 or higher)
2. PHP server running on localhost
3. Database configured and accessible

## Setup

1. Install dependencies:
```bash
cd tests/e2e
npm install
```

2. Install Playwright browsers:
```bash
npm run install
```

3. Ensure the PHP application is running:
```bash
# From the root directory
php -S localhost:8000
```

## Running Tests

### Run all tests
```bash
npm test
```

### Run tests in headed mode (visible browser)
```bash
npm run test:headed
```

### Run tests with debugging
```bash
npm run test:debug
```

### Generate HTML report
```bash
npm run test:report
```

### Run specific test file
```bash
npx playwright test tests/auth.spec.js
```

### Run tests in specific browser
```bash
npx playwright test --project=chromium
npx playwright test --project=firefox
npx playwright test --project=webkit
```

## Test Coverage

### Authentication Tests (`auth.spec.js`)
- Login page display
- Invalid credentials handling
- Successful login
- Logout functionality
- Session management
- Redirect protection

### Dashboard Tests (`dashboard.spec.js`)
- Dashboard page display
- Statistics cards
- Navigation functionality
- User information display
- Cabang selector (for admin)
- Recent activities
- Responsive design

### Nasabah Management Tests (`nasabah.spec.js`)
- Nasabah list display
- Search and filtering
- Create new nasabah
- Form validation
- Data integrity
- Navigation between pages

### Pinjaman Management Tests (`pinjaman.spec.js`)
- Pinjaman list display
- Statistics overview
- Create new pinjaman
- Loan calculation preview
- Form validation
- Approval/rejection workflow
- Data formatting

### Angsuran Management Tests (`angsuran.spec.js`)
- Angsuran list display
- Payment processing
- Late payment handling
- Search and filtering
- Payment calculation
- WhatsApp integration

### API Integration Tests (`api-integration.spec.js`)
- API authentication
- Endpoint validation
- Data CRUD operations
- Error handling
- CORS support
- Response validation

## Test Data

Tests use the following default credentials:
- Username: `admin`
- Password: `admin123`
- API Token: `Bearer kewer-api-token-2024`
- Test Cabang ID: `1`

## Configuration

The Playwright configuration is in `playwright.config.js`:
- Base URL: `http://localhost/kewer-app`
- Test timeout: 30 seconds
- Retry on failure (CI only)
- Screenshots on failure
- Video recording on failure
- HTML reporting

## Reports

After running tests, HTML reports are generated in:
- `playwright-report/index.html` - Main test report
- `test-results/` - Raw test results and artifacts

## Debugging

### Debug specific test
```bash
npx playwright test --debug tests/auth.spec.js
```

### Run with trace viewer
```bash
npx playwright test --trace on
```

### Generate codegen for new tests
```bash
npx playwright codegen http://localhost/kewer-app
```

## Best Practices

1. **Test Isolation**: Each test is independent and can run alone
2. **Data Cleanup**: Tests clean up created data to avoid interference
3. **Realistic Scenarios**: Tests simulate real user workflows
4. **Error Coverage**: Tests include both success and failure scenarios
5. **Cross-browser**: Tests run on Chrome, Firefox, Safari, and mobile
6. **Responsive Design**: Tests include mobile viewport testing

## Troubleshooting

### Common Issues

1. **Server not running**: Ensure PHP server is running on localhost:8000
2. **Database connection**: Check database configuration in `config/database.php`
3. **Authentication failures**: Verify default user exists in database
4. **Timeout issues**: Increase timeout in `playwright.config.js` if needed
5. **Browser installation**: Run `npm run install` to install browsers

### Debug Steps

1. Check server logs for PHP errors
2. Verify database connection
3. Run tests in headed mode to see browser actions
4. Use debugging tools to pause execution
5. Check HTML report for detailed failure information

## Adding New Tests

1. Create new `.spec.js` file in `tests/` directory
2. Use existing tests as templates
3. Follow naming conventions and structure
4. Include both positive and negative test cases
5. Add proper assertions and error handling

## Continuous Integration

These tests can be integrated into CI/CD pipelines:

```yaml
# Example GitHub Actions workflow
- name: Run E2E Tests
  run: |
    cd tests/e2e
    npm install
    npm run install
    npm test
```

## Performance Considerations

- Tests run in parallel when possible
- Page load states are properly waited for
- Network idle conditions ensure complete loading
- Timeouts are configured appropriately for different environments

## Security Testing

- Authentication and authorization are tested
- Input validation is verified
- API security is tested
- Session management is validated
