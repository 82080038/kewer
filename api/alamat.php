<?php
/**
 * Address API Endpoint
 * 
 * Provides dynamic address data for dropdowns
 * Supports hierarchical loading: Province -> Regency -> District -> Village
 * This endpoint requires authentication (user must be logged in)
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/path.php';
require_once BASE_PATH . '/includes/functions.php';
requireLogin();

// Apply rate limiting for API requests
if (!checkRateLimit(RATE_LIMIT_PER_MINUTE, 60)) {
    apiError('Too many requests. Please try again later.', 429);
}

require_once __DIR__ . '/../includes/database_class.php';
require_once __DIR__ . '/../includes/alamat_helper.php';

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'provinces':
            // Get all provinces
            $provinces = getProvinces();
            apiSuccess($provinces);
            break;
            
        case 'regencies':
            // Get regencies by province ID
            $provinceId = $_GET['province_id'] ?? null;
            if (!$provinceId) {
                apiError('Province ID is required');
            }
            $regencies = getRegenciesByProvince($provinceId);
            apiSuccess($regencies);
            break;
            
        case 'districts':
            // Get districts by regency ID
            $regencyId = $_GET['regency_id'] ?? null;
            if (!$regencyId) {
                apiError('Regency ID is required');
            }
            $districts = getDistrictsByRegency($regencyId);
            apiSuccess($districts);
            break;
            
        case 'villages':
            // Get villages by district ID
            $districtId = $_GET['district_id'] ?? null;
            if (!$districtId) {
                apiError('District ID is required');
            }
            $villages = getVillagesByDistrict($districtId);
            apiSuccess($villages);
            break;
            
        default:
            apiError('Invalid action');
            break;
    }
} catch (Exception $e) {
    apiError($e->getMessage());
}
