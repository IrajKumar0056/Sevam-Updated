<?php
/**
 * Supabase Integration Service for Sevam
 * 
 * Project ID: vvltacibwvrwtfhyesur
 * API Key: sb_publishable_uPIh-o3lSFGmjZsh7Z3cMA_hvwAjvRy
 * 
 * Automatically captures all user actions and synchronizes entity records
 * to Supabase tables (actions, users, food_providers, social_working_groups,
 * food_listings, food_requests, contact_messages).
 */

if (!defined('SUPABASE_PROJECT_ID')) {
    define('SUPABASE_PROJECT_ID', getenv('SUPABASE_PROJECT_ID') ?: 'vvltacibwvrwtfhyesur');
}
if (!defined('SUPABASE_URL')) {
    define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'https://vvltacibwvrwtfhyesur.supabase.co');
}
if (!defined('SUPABASE_API_KEY')) {
    define('SUPABASE_API_KEY', getenv('SUPABASE_API_KEY') ?: 'sb_publishable_uPIh-o3lSFGmjZsh7Z3cMA_hvwAjvRy');
}

/**
 * Execute an HTTP request to Supabase REST API via cURL or native streams.
 * Fault-tolerant & non-blocking: fails gracefully without interrupting user flow.
 *
 * @param string $endpoint Path relative to REST v1, e.g. 'actions' or 'users'
 * @param string $method HTTP method (GET, POST, PATCH, DELETE)
 * @param mixed $data Payload to encode as JSON
 * @param array $extraHeaders Optional additional headers
 * @return array ['success' => bool, 'code' => int, 'response' => mixed, 'error' => string|null]
 */
function supabase_request($endpoint, $method = 'POST', $data = null, $extraHeaders = []) {
    $url = rtrim(SUPABASE_URL, '/') . '/rest/v1/' . ltrim($endpoint, '/');
    $apiKey = SUPABASE_API_KEY;

    $headers = [
        "apikey: {$apiKey}",
        "Authorization: Bearer {$apiKey}",
        "Content-Type: application/json",
        "Prefer: return=representation,resolution=merge-duplicates"
    ];

    if (!empty($extraHeaders)) {
        $headers = array_merge($headers, $extraHeaders);
    }

    $raw = null;
    $statusCode = 0;

    // Use cURL if extension loaded
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $raw = curl_exec($ch);
        $statusCode = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $curlErr = curl_error($ch);
        curl_close($ch);
    } else {
        // Fallback to stream context
        $httpOptions = [
            'method' => strtoupper($method),
            'header' => implode("\r\n", $headers) . "\r\n",
            'timeout' => 5,
            'ignore_errors' => true
        ];

        if ($data !== null) {
            $httpOptions['content'] = json_encode($data);
        }

        $context = stream_context_create(['http' => $httpOptions]);
        $raw = @file_get_contents($url, false, $context);

        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $line) {
                if (preg_match('#^HTTP/\S+\s+(\d+)#i', $line, $m)) {
                    $statusCode = intval($m[1]);
                    break;
                }
            }
        }
    }

    $decoded = $raw ? json_decode($raw, true) : null;
    $success = ($statusCode >= 200 && $statusCode < 300);

    // Record to local activity log file for telemetry and audit
    supabase_record_local_log($endpoint, $method, $statusCode, $success, $decoded, $data);

    return [
        'success' => $success,
        'code' => $statusCode,
        'response' => $decoded,
        'raw' => $raw,
        'error' => $success ? null : ($decoded['message'] ?? ($curlErr ?? "HTTP {$statusCode}"))
    ];
}

/**
 * Record action to local log file for quick diagnostics and dashboard view
 */
function supabase_record_local_log($endpoint, $method, $statusCode, $success, $response, $payload = null) {
    $logDir = __DIR__ . '/../tmp';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/supabase_sync.log';
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'endpoint' => $endpoint,
        'method' => $method,
        'status_code' => $statusCode,
        'success' => $success,
        'summary' => is_array($response) ? ($response['message'] ?? 'OK') : ($success ? 'Success' : 'HTTP ' . $statusCode),
        'payload' => $payload
    ];
    @file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * Record an action event into Supabase backend
 *
 * @param string $action Action name, e.g. 'user_registered', 'user_login', 'food_listed', 'food_requested'
 * @param array $details Detailed contextual payload
 * @param string|null $entityType E.g. 'user', 'food_listing', 'food_request', 'contact', 'auth'
 * @param string|int|null $entityId ID of the affected resource
 * @return array
 */
