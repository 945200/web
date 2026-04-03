<?php
require_once 'database.php';

$records = getHistoricalRecords(20);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل GPAs السابقة</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .main-card {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }
        .record-card {
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .record-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .gpa-badge {
            font-size: 24px;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
        }
        .gpa-success { background: #28a745; color: white; }
        .gpa-info { background: #17a2b8; color: white; }
        .gpa-warning { background: #ffc107; color: #333; }
        .gpa-danger { background: #dc3545; color: white; }
        .btn-back {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            line-height: 50px;
            font-size: 24px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            transform: scale(1.1);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card main-card">
                    <div class="card-header">
                        <h2 class="mb-0">
                            <i class="fas fa-history"></i> سجل GPAs السابقة
                        </h2>
                        <p class="mb-0 mt-2">آخر 20 عملية حساب</p>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if (empty($records)): ?>
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i>
                                لا توجد سجلات سابقة. قم بحساب GPA أولاً.
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($records as $record): 
                                    $gpaClass = '';
                                    if ($record['gpa'] >= 3.7) $gpaClass = 'gpa-success';
                                    elseif ($record['gpa'] >= 3.0) $gpaClass = 'gpa-info';
                                    elseif ($record['gpa'] >= 2.0) $gpaClass = 'gpa-warning';
                                    else $gpaClass = 'gpa-danger';
                                    
                                    $coursesList = json_decode($record['course_details'], true);
                                ?>
                                <div class="col-md-6 mb-3">
<div class="card record-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-1">
                                                        <i class="fas fa-user-graduate"></i> 
                                                        <?php echo htmlspecialchars($record['student_name']); ?>
                                                    </h5>
                                                    <small class="text-muted">
                                                        <i class="far fa-calendar-alt"></i> 
                                                        <?php echo date('Y-m-d H:i', strtotime($record['calculation_date'])); ?>
                                                    </small>
                                                </div>
                                                <div class="gpa-badge <?php echo $gpaClass; ?>">
                                                    <?php echo number_format($record['gpa'], 2); ?>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <span class="badge bg-secondary">
                                                    <?php echo $record['interpretation']; ?>
                                                </span>
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-book"></i> <?php echo count($coursesList); ?> مواد
                                                </span>
                                                <span class="badge bg-info">
                                                    <i class="fas fa-hourglass-half"></i> <?php echo $record['total_credits']; ?> ساعات
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <a href="index.php" class="btn-back">
        <i class="fas fa-home"></i>
    </a>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</body>
</html>
