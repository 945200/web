<?php
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $studentName = $data['student_name'] ?? 'طالب';
    $studentId = $data['student_id'] ?? '';
    $gpa = floatval($data['gpa'] ?? 0);
    $interpretation = $data['interpretation'] ?? '';
    $totalCredits = floatval($data['total_credits'] ?? 0);
    $totalPoints = floatval($data['total_points'] ?? 0);
    $courses = $data['courses'] ?? [];
    
    if (empty($courses)) {
        echo json_encode(['success' => false, 'error' => 'لا توجد مواد لحفظها']);
        exit;
    }
    
    $recordId = saveGPARecord(
        $studentName,
        $studentId,
        $courses,
        $totalCredits,
        $totalPoints,
        $gpa,
        $interpretation
    );
    
    if ($recordId) {
        echo json_encode([
            'success' => true,
            'message' => 'تم حفظ السجل بنجاح!',
            'record_id' => $recordId
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'حدث خطأ أثناء حفظ السجل'
        ]);
    }
    
} else {
    echo json_encode(['success' => false, 'error' => 'طريقة طلب غير صحيحة']);
}
?>
