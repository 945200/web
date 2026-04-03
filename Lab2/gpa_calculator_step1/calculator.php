<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['course']) && isset($_POST['credits']) && isset($_POST['grade'])) {
        
        $courses = $_POST['course'];
        $credits = $_POST['credits'];
        $grades = $_POST['grade'];
        
        $totalPoints = 0;
        $totalCredits = 0;
        $validCourses = [];
        
        for ($i = 0; $i < count($courses); $i++) {
            $courseName = htmlspecialchars(trim($courses[$i]), ENT_QUOTES, 'UTF-8');
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
        
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>نتيجة المعدل التراكمي</title>
            <link rel="stylesheet" href="style.css">
        </head>
        <body>
            <div class="container">
                <h1>📊 نتيجة المعدل التراكمي</h1>
                
                <?php if ($totalCredits > 0): ?>
                    
                    <div class="results-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>اسم المادة</th>
                                    <th>الساعات</th>
                                    <th>نقاط الدرجة</th>
                                    <th>النقاط الموزونة</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($validCourses as $course): ?>
                                <tr>
                                    <td><?php echo $course['name']; ?></td>
                                    <td><?php echo $course['credits']; ?></td>
                                    <td><?php echo $course['grade']; ?></td>
                                    <td><?php echo number_format($course['points'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr style="background: #f0f0f0; font-weight: bold;">
                                    <td colspan="2">الإجمالي:</td>
                                    <td><?php echo number_format($totalCredits, 0); ?> ساعة</td>
                                    <td><?php echo number_format($totalPoints, 2); ?> نقطة</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php
                    $gpa = $totalPoints / $totalCredits;
                    
                    if ($gpa >= 3.7) {
                        $interpretation = "🏆 امتياز (ممتاز)";
                    } elseif ($gpa >= 3.0) {
                        $interpretation = "👍 جيد جداً (ممتاز)";
                    } elseif ($gpa >= 2.0) {
                        $interpretation = "✅ جيد (مقبول)";
                    } else {
                        $interpretation = "❌ راسب (ضعيف)";
                    }
                    ?>
                    
                    <div class="gpa-result">
                        <div class="gpa-value"><?php echo number_format($gpa, 2); ?></div>
                        <div><?php echo $interpretation; ?></div>
</div>
                    
                <?php else: ?>
                    <div class="gpa-result" style="background: #dc3545;">
                        <h3>لا توجد مواد صحيحة</h3>
                        <p>الرجاء إدخال مادة واحدة على الأقل بساعات معتمدة صحيحة.</p>
                    </div>
                <?php endif; ?>
                
                <div style="text-align: center; margin-top: 30px;">
                    <button onclick="window.location.href='index.html'" class="btn-add">← العودة للحاسبة</button>
                </div>
                
            </div>
        </body>
        </html>
        <?php
        
    } else {
        echo "<h1>خطأ</h1><p>البيانات غير مكتملة.</p><a href='index.html'>العودة</a>";
    }
    
} else {
    header('Location: index.html');
    exit;
}
?>
