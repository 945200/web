<?php
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id > 0) {
        $result = deleteRecord($id);
        echo json_encode(['success' => $result]);
    } else {
        echo json_encode(['success' => false, 'error' => 'معرف غير صالح']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'طريقة طلب غير صحيحة']);
}
?>
