<?php
// إعدادات الاتصال بقاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gpa_calculator');

// دالة للحصول على اتصال بقاعدة البيانات
function getDatabaseConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("فشل الاتصال: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
        
    } catch (Exception $e) {
        die("خطأ في قاعدة البيانات: " . $e->getMessage());
    }
}

// دالة لحفظ سجل GPA جديد
function saveGPARecord($studentName, $studentId, $courses, $totalCredits, $totalPoints, $gpa, $interpretation) {
    $conn = getDatabaseConnection();
    
    $courseDetails = json_encode($courses, JSON_UNESCAPED_UNICODE);
    
    $stmt = $conn->prepare("
        INSERT INTO gpa_records (student_name, student_id, total_credits, total_points, gpa, interpretation, course_details) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("ssddsss", $studentName, $studentId, $totalCredits, $totalPoints, $gpa, $interpretation, $courseDetails);
    $stmt->execute();
    $recordId = $stmt->insert_id;
    $stmt->close();
    
    $stmt = $conn->prepare("
        INSERT INTO courses (record_id, course_name, credits, grade_points, quality_points) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($courses as $course) {
        $stmt->bind_param("isidd", $recordId, $course['name'], $course['credits'], $course['grade'], $course['points']);
        $stmt->execute();
    }
    
    $stmt->close();
    $conn->close();
    
    return $recordId;
}

// دالة لجلب السجلات التاريخية
function getHistoricalRecords($limit = 20) {
    $conn = getDatabaseConnection();
    
    $result = $conn->query("
        SELECT * FROM gpa_records 
        ORDER BY calculation_date DESC 
        LIMIT $limit
    ");
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    
    $conn->close();
    return $records;
}

// دالة لحذف سجل
function deleteRecord($id) {
    $conn = getDatabaseConnection();
    
    $stmt = $conn->prepare("DELETE FROM gpa_records WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $result;
}
?>