function supabase_record_action($action, $details = [], $entityType = null, $entityId = null) {
    $userId = $_SESSION['user_id'] ?? ($details['user_id'] ?? null);
    $userRole = $_SESSION['role'] ?? ($details['role'] ?? 'guest');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $payload = [
        'action' => $action,
        'entity_type' => $entityType ?: 'general',
        'entity_id' => $entityId !== null ? strval($entityId) : null,
        'user_id' => $userId !== null ? intval($userId) : null,
        'user_role' => $userRole,
        'details' => $details,
        'ip_address' => $ip,
        'created_at' => date('c')
    ];

    // Try posting to 'actions' table
    $res = supabase_request('actions', 'POST', $payload);

    // Also attempt 'activity_logs' for compatibility if actions is not yet migrated
    if (!$res['success'] && ($res['code'] === 404)) {
        supabase_request('activity_logs', 'POST', $payload);
    }

    return $res;
}

/**
 * Synchronize User entity to Supabase
 */
function supabase_sync_user($userData) {
    $userId = intval($userData['id'] ?? 0);
    $payload = [
        'username' => $userData['username'] ?? '',
        'email' => $userData['email'] ?? '',
        'role' => $userData['role'] ?? 'guest',
        'status' => $userData['status'] ?? 'active',
        'created_at' => $userData['created_at'] ?? date('c')
    ];
    if ($userId > 0) {
        $payload['id'] = $userId;
    }
    return supabase_request('users', 'POST', $payload);
}

/**
 * Synchronize Food Provider Profile to Supabase
 */
function supabase_sync_provider($providerData) {
    $userId = intval($providerData['user_id'] ?? 0);
    $payload = [
        'user_id' => $userId,
        'business_name' => $providerData['business_name'] ?? '',
        'owner_name' => $providerData['owner_name'] ?? '',
        'phone' => $providerData['phone'] ?? '',
        'address' => $providerData['address'] ?? '',
        'city' => $providerData['city'] ?? '',
        'state' => $providerData['state'] ?? '',
        'business_type' => $providerData['business_type'] ?? '',
        'created_at' => $providerData['created_at'] ?? date('c')
    ];
    if (isset($providerData['fssai_status'])) {
        $payload['fssai_status'] = $providerData['fssai_status'];
    }
    if (isset($providerData['fssai_number'])) {
        $payload['fssai_number'] = $providerData['fssai_number'];
    }

    // If this user already has a provider profile in Supabase, update that specific record
    if ($userId > 0) {
        $existing = supabase_request("food_providers?user_id=eq.{$userId}&select=id", 'GET');
        if ($existing['success'] && !empty($existing['response']) && isset($existing['response'][0]['id'])) {
            $existingId = $existing['response'][0]['id'];
            return supabase_request("food_providers?id=eq.{$existingId}", 'PATCH', $payload);
        }
    }

    // Only include positive primary ID if passed, otherwise let Supabase auto-increment
    $profileId = intval($providerData['id'] ?? 0);
    if ($profileId > 0) {
        $payload['id'] = $profileId;
    }

    return supabase_request('food_providers', 'POST', $payload);
}

/**
 * Synchronize Social Working Group Profile to Supabase
 */
