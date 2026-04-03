<?php
// ========== 1. إعداد قاعدة البيانات ==========
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'gpa_calculator';

$conn = new mysqli($host, $user, $pass);
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

$conn->query("CREATE TABLE IF NOT EXISTS gpa_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100),
    gpa DECIMAL(5,2),
    interpretation VARCHAR(50),
    courses_data TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ========== 2. معالجة AJAX ==========
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_record']) && !isset($_POST['get_history'])) {
    header('Content-Type: application/json');
    
    $courses = $_POST['course'] ?? [];
    $credits = $_POST['credits'] ?? [];
    $grades = $_POST['grade'] ?? [];
    
    $totalPoints = 0;
    $totalCredits = 0;
    $validCourses = [];
    
    for ($i = 0; $i < count($courses); $i++) {
        $courseName = htmlspecialchars(trim($courses[$i]));
        $creditHours = floatval($credits[$i]);
        $gradePoints = floatval($grades[$i]);
        
        if ($creditHours > 0 && !empty($courseName)) {
            $coursePoints = $gradePoints * $creditHours;
            $totalPoints += $coursePoints;
            $totalCredits += $creditHours;
            $validCourses[] = [
                'name' => $courseName,
                'credits' => $creditHours,
                'grade' => $gradePoints,
                'points' => $coursePoints
            ];
        }
    }
    
    if ($totalCredits > 0) {
        $gpa = $totalPoints / $totalCredits;
        
        if ($gpa >= 3.7) {
            $interpretation = "امتياز";
            $gpaClass = "success";
        } elseif ($gpa >= 3.0) {
            $interpretation = "جيد جداً";
            $gpaClass = "info";
        } elseif ($gpa >= 2.0) {
            $interpretation = "جيد";
            $gpaClass = "warning";
        } else {
            $interpretation = "راسب";
            $gpaClass = "danger";
        }
        
        $tableHtml = '<table class="table table-striped">';
        $tableHtml .= '<thead class="table-primary">';
        $tableHtml .= '<tr><th>اسم المادة</th><th>الساعات</th><th>نقاط الدرجة</th><th>النقاط الموزونة</th></tr>';
        $tableHtml .= '</thead><tbody>';
        
        foreach ($validCourses as $course) {
            $tableHtml .= '<tr>';
            $tableHtml .= '<td>' . $course['name'] . '</td>';
            $tableHtml .= '<td>' . $course['credits'] . '</td>';
            $tableHtml .= '<td>' . $course['grade'] . '</td>';
            $tableHtml .= '<td>' . number_format($course['points'], 2) . '</td>';
            $tableHtml .= '</tr>';
        }
        
        $tableHtml .= '<tr class="table-secondary"><td colspan="2">الإجمالي:</td>';
        $tableHtml .= '<td>' . number_format($totalCredits, 0) . ' ساعة</td>';
        $tableHtml .= '<td>' . number_format($totalPoints, 2) . ' نقطة</td>';
        $tableHtml .= '</tr>';
        $tableHtml .= '</tbody></table>';
        
        $message = '<div class="alert alert-' . $gpaClass . ' mt-3">';
        $message .= '<h4>المعدل التراكمي: ' . number_format($gpa, 2) . '</h4>';
        $message .= '<p>التقدير: <strong>' . $interpretation . '</strong></p>';
        $message .= '</div>';
        
        echo json_encode([
            'success' => true,
            'table' => $tableHtml,
            'message' => $message,
            'gpa' => number_format($gpa, 2),
            'interpretation' => $interpretation,
            'courses' => $validCourses
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'لا توجد مواد صحيحة']);
    }
    exit;
}


// ========== 3. حفظ السجل ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_record'])) {
    $studentName = $_POST['student_name'] ?? 'طالب';
    $gpa = $_POST['gpa'] ?? 0;
    $interpretation = $_POST['interpretation'] ?? '';
    $coursesData = $_POST['courses_data'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO gpa_records (student_name, gpa, interpretation, courses_data) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $studentName, $gpa, $interpretation, $coursesData);
    $stmt->execute();
    $stmt->close();
    
    echo "<script>alert('تم حفظ السجل!'); window.location.href='';</script>";
    exit;
}

