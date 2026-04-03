<?php
// ملاحظة: هذا الملف يتطلب تثبيت مكتبة TCPDF
// قم بتحميلها من: https://github.com/tecnickcom/tcpdf
// وضعها في مجلد TCPDF داخل المشروع

// إذا لم تكن المكتبة مثبتة، استخدم هذا الكود البديل (تصدير HTML بدلاً من PDF)
if (!file_exists('TCPDF/tcpdf.php')) {
    // بديل: تصدير كملف HTML
    $studentName = $_POST['student_name'] ?? 'طالب';
    $gpa = floatval($_POST['gpa'] ?? 0);
    $interpretation = $_POST['interpretation'] ?? 'N/A';
    $totalCredits = floatval($_POST['total_credits'] ?? 0);
    $totalPoints = floatval($_POST['total_points'] ?? 0);
    $courses = json_decode($_POST['courses'] ?? '[]', true);
    
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="GPA_Report_' . date('Ymd_His') . '.html"');
    
    echo '<!DOCTYPE html>
    <html dir="rtl">
    <head><meta charset="UTF-8"><title>تقرير GPA</title></head>
    <body>
        <h1>تقرير المعدل التراكمي</h1>
        <p><strong>اسم الطالب:</strong> ' . htmlspecialchars($studentName) . '</p>
        <p><strong>التاريخ:</strong> ' . date('Y-m-d H:i') . '</p>
        <h2>المعدل: ' . number_format($gpa, 2) . '</h2>
        <p><strong>التقدير:</strong> ' . htmlspecialchars($interpretation) . '</p>
        <h3>تفاصيل المواد:</h3>
        <table border="1">
            <tr><th>المادة</th><th>الساعات</th><th>الدرجة</th><th>النقاط</th></tr>';
    
    foreach ($courses as $course) {
        echo '<tr>
            <td>' . htmlspecialchars($course['name']) . '</td>
            <td>' . $course['credits'] . '</td>
            <td>' . $course['grade'] . '</td>
            <td>' . number_format($course['points'], 2) . '</td>
        </tr>';
    }
    
    echo '</table>
        <p><strong>إجمالي الساعات:</strong> ' . number_format($totalCredits, 0) . '</p>
        <p><strong>إجمالي النقاط:</strong> ' . number_format($totalPoints, 2) . '</p>
    </body></html>';
    exit;
}

// إذا كانت المكتبة مثبتة
require_once('TCPDF/tcpdf.php');

$studentName = $_POST['student_name'] ?? 'طالب';
$gpa = floatval($_POST['gpa'] ?? 0);
$interpretation = $_POST['interpretation'] ?? 'N/A';
$totalCredits = floatval($_POST['total_credits'] ?? 0);
$totalPoints = floatval($_POST['total_points'] ?? 0);
$courses = json_decode($_POST['courses'] ?? '[]', true);

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 12);

$html = '
<style>
    h1 { color: #4a5568; text-align: center; }
    .gpa-box { background: #f0f4f8; padding: 20px; text-align: center; margin: 20px 0; }
    .gpa-value { font-size: 48px; font-weight: bold; color: #667eea; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th { background: #e2e8f0; padding: 10px; border: 1px solid #cbd5e0; }
    td { padding: 8px; border: 1px solid #cbd5e0; text-align: center; }
</style>

<h1>تقرير المعدل التراكمي</h1>

<p><strong>اسم الطالب:</strong> ' . htmlspecialchars($studentName) . '</p>
<p><strong>التاريخ:</strong> ' . date('Y-m-d H:i') . '</p>

<div class="gpa-box">
    <div class="gpa-value">' . number_format($gpa, 2) . '</div>
    <div><strong>' . htmlspecialchars($interpretation) . '</strong></div>
</div>

<h3>تفاصيل المواد</h3>
<table>
    <thead><tr><th>المادة</th><th>الساعات</th><th>الدرجة</th><th>النقاط</th></tr></thead>
    <tbody>';

foreach ($courses as $course) {
    $html .= '<tr>
        <td>' . htmlspecialchars($course['name']) . '</td>
        <td>' . $course['credits'] . '</td>
        <td>' . $course['grade'] . '</td>
        <td>' . number_format($course['points'], 2) . '</td>
    </tr>';
}

$html .= '
    </tbody>
</table>

<p><strong>إجمالي الساعات:</strong> ' . number_format($totalCredits, 0) . '</p>
<p><strong>إجمالي النقاط:</strong> ' . number_format($totalPoints, 2) . '</p>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('GPA_Report_' . date('Ymd_His') . '.pdf', 'D');
?>
GitHub
GitHub - tecnickcom/TCPDF: Official clone of PHP library to generate PDF documents and barcodes

Official clone of PHP library to generate PDF documents and barcodes - tecnickcom/TCPDF

