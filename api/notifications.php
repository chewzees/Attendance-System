<?php
/**
 * Notifications API
 * Endpoint: GET/POST/PUT /api/notifications.php
 */

require_once '../config/auth.php';
setCorsHeaders();
$authUser = requireApiLogin();


require_once '../config/database.php';
require_once '../config/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    switch ($method) {
        case 'GET':
            // Get notifications
            $userId = $_GET['user_id'] ?? null;
            $isRead = $_GET['is_read'] ?? null;
            $priority = $_GET['priority'] ?? null;
            
            if (!$userId) {
                errorResponse('User ID is required');
            }
            
            // Get user
            $stmt = $db->prepare("SELECT id FROM users WHERE id = ? OR user_id = ?");
            $stmt->execute([$userId, $userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                errorResponse('User not found');
            }
            
            $query = "
                SELECT * FROM notifications 
                WHERE user_id = ?
            ";
            $params = [$user['id']];
            
            if ($isRead !== null) {
                $query .= " AND is_read = ?";
                $params[] = (int)$isRead;
            }
            
            if ($priority) {
                $query .= " AND priority = ?";
                $params[] = $priority;
            }
            
            $query .= " ORDER BY priority DESC, created_at DESC LIMIT 50";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $notifications = $stmt->fetchAll();
            
            // Get unread count
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$user['id']]);
            $unreadCount = $stmt->fetch()['count'];
            
            successResponse('Notifications retrieved', [
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
            break;
            
        case 'POST':
            // Create notification
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required = ['user_id', 'title', 'message'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    errorResponse("Missing required field: $field");
                }
            }
            
            // Get user
            $stmt = $db->prepare("SELECT id FROM users WHERE id = ? OR user_id = ?");
            $stmt->execute([$input['user_id'], $input['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                errorResponse('User not found');
            }
            
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type, priority, action_url)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $user['id'],
                sanitize($input['title']),
                sanitize($input['message']),
                $input['type'] ?? 'info',
                $input['priority'] ?? 'medium',
                sanitize($input['action_url'] ?? '')
            ]);
            
            successResponse('Notification created successfully', ['id' => $db->lastInsertId()]);
            break;
            
        case 'PUT':
            // Mark notification as read
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                errorResponse('Notification ID is required');
            }
            
            if (isset($input['mark_all_read']) && $input['mark_all_read']) {
                // Mark all as read for user
                $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
                $stmt->execute([$input['user_id']]);
                successResponse('All notifications marked as read');
            } else {
                // Mark single notification as read
                $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
                $stmt->execute([$input['id']]);
                successResponse('Notification marked as read');
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