function supabase_sync_group($groupData) {
    $userId = intval($groupData['user_id'] ?? 0);
    $payload = [
        'user_id' => $userId,
        'group_name' => $groupData['group_name'] ?? '',
        'representative_name' => $groupData['representative_name'] ?? '',
        'phone' => $groupData['phone'] ?? '',
        'address' => $groupData['address'] ?? '',
        'city' => $groupData['city'] ?? '',
        'state' => $groupData['state'] ?? '',
        'organization_type' => $groupData['organization_type'] ?? '',
        'darpan_id' => $groupData['darpan_id'] ?? null,
        'created_at' => $groupData['created_at'] ?? date('c')
    ];

    // If this user already has a group profile in Supabase, update that specific record
    if ($userId > 0) {
        $existing = supabase_request("social_working_groups?user_id=eq.{$userId}&select=id", 'GET');
        if ($existing['success'] && !empty($existing['response']) && isset($existing['response'][0]['id'])) {
            $existingId = $existing['response'][0]['id'];
            return supabase_request("social_working_groups?id=eq.{$existingId}", 'PATCH', $payload);
        }
    }

    $profileId = intval($groupData['id'] ?? 0);
    if ($profileId > 0) {
        $payload['id'] = $profileId;
    }

    return supabase_request('social_working_groups', 'POST', $payload);
}

/**
 * Synchronize Food Listing entity to Supabase
 */
function supabase_sync_food_listing($listingData) {
    $payload = [
        'provider_id' => intval($listingData['provider_id'] ?? 0),
        'food_name' => $listingData['food_name'] ?? '',
        'food_type' => $listingData['food_type'] ?? 'Veg',
        'quantity' => floatval($listingData['quantity'] ?? 0),
        'available_quantity' => floatval($listingData['available_quantity'] ?? 0),
        'quantity_unit' => $listingData['quantity_unit'] ?? 'kg',
        'price' => floatval($listingData['price'] ?? 0),
        'available_date' => $listingData['available_date'] ?? date('Y-m-d'),
        'available_start_time' => $listingData['available_start_time'] ?? '',
        'available_end_time' => $listingData['available_end_time'] ?? '',
        'expiry_date' => $listingData['expiry_date'] ?? date('Y-m-d'),
        'pickup_info' => $listingData['pickup_info'] ?? '',
        'status' => $listingData['status'] ?? 'Available',
        'created_at' => $listingData['created_at'] ?? date('c')
    ];
    $listingId = intval($listingData['id'] ?? 0);
    if ($listingId > 0) {
        $payload['id'] = $listingId;
    }
    return supabase_request('food_listings', 'POST', $payload);
}

/**
 * Update Food Listing Status in Supabase
 */
function supabase_update_food_listing_status($listingId, $status, $availableQuantity = null) {
    $payload = ['status' => $status];
    if ($availableQuantity !== null) {
        $payload['available_quantity'] = floatval($availableQuantity);
    }
    return supabase_request("food_listings?id=eq." . intval($listingId), 'PATCH', $payload);
}

/**
 * Synchronize Food Request entity to Supabase
 */
function supabase_sync_food_request($reqData) {
    $payload = [
        'food_id' => intval($reqData['food_id'] ?? 0),
        'group_id' => intval($reqData['group_id'] ?? 0),
        'requested_quantity' => floatval($reqData['requested_quantity'] ?? 0),
        'requested_date' => $reqData['requested_date'] ?? date('Y-m-d'),
        'requested_time' => $reqData['requested_time'] ?? '',
        'status' => $reqData['status'] ?? 'Pending',
        'message' => $reqData['message'] ?? '',
        'created_at' => date('c')
    ];
    $reqId = intval($reqData['id'] ?? 0);
    if ($reqId > 0) {
        $payload['id'] = $reqId;
    }
    return supabase_request('food_requests', 'POST', $payload);
}

/**
 * Update Food Request Status in Supabase
 */
function supabase_update_food_request_status($requestId, $status) {
    return supabase_request("food_requests?id=eq." . intval($requestId), 'PATCH', [
        'status' => $status
    ]);
}

/**
 * Synchronize Contact Message to Supabase
 */
function supabase_sync_contact_message($msgData) {
    $payload = [
        'name' => $msgData['name'] ?? '',
        'email' => $msgData['email'] ?? '',
        'phone' => $msgData['phone'] ?? '',
        'subject' => $msgData['subject'] ?? '',
        'message' => $msgData['message'] ?? '',
        'created_at' => date('c')
    ];
    $msgId = intval($msgData['id'] ?? 0);
    if ($msgId > 0) {
        $payload['id'] = $msgId;
    }
    return supabase_request('contact_messages', 'POST', $payload);
}

