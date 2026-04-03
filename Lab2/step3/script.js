$(document).ready(function () {
    // إضافة صف جديد للمادة
    $('#addCourse').click(function () {
        var row = $('.course-row').first().clone();
        row.find('input').val(''); // تفريغ الحقول المنسوخة
        row.append('<div class="col-auto"><button type="button" class="btn btn-danger remove-row">X</button></div>');
        $('#courses').append(row);
    });

    // حذف صف المادة
    $(document).on('click', '.remove-row', function () {
        if ($('.course-row').length > 1) {
            $(this).closest('.course-row').remove();
        }
    });

    // إرسال البيانات عبر AJAX
    $('#gpaForm').submit(function (e) {
        e.preventDefault();
        
        // التحقق من صحة المدخلات برمجياً
        var valid = true;
        $('[name="course[]"]').each(function () {
            if ($(this).val().trim() === '') valid = false;
        });
        if (!valid) {
            $('#result').html('<div class="alert alert-warning">Please enter valid values.</div>');
            return;
        }

        $.ajax({
            url: 'calculate.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    var alertClass = 'alert-info';
                    if (response.gpa >= 3.7) alertClass = 'alert-success';
                    else if (response.gpa >= 3.0) alertClass = 'alert-info';
                    else if (response.gpa >= 2.0) alertClass = 'alert-warning';
                    else alertClass = 'alert-danger';

                    $('#result').html('<div class="alert ' + alertClass + '">' + response.message + '</div>' + response.tableHtml);
                }
            }
        });
    });
});
