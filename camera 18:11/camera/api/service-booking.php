<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../classes/ServiceBooking.php';

// Check if POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$fullName = $_POST['full_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$serviceId = $_POST['service_id'] ?? 0;
$notes = $_POST['notes'] ?? '';
$appointmentDate = $_POST['appointment_date'] ?? '';

// Validate required fields
if (empty($fullName) || empty($email) || empty($phone) || empty($serviceId)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
    exit;
}

// Validate phone
if (!preg_match('/^[\d\s\-\+\(\)]{10,}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ']);
    exit;
}

try {
    $bookingModel = new ServiceBooking();
    
    $bookingData = [
        'service_id' => $serviceId,
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'notes' => $notes,
        'appointment_date' => $appointmentDate,
        'user_id' => null
    ];
    
    // Check if user is logged in
    session_start();
    if (isset($_SESSION['user_id'])) {
        $bookingData['user_id'] = $_SESSION['user_id'];
    }
    
    // Save booking
    $result = $bookingModel->createBooking($bookingData);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Đặt dịch vụ thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.',
            'booking_id' => $result
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()]);
}
?>
