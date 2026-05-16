<?php
require_once '../../config/constants.php';
require_once '../../classes/reviews.php'; 

$reviewObj = new Review($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        exit(json_encode(['status' => 'error', 'message' => 'Lỗi bảo mật!']));
    }

    $review_id = (int)$_POST['review_id'];
    $reason = e(trim($_POST['reason'] ?? ''));
    if (empty($reason)) exit(json_encode(['status' => 'error']));
    $reporter_ip = $_SERVER['REMOTE_ADDR'];

    if ($review_id > 0 && !empty($reason)) {
        if ($reviewObj->reportReview($review_id, $reason, $reporter_ip)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống.']);
        }
    }
}
?>