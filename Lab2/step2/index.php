<?php
$results = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course'])) {
    $courses = $_POST['course'];
    $credits = $_POST['credits'];
    $grades = $_POST['grade'];
    $totalPoints = 0;
    $totalCredits = 0;

    $results .= "<h3>Calculated Result:</h3>";
    $results .= "<table><tr><th>Course</th><th>Credits</th><th>Grade</th><th>Points</th></tr>";

    for ($i = 0; $i < count($courses); $i++) {
        $name = htmlspecialchars($courses[$i]);
        $cr = floatval($credits[$i]);
        $g = floatval($grades[$i]);
        if ($cr <= 0) continue;

        $pts = $cr * $g;
        $totalPoints += $pts;
        $totalCredits += $cr;
        $results .= "<tr><td>$name</td><td>$cr</td><td>$g</td><td>$pts</td></tr>";
    }
    $results .= "</table>";

    if ($totalCredits > 0) {
        $gpa = $totalPoints / $totalCredits;
        if ($gpa >= 3.7) $status = "Distinction";
        elseif ($gpa >= 3.0) $status = "Merit";
        elseif ($gpa >= 2.0) $status = "Pass";
        else $status = "Fail";

        $results .= "<p>Your GPA is: <strong>" . number_format($gpa, 2) . "</strong> ($status)</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GPA Calculator - Single Page</title>
    <style>
        body { font-family: Arial; margin: 20px; background-color: #f4f4f9; }
        .course-row { margin-bottom: 10px; display: flex; gap: 10px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #007BFF; color: white; }
        .result-container { margin-top: 30px; padding: 15px; border-top: 2px solid #007BFF; }
    </style>
</head>
<body>
    <h1>GPA Calculator (Step 2)</h1>

    <form method="post" action="" onsubmit="return validateForm();">
        <div id="course-list">
            <div class="course-row">
                <input type="text" name="course[]" placeholder="Course Name" required>
                <input type="number" name="credits[]" placeholder="Credits" min="1" required>
                <select name="grade[]">
                    <option value="4.0">A</option>
                    <option value="3.0">B</option>
                    <option value="2.0">C</option>
                    <option value="1.0">D</option>
                    <option value="0.0">F</option>
                </select>
            </div>
        </div>
        <button type="button" onclick="addRow()">+ Add Course</button>
        <input type="submit" value="Calculate GPA">
    </form>


    <div class="result-container">
        <?php echo $results; ?>
    </div>


    <script>
        function addRow() {
            const div = document.createElement('div');
            div.className = 'course-row';
            div.innerHTML = 
                <input type="text" name="course[]" placeholder="Course Name" required>
                <input type="number" name="credits[]" placeholder="Credits" min="1" required>
                <select name="grade[]">
                    <option value="4.0">A</option><option value="3.0">B</option>
                    <option value="2.0">C</option><option value="1.0">D</option>
                    <option value="0.0">F</option>
                </select>
                <button type="button" onclick="this.parentNode.remove()">X</button>;
            document.getElementById('course-list').appendChild(div);
        }

        function validateForm() {
            const credits = document.querySelectorAll('input[type="number"]');
            for (let cr of credits) {
                if (cr.value <= 0) {
                    alert("Credits must be positive!");
                    return false;
                }
            }
            return true;
        }
    </script>
</body>
</html>
