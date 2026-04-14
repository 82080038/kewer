<?php
/**
 * Frontend-to-Backend (F2E) Testing for Kewer System
 * This script tests API endpoints functionality
 */

require_once '../includes/functions.php';

// Test configuration
define('API_BASE_URL', 'http://localhost/kewer-app/api');
define('API_TOKEN', 'Bearer kewer-api-token-2024');
define('TEST_CABANG_ID', 1);

// Test results
$test_results = [];
$total_tests = 0;
$passed_tests = 0;

// Helper function to make API requests
function makeRequest($endpoint, $method = 'GET', $data = null, $params = []) {
    $url = API_BASE_URL . '/' . $endpoint;
    
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . API_TOKEN
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status_code' => $http_code,
        'response' => json_decode($response, true),
        'raw_response' => $response
    ];
}

// Helper function to run test
function runTest($test_name, $endpoint, $method = 'GET', $data = null, $params = [], $expected_status = 200, $expected_keys = []) {
    global $test_results, $total_tests, $passed_tests;
    
    $total_tests++;
    
    echo "Testing: $test_name\n";
    echo "Endpoint: $method $endpoint\n";
    
    $result = makeRequest($endpoint, $method, $data, $params);
    
    $test_passed = true;
    $error_messages = [];
    
    // Check HTTP status
    if ($result['status_code'] !== $expected_status) {
        $test_passed = false;
        $error_messages[] = "Expected status $expected_status, got {$result['status_code']}";
    }
    
    // Check response structure
    if ($expected_status === 200 && $result['response']) {
        foreach ($expected_keys as $key) {
            if (!isset($result['response'][$key])) {
                $test_passed = false;
                $error_messages[] = "Missing key: $key";
            }
        }
    }
    
    // Check success flag for successful responses
    if ($expected_status === 200 && isset($result['response']['success']) && !$result['response']['success']) {
        $test_passed = false;
        $error_messages[] = "API returned success=false: " . ($result['response']['error'] ?? 'Unknown error');
    }
    
    if ($test_passed) {
        $passed_tests++;
        echo "Status: PASSED\n";
    } else {
        echo "Status: FAILED\n";
        echo "Errors: " . implode(', ', $error_messages) . "\n";
        echo "Response: " . json_encode($result['response']) . "\n";
    }
    
    echo str_repeat('-', 50) . "\n";
    
    $test_results[] = [
        'name' => $test_name,
        'passed' => $test_passed,
        'status_code' => $result['status_code'],
        'errors' => $error_messages
    ];
    
    return $result['response'];
}

// Start testing
echo "=== KEWER - Frontend-to-Backend Testing ===\n";
echo "API Base URL: " . API_BASE_URL . "\n";
echo "Test Cabang ID: " . TEST_CABANG_ID . "\n";
echo str_repeat('=', 60) . "\n\n";

// Test 1: Dashboard API
runTest('Dashboard Statistics', 'dashboard', 'GET', null, ['cabang_id' => TEST_CABANG_ID], 200, ['success', 'data']);

// Test 2: Get Nasabah List
runTest('Get Nasabah List', 'nasabah', 'GET', null, ['cabang_id' => TEST_CABANG_ID], 200, ['success', 'data']);

// Test 3: Create Nasabah
$test_nasabah_data = [
    'nama' => 'Test Nasabah F2E',
    'alamat' => 'Alamat Test F2E',
    'ktp' => '1234567890123456',
    'telp' => '08123456789',
    'jenis_usaha' => 'Test Usaha',
    'lokasi_pasar' => 'Test Lokasi'
];
$nasabah_response = runTest('Create Nasabah', 'nasabah', 'POST', $test_nasabah_data, ['cabang_id' => TEST_CABANG_ID], 200, ['success', 'data']);

// Test 4: Get Pinjaman List
runTest('Get Pinjaman List', 'pinjaman', 'GET', null, ['cabang_id' => TEST_CABANG_ID], 200, ['success', 'data']);

// Test 5: Create Pinjaman (if nasabah was created successfully)
if ($nasabah_response && isset($nasabah_response['data']['id'])) {
    $test_pinjaman_data = [
        'nasabah_id' => $nasabah_response['data']['id'],
        'plafon' => 1000000,
        'tenor' => 6,
        'bunga_per_bulan' => 2.5,
        'tanggal_akad' => date('Y-m-d'),
        'tujuan_pinjaman' => 'Modal usaha test',
        'jaminan' => 'Test jaminan'
    ];
    
    $pinjaman_response = runTest('Create Pinjaman', 'pinjaman', 'POST', $test_pinjaman_data, ['cabang_id' => TEST_CABANG_ID], 200, ['success', 'data']);
    
    // Test 6: Approve Pinjaman
    if ($pinjaman_response && isset($pinjaman_response['data']['id'])) {
        runTest('Approve Pinjaman', 'pinjaman', 'PUT', null, ['cabang_id' => TEST_CABANG_ID, 'id' => $pinjaman_response['data']['id'], 'action' => 'approve'], 200, ['success']);
    }
}

