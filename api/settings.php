<?php
/**
 * System Settings API
 * Endpoint: GET, PUT /api/settings.php
 */

require_once '../config/auth.php';
header('Content-Type: application/json');
setCorsHeaders();
$authUser = requireApiRole(['admin', 'hod']);


require_once '../config/database.php';
require_once '../config/helpers.php';

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Get all settings
            $stmt = $db->query("SELECT * FROM system_settings ORDER BY setting_key");
            $settings = $stmt->fetchAll();
            
            // Format for easier access
            $formattedSettings = [];
            foreach ($settings as $setting) {
                $formattedSettings[$setting['setting_key']] = [
                    'value' => $setting['setting_value'],
                    'type' => $setting['setting_type'],
                    'description' => $setting['description']
                ];
            }
            
            successResponse('Settings retrieved', $formattedSettings);
            break;
            
        case 'PUT':
        case 'POST':
            // Update settings
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input) || !is_array($input)) {
                errorResponse('Invalid settings data');
            }
            
            $db->beginTransaction();
            
            try {
                foreach ($input as $key => $value) {
                    // Update or insert setting
                    $stmt = $db->prepare("
                        INSERT INTO system_settings (setting_key, setting_value, setting_type, description) 
                        VALUES (?, ?, 'string', 'User defined setting')
                        ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
                    ");
                    $stmt->execute([$key, $value, $value]);
                }
                
                $db->commit();
                successResponse('Settings updated successfully');
                
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;
            
        default:
            errorResponse('Method not allowed', 405);
    }
    
} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    errorResponse('Error: ' . $e->getMessage(), 500);
}
?>

