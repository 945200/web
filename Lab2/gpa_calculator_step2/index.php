<?php
$result = '';
$tableHtml = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $courses = $_POST['course'] ?? [];
    $credits = $_POST['credits'] ?? [];
    $grades = $_POST['grade'] ?? [];
    
    $totalPoints = 0;
    $totalCredits = 0;
    
    for ($i = 0; $i < count($courses); $i++) {
        $cr = floatval($credits[$i]);
        $gp = floatval($grades[$i]);
        
        if ($cr > 0) {
            $pts = $gp * $cr;
            $totalPoints += $pts;
            $totalCredits += $cr;
            
            $tableHtml .= "<tr><td>" . htmlspecialchars($courses[$i]) . "</td><td>$cr</td><td>$gp</td><td>" . number_format($pts, 2) . "</td></tr>";
        }
    }
    
    if ($totalCredits > 0) {
        $gpa = $totalPoints / $totalCredits;
        $gpaValue = number_format($gpa, 2);
        
        if ($gpa >= 3.7) $inter = "امتياز";
        elseif ($gpa >= 3.0) $inter = "جيد جداً";
        elseif ($gpa >= 2.0) $inter = "جيد";
        else $inter = "راسب";
        
        $result = "<div style='background:#2196F3;color:white;padding:20px;margin-top:20px;text-align:center'>
                    <h2>المعدل: $gpaValue</h2>
                    <h3>$inter</h3>
                   </div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>GPA Calculator - المرحلة 2</title>
</head>
<body>
    <h1>حاسبة المعدل التراكمي</h1>
    
    <?php if ($tableHtml != ""): ?>
        <h2>النتائج</h2>
        <table border="1">
            <tr><th>المادة</th><th>الساعات</th><th>الدرجة</th><th>النقاط</th></tr>
            <?php echo $tableHtml; ?>
        </table>
        <?php echo $result; ?>
        <hr>
    <?php endif; ?>
    
    <form method="post">
        <div id="courses">
            <div>
                <input type="text" name="course[]" placeholder="اسم المادة" required>
                <input type="number" name="credits[]" placeholder="الساعات" min="1" required>
                <select name="grade[]">
                    <option value="4.0">A</option>
                    <option value="3.0">B</option>
                    <option value="2.0">C</option>
                    <option value="1.0">D</option>
                    <option value="0.0">F</option>
                </select>
            </div>
        </div>
        <button type="button" onclick="addCourse()">+ إضافة مادة</button>
        <button type="submit">حساب المعدل</button>
    </form>
    
    <script>
        function addCourse() {
            var div = document.getElementById('courses');
            var clone = div.children[0].cloneNode(true);
            clone.querySelectorAll('input').forEach(i => i.value = '');
            clone.querySelector('select').value = '4.0';
            div.appendChild(clone);
        }
    </script>
</body>
</html>
