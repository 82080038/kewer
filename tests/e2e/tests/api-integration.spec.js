const { test, expect } = require('@playwright/test');

test.describe('API Integration Tests', () => {
  const API_BASE_URL = 'http://localhost/kewer-app/api';
  const API_TOKEN = 'Bearer kewer-api-token-2024';
  const TEST_CABANG_ID = 1;

  test('should authenticate API requests', async ({ request }) => {
    const response = await request.get(`${API_BASE_URL}/dashboard?cabang_id=${TEST_CABANG_ID}`, {
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.data).toHaveProperty('summary');
    expect(data.data).toHaveProperty('recent_activities');
  });

  test('should reject requests without token', async ({ request }) => {
    const response = await request.get(`${API_BASE_URL}/dashboard?cabang_id=${TEST_CABANG_ID}`);

    expect(response.status()).toBe(401);
    const data = await response.json();
    expect(data.error).toBe('Unauthorized');
  });

  test('should reject requests with invalid token', async ({ request }) => {
    const response = await request.get(`${API_BASE_URL}/dashboard?cabang_id=${TEST_CABANG_ID}`, {
      headers: {
        'Authorization': 'Bearer invalid-token'
      }
    });

    expect(response.status()).toBe(401);
    const data = await response.json();
    expect(data.error).toBe('Unauthorized');
  });

  test('should get nasabah list via API', async ({ request }) => {
    const response = await request.get(`${API_BASE_URL}/nasabah?cabang_id=${TEST_CABANG_ID}`, {
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.data).toBeInstanceOf(Array);
    expect(data).toHaveProperty('total');
  });

  test('should create nasabah via API', async ({ request }) => {
    const nasabahData = {
      nama: 'Test Nasabah API',
      alamat: 'Alamat Test API',
      ktp: '1234567890123456',
      telp: '081234567890',
      jenis_usaha: 'Test Usaha',
      lokasi_pasar: 'Test Lokasi'
    };

    const response = await request.post(`${API_BASE_URL}/nasabah?cabang_id=${TEST_CABANG_ID}`, {
      headers: {
        'Authorization': API_TOKEN,
        'Content-Type': 'application/json'
      },
      data: nasabahData
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.data).toHaveProperty('id');
    expect(data.data.nama).toBe(nasabahData.nama);

    // Cleanup: delete the created nasabah
    if (data.data.id) {
      await request.delete(`${API_BASE_URL}/nasabah?id=${data.data.id}&cabang_id=${TEST_CABANG_ID}`, {
        headers: {
          'Authorization': API_TOKEN
        }
      });
    }
  });

  test('should validate nasabah data via API', async ({ request }) => {
    const invalidData = {
      nama: 'Test Nasabah',
      alamat: 'Alamat Test',
      ktp: '123', // Invalid KTP format
      telp: '081234567890'
    };

    const response = await request.post(`${API_BASE_URL}/nasabah?cabang_id=${TEST_CABANG_ID}`, {
      headers: {
        'Authorization': API_TOKEN,
        'Content-Type': 'application/json'
      },
      data: invalidData
    });

    expect(response.status()).toBe(400);
    const data = await response.json();
    expect(data.success).toBe(false);
    expect(data.error).toBe('Format KTP tidak valid');
  });

  test('should get pinjaman list via API', async ({ request }) => {
    const response = await request.get(`${API_BASE_URL}/pinjaman?cabang_id=${TEST_CABANG_ID}`, {
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.data).toBeInstanceOf(Array);
    expect(data).toHaveProperty('total');
  });

  test('should handle missing cabang_id parameter', async ({ request }) => {
    const response = await request.get(`${API_BASE_URL}/dashboard`, {
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(400);
    const data = await response.json();
    expect(data.error).toBe('cabang_id parameter required');
  });

  test('should handle invalid endpoint', async ({ request }) => {
    const response = await request.get(`${API_BASE_URL}/invalid_endpoint?cabang_id=${TEST_CABANG_ID}`, {
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(404);
    const data = await response.json();
    expect(data.error).toBe('Endpoint not found');
  });

  test('should handle invalid HTTP method', async ({ request }) => {
    const response = await request.delete(`${API_BASE_URL}/nasabah?cabang_id=${TEST_CABANG_ID}`, {
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(405);
    const data = await response.json();
    expect(data.error).toBe('Method not allowed');
  });

  test('should search nasabah via API', async ({ request }) => {
    const response = await request.get(`${API_BASE_URL}/nasabah?cabang_id=${TEST_CABANG_ID}&search=test`, {
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.data).toBeInstanceOf(Array);
  });

  test('should filter nasabah by status via API', async ({ request }) => {
    const response = await request.get(`${API_BASE_URL}/nasabah?cabang_id=${TEST_CABANG_ID}&status=aktif`, {
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.data).toBeInstanceOf(Array);
  });

  test('should return CORS headers', async ({ request }) => {
    const response = await request.get(`${API_BASE_URL}/dashboard?cabang_id=${TEST_CABANG_ID}`, {
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(200);
    expect(response.headers()['access-control-allow-origin']).toBe('*');
    expect(response.headers()['access-control-allow-methods']).toContain('GET');
    expect(response.headers()['access-control-allow-headers']).toContain('Content-Type');
  });

  test('should handle OPTIONS request for CORS', async ({ request }) => {
    const response = await request.fetch(`${API_BASE_URL}/dashboard?cabang_id=${TEST_CABANG_ID}`, {
      method: 'OPTIONS',
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(200);
    expect(response.headers()['access-control-allow-origin']).toBe('*');
  });

  test('should return proper JSON content type', async ({ request }) => {
    const response = await request.get(`${API_BASE_URL}/dashboard?cabang_id=${TEST_CABANG_ID}`, {
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(200);
    expect(response.headers()['content-type']).toContain('application/json');
  });

  test('should handle server errors gracefully', async ({ page }) => {
    // This test simulates a server error by calling an endpoint that might fail
    await page.goto('/tests/api_test_client.html');
    
    // Run tests to see error handling
    await page.fill('#apiToken', 'Bearer kewer-api-token-2024');
    await page.click('button:has-text("Run All Tests")');
    
    // Wait for tests to complete
    await page.waitForTimeout(5000);
    
    // Check if tests completed
    const totalTests = await page.locator('#totalTests').textContent();
    expect(parseInt(totalTests)).toBeGreaterThan(0);
  });

  test('should validate API response structure', async ({ request }) => {
    const response = await request.get(`${API_BASE_URL}/dashboard?cabang_id=${TEST_CABANG_ID}`, {
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    
    // Check response structure
    expect(data).toHaveProperty('success');
    expect(data).toHaveProperty('data');
    expect(data.data).toHaveProperty('summary');
    expect(data.data).toHaveProperty('recent_activities');
    expect(data.data).toHaveProperty('loan_statistics');
    expect(data.data).toHaveProperty('installment_statistics');
    
    // Check summary structure
    expect(data.data.summary).toHaveProperty('total_nasabah');
    expect(data.data.summary).toHaveProperty('total_pinjaman');
    expect(data.data.summary).toHaveProperty('outstanding');
    expect(data.data.summary).toHaveProperty('late_payments');
  });

  test('should handle concurrent requests', async ({ request }) => {
    // Make multiple concurrent requests
    const requests = [];
    for (let i = 0; i < 5; i++) {
      requests.push(
        request.get(`${API_BASE_URL}/dashboard?cabang_id=${TEST_CABANG_ID}`, {
          headers: {
            'Authorization': API_TOKEN
          }
        })
      );
    }

    const responses = await Promise.all(requests);
    
    // All requests should succeed
    responses.forEach(response => {
      expect(response.status()).toBe(200);
    });
  });

  test('should handle large data responses', async ({ request }) => {
    // This test checks if the API can handle requests that might return large datasets
    const response = await request.get(`${API_BASE_URL}/nasabah?cabang_id=${TEST_CABANG_ID}`, {
      headers: {
        'Authorization': API_TOKEN
      }
    });

    expect(response.status()).toBe(200);
    const data = await response.json();
    expect(data.success).toBe(true);
    
    // Should not take too long to respond
    const responseTime = await response.timing();
    expect(responseTime.responseEnd).toBeLessThan(5000); // Less than 5 seconds
  });
});