// Test 7: Invalid API Token
echo "Testing: Invalid API Token\n";
$invalid_result = makeRequest('dashboard', 'GET', null, ['cabang_id' => TEST_CABANG_ID]);
if ($invalid_result['status_code'] === 401) {
    echo "Status: PASSED (Correctly rejected invalid token)\n";
    $passed_tests++;
} else {
    echo "Status: FAILED (Should reject invalid token)\n";
}
echo str_repeat('-', 50) . "\n";

// Test 8: Missing cabang_id parameter
echo "Testing: Missing cabang_id Parameter\n";
$missing_param_result = makeRequest('dashboard', 'GET');
if ($missing_param_result['status_code'] === 400) {
    echo "Status: PASSED (Correctly rejected missing parameter)\n";
    $passed_tests++;
} else {
    echo "Status: FAILED (Should reject missing parameter)\n";
}
echo str_repeat('-', 50) . "\n";

// Test 9: Invalid endpoint
echo "Testing: Invalid Endpoint\n";
$invalid_endpoint_result = makeRequest('invalid_endpoint', 'GET');
if ($invalid_endpoint_result['status_code'] === 404) {
    echo "Status: PASSED (Correctly returned 404)\n";
    $passed_tests++;
} else {
    echo "Status: FAILED (Should return 404)\n";
}
echo str_repeat('-', 50) . "\n";

// Test 10: Invalid HTTP method
echo "Testing: Invalid HTTP Method\n";
$invalid_method_result = makeRequest('nasabah', 'DELETE');
if ($invalid_method_result['status_code'] === 405) {
    echo "Status: PASSED (Correctly rejected invalid method)\n";
    $passed_tests++;
} else {
    echo "Status: FAILED (Should reject invalid method)\n";
}
echo str_repeat('-', 50) . "\n";

// Print summary
echo "\n=== TEST SUMMARY ===\n";
echo "Total Tests: $total_tests\n";
echo "Passed: $passed_tests\n";
echo "Failed: " . ($total_tests - $passed_tests) . "\n";
echo "Success Rate: " . round(($passed_tests / $total_tests) * 100, 2) . "%\n";

// Print failed tests details
$failed_tests = array_filter($test_results, function($result) {
    return !$result['passed'];
});

if (!empty($failed_tests)) {
    echo "\n=== FAILED TESTS DETAILS ===\n";
    foreach ($failed_tests as $test) {
        echo "Test: {$test['name']}\n";
        echo "Status Code: {$test['status_code']}\n";
        echo "Errors: " . implode(', ', $test['errors']) . "\n";
        echo str_repeat('-', 30) . "\n";
    }
}

echo "\n=== API CONNECTIVITY TEST ===\n";

// Test database connectivity
try {
    $test_query = query("SELECT 1 as test");
    if ($test_query && $test_query[0]['test'] === 1) {
        echo "Database Connection: PASSED\n";
        $passed_tests++;
        $total_tests++;
    } else {
        echo "Database Connection: FAILED\n";
    }
} catch (Exception $e) {
    echo "Database Connection: FAILED - " . $e->getMessage() . "\n";
}

// Test required functions
$required_functions = ['generateKode', 'calculateLoan', 'formatRupiah', 'validateKTP', 'validatePhone'];
echo "\n=== FUNCTION TESTS ===\n";

foreach ($required_functions as $func) {
    $total_tests++;
    if (function_exists($func)) {
        echo "Function $func: EXISTS\n";
        $passed_tests++;
    } else {
        echo "Function $func: MISSING\n";
    }
}

// Final summary
echo "\n=== FINAL SUMMARY ===\n";
echo "Total Tests: $total_tests\n";
echo "Passed: $passed_tests\n";
echo "Failed: " . ($total_tests - $passed_tests) . "\n";
echo "Success Rate: " . round(($passed_tests / $total_tests) * 100, 2) . "%\n";

if ($passed_tests === $total_tests) {
    echo "\nAll tests PASSED! The application is ready for production.\n";
} else {
    echo "\nSome tests FAILED. Please review and fix the issues before deploying to production.\n";
}

// Cleanup test data (optional)
if ($nasabah_response && isset($nasabah_response['data']['id'])) {
    query("DELETE FROM nasabah WHERE id = ?", [$nasabah_response['data']['id']]);
    echo "\nTest data cleaned up.\n";
}
?>