// ========== 4. جلب السجلات ==========
if ($isAjax && isset($_POST['get_history'])) {
    header('Content-Type: application/json');
    $result = $conn->query("SELECT * FROM gpa_records ORDER BY created_at DESC LIMIT 10");
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    echo json_encode($records);
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>حاسبة المعدل التراكمي - المرحلة 4</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 40px 0; }
        .main-card { border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; text-align: center; }
        .course-row { background: #f8f9fa; border-radius: 10px; padding: 15px; margin-bottom: 15px; border: 1px solid #e9ecef; }
        .loading { display: inline-block; width: 20px; height: 20px; border: 3px solid #f3f3f3; border-top: 3px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .btn-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; }
        .btn-gradient:hover { background: linear-gradient(135deg, #5a67d8 0%, #6b46c0 100%); color: white; }
        .history-card { cursor: pointer; transition: transform 0.2s; }
        .history-card:hover { transform: translateY(-5px); }
        .gpa-badge { font-size: 24px; font-weight: bold; padding: 10px 20px; border-radius: 50px; display: inline-block; }
        @media print { .no-print { display: none !important; } body { background: white; } .main-card { box-shadow: none; } }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card main-card">
                <div class="card-header">
                    <h1><i class="fas fa-calculator"></i> حاسبة المعدل التراكمي - المرحلة 4</h1>
                    <p>حفظ السجلات، مخططات بيانية، وأكثر!</p>
                </div>
                <div class="card-body p-4">
                    
                    <!-- اسم الطالب -->
                    <div class="row mb-4 no-print">
                        <div class="col-md-8">
                            <label><i class="fas fa-user"></i> اسم الطالب</label>
                            <input type="text" id="studentName" class="form-control" placeholder="أدخل اسمك">
                        </div>
                        <div class="col-md-4">
                            <label><i class="fas fa-history"></i> السجلات</label>
                            <button type="button" id="showHistoryBtn" class="btn btn-info w-100"><i class="fas fa-list"></i> السجلات السابقة</button>


</div>
                    </div>
                    
                    <div id="resultContainer"></div>
                    <div id="chartContainer" style="display: none; margin-top: 20px;"><canvas id="gradeChart"></canvas></div>
                    
                    <form id="gpaForm">
                        <div id="coursesContainer">
                            <div class="course-row">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label><i class="fas fa-book"></i> اسم المادة</label>
                                        <input type="text" name="course[]" class="form-control" placeholder="رياضيات" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label><i class="fas fa-clock"></i> الساعات</label>
                                        <input type="number" name="credits[]" class="form-control" placeholder="3" min="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label><i class="fas fa-graduation-cap"></i> الدرجة</label>
                                        <select name="grade[]" class="form-select">
                                            <option value="4.0">A (ممتاز - 4.0)</option>
                                            <option value="3.0">B (جيد جداً - 3.0)</option>
                                            <option value="2.0">C (جيد - 2.0)</option>
                                            <option value="1.0">D (مقبول - 1.0)</option>
                                            <option value="0.0">F (راسب - 0.0)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-row" style="display: none;"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-3 mt-4 flex-wrap no-print">
                            <button type="button" id="addCourseBtn" class="btn btn-success"><i class="fas fa-plus"></i> إضافة مادة</button>
                            <button type="submit" id="calculateBtn" class="btn btn-gradient"><i class="fas fa-chart-line"></i> حساب المعدل</button>
                            <button type="button" id="resetBtn" class="btn btn-secondary"><i class="fas fa-undo"></i> إعادة تعيين</button>
                            <button type="button" id="printBtn" class="btn btn-secondary"><i class="fas fa-print"></i> طباعة</button>
                        </div>
                    </form>
                    
                    <div class="mt-4 p-3 bg-light rounded no-print">
                        <h5>جدول تحويل الدرجات:</h5>
                        <div class="row text-center mt-3">
                            <div class="col"><span class="badge bg-success">A/A+</span><br>4.0</div>
                            <div class="col"><span class="badge bg-info">B</span><br>3.0</div>
                            <div class="col"><span class="badge bg-warning">C</span><br>2.0</div>
                            <div class="col"><span class="badge bg-secondary">D</span><br>1.0</div>
                            <div class="col"><span class="badge bg-danger">F</span><br>0.0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- مودال السجلات -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5><i class="fas fa-history"></i> السجلات السابقة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historyList"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
var currentGPA = 0;
var currentInterpretation = '';
var currentCourses = [];

$(document).ready(function() {
    
    // إضافة مادة
    $('#addCourseBtn').click(function() {
        var newRow = $('.course-row').first().clone();
        newRow.find('input').val('');
        newRow.find('select').val('4.0');
        newRow.find('.remove-row').show();
        $('#coursesContainer').append(newRow);
        newRow.hide().fadeIn(300);
    });
    
    // حذف مادة
    $(document).on('click', '.remove-row', function() {
        if ($('.course-row').length > 1) {
            $(this).closest('.course-row').fadeOut(300, function() { $(this).remove(); });
        } else {
            alert('يجب أن يكون لديك مادة واحدة على الأقل');
        }
    });
    
    // إعادة تعيين
    $('#resetBtn').click(function() {
        if (confirm('هل أنت متأكد؟')) {
            $('#coursesContainer .course-row:not(:first)').remove();
            $('#coursesContainer .course-row:first').find('input').val('');
            $('#coursesContainer .course-row:first').find('select').val('4.0');
            $('#coursesContainer .course-row:first').find('.remove-row').hide();
            $('#resultContainer').empty();
            $('#chartContainer').hide();
        }
    });
    
    // طباعة
    $('#printBtn').click(function() { window.print(); });
    
    // مخطط بياني
    function showChart(courses) {
        var grades = {'A (4.0)':0, 'B (3.0)':0, 'C (2.0)':0, 'D (1.0)':0, 'F (0.0)':0};
        for (var i = 0; i < courses.length; i++) {
            var gp = parseFloat(courses[i].grade);
            if (gp >= 3.7) grades['A (4.0)']++;
            else if (gp >= 3.0) grades['B (3.0)']++;
            else if (gp >= 2.0) grades['C (2.0)']++;
            else if (gp >= 1.0) grades['D (1.0)']++;
            else grades['F (0.0)']++;
        }
        var ctx = document.getElementById('gradeChart').getContext('2d');
        if (window.myChart) window.myChart.destroy();
        window.myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(grades),
                datasets: [{
                    label: 'عدد المواد',
                    data: Object.values(grades),
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#6c757d', '#dc3545']
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
        $('#chartContainer').show();
    }
    
    // حفظ السجل
    window.saveToHistory = function() {
        var studentName = $('#studentName').val().trim() || 'طالب';
        if (currentCourses.length === 0) { alert('لا توجد بيانات للحفظ'); return; }
        var form = $('<form method="post">');
        form.append('<input type="hidden" name="save_record" value="1">');
        form.append('<input type="hidden" name="student_name" value="' + studentName + '">');
        form.append('<input type="hidden" name="gpa" value="' + currentGPA + '">');
        form.append('<input type="hidden" name="interpretation" value="' + currentInterpretation + '">');


form.append('<input type="hidden" name="courses_data" value=\'' + JSON.stringify(currentCourses) + '\'>');
        $('body').append(form);
        form.submit();
    };
    
    // عرض السجلات
    $('#showHistoryBtn').click(function() {
        $.ajax({
            url: '', type: 'POST', data: { get_history: 1 }, dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(records) {
                var html = '';
                if (records.length === 0) { html = '<div class="alert alert-info">لا توجد سجلات</div>'; }
                else {
                    html = '<div class="row">';
                    for (var i = 0; i < records.length; i++) {
                        var r = records[i];
                        var gpaClass = (r.gpa >= 3.7) ? 'success' : ((r.gpa >= 3.0) ? 'info' : ((r.gpa >= 2.0) ? 'warning' : 'danger'));
                        html += '<div class="col-md-6 mb-3"><div class="card history-card"><div class="card-body">';
                        html += '<div class="d-flex justify-content-between"><div><strong>' + r.student_name + '</strong><br><small>' + r.created_at + '</small></div>';
                        html += '<div class="gpa-badge bg-' + gpaClass + ' text-white p-2 rounded">' + r.gpa + '</div></div>';
                        html += '<div>' + r.interpretation + '</div></div></div></div>';
                    }
                    html += '</div>';
                }
                $('#historyList').html(html);
                $('#historyModal').modal('show');
            }
        });
    });
    
    // التحقق من صحة البيانات
    function validateForm() {
        var valid = true;
        $('.course-row').each(function(idx) {
            var name = $(this).find('input[name="course[]"]').val();
            var credits = $(this).find('input[name="credits[]"]').val();
            if (name.trim() === '') {
                alert('أدخل اسم المادة رقم ' + (idx+1));
                valid = false;
                return false;
            }
            var creditsNum = parseFloat(credits);
            if (isNaN(creditsNum) || creditsNum <= 0) {
                alert('الساعات يجب أن تكون رقماً موجباً للمادة ' + (idx+1));
                valid = false;
                return false;
            }
        });
        return valid;
    }
    
    // إرسال النموذج
    $('#gpaForm').submit(function(e) {
        e.preventDefault();
        if (!validateForm()) return;
        
        var btn = $('#calculateBtn');
        var original = btn.html();
        btn.html('<span class="loading"></span> جاري الحساب...').prop('disabled', true);
        $('#resultContainer').html('<div class="alert alert-info"><div class="loading"></div> جاري الحساب...</div>');
        
        $.ajax({
            url: '', type: 'POST', data: $(this).serialize(), dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                if (res.success) {
                    currentGPA = res.gpa;
                    currentInterpretation = res.interpretation;
                    currentCourses = res.courses;
                    var html = '<div class="card"><div class="card-body">' + res.table + res.message;
                    html += '<div class="mt-3"><button onclick="saveToHistory()" class="btn btn-info"><i class="fas fa-save"></i> حفظ السجل</button></div>';
                    html += '</div></div>';
                    $('#resultContainer').html(html);
                    showChart(res.courses);
                    $('html, body').animate({ scrollTop: $('#resultContainer').offset().top - 20 }, 500);
                } else {
                    $('#resultContainer').html('<div class="alert alert-danger">' + res.error + '</div>');


}
            },
            error: function() { $('#resultContainer').html('<div class="alert alert-danger">خطأ في الاتصال</div>'); },
            complete: function() { btn.html(original).prop('disabled', false); }
        });
    });
    
    $('.course-row:first .remove-row').hide();
});
</script>
</body>
</html>