/**
 * Test connectivity to the Supabase instance
 */
function supabase_test_connection() {
    $url = rtrim(SUPABASE_URL, '/') . '/auth/v1/health';
    $apiKey = SUPABASE_API_KEY;

    $headers = [
        "apikey: {$apiKey}",
        "Authorization: Bearer {$apiKey}"
    ];

    $raw = null;
    $code = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $raw = curl_exec($ch);
        $code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers) . "\r\n",
                'timeout' => 5,
                'ignore_errors' => true
            ]
        ]);
        $raw = @file_get_contents($url, false, $context);
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $line) {
                if (preg_match('#^HTTP/\S+\s+(\d+)#i', $line, $m)) {
                    $code = intval($m[1]);
                    break;
                }
            }
        }
    }

    $connected = ($code === 200);
    return [
        'connected' => $connected,
        'status_code' => $code,
        'project_id' => SUPABASE_PROJECT_ID,
        'endpoint' => SUPABASE_URL,
        'api_key_masked' => substr(SUPABASE_API_KEY, 0, 16) . '...' . substr(SUPABASE_API_KEY, -6),
        'raw_response' => $raw
    ];
}

/**
 * Bulk synchronize all existing local MariaDB data to Supabase
 *
 * @param PDO $pdo
 * @return array Sync statistics
 */
function supabase_sync_all_existing_data($pdo) {
    $stats = [
        'users' => 0,
        'providers' => 0,
        'groups' => 0,
        'listings' => 0,
        'requests' => 0,
        'messages' => 0,
        'errors' => []
    ];

    try {
        // 1. Sync Users
        $users = $pdo->query("SELECT id, username, email, role, status, created_at FROM users")->fetchAll();
        foreach ($users as $u) {
            $res = supabase_sync_user($u);
            if ($res['success']) {
                $stats['users']++;
            } else {
                $stats['errors'][] = "User #{$u['id']}: " . ($res['error'] ?? 'Unknown');
            }
        }

        // 2. Sync Providers
        $providers = $pdo->query("SELECT * FROM food_providers")->fetchAll();
        foreach ($providers as $p) {
            $res = supabase_sync_provider($p);
            if ($res['success']) {
                $stats['providers']++;
            }
        }

        // 3. Sync Groups
        $groups = $pdo->query("SELECT * FROM social_working_groups")->fetchAll();
        foreach ($groups as $g) {
            $res = supabase_sync_group($g);
            if ($res['success']) {
                $stats['groups']++;
            }
        }

        // 4. Sync Food Listings
        $listings = $pdo->query("SELECT * FROM food_listings")->fetchAll();
        foreach ($listings as $l) {
            $res = supabase_sync_food_listing($l);
            if ($res['success']) {
                $stats['listings']++;
            }
        }

        // 5. Sync Food Requests
        $requests = $pdo->query("SELECT * FROM food_requests")->fetchAll();
        foreach ($requests as $r) {
            $res = supabase_sync_food_request($r);
            if ($res['success']) {
                $stats['requests']++;
            }
        }

        // 6. Sync Contact Messages
        $messages = $pdo->query("SELECT * FROM contact_messages")->fetchAll();
        foreach ($messages as $m) {
            $res = supabase_sync_contact_message($m);
            if ($res['success']) {
                $stats['messages']++;
            }
        }

        // Record sync action
        supabase_record_action('manual_database_sync', [
            'total_users' => $stats['users'],
            'total_listings' => $stats['listings'],
            'total_requests' => $stats['requests']
        ], 'system', 0);

    } catch (Exception $e) {
        $stats['errors'][] = $e->getMessage();
    }

    return $stats;
}

/**
 * Retrieve recent local sync log entries
 */
function supabase_get_recent_logs($limit = 25) {
    $logFile = __DIR__ . '/../tmp/supabase_sync.log';
    if (!file_exists($logFile)) {
        return [];
    }
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) {
        return [];
    }
    $lines = array_reverse(array_slice($lines, -$limit));
    $parsed = [];
    foreach ($lines as $line) {
        $data = json_decode($line, true);
        if ($data) {
            $parsed[] = $data;
        }
    }
    return $parsed;
}
