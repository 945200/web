function addCourse() {
    var coursesContainer = document.getElementById('courses');
    var firstRow = document.querySelector('.course-row');
    var newRow = firstRow.cloneNode(true);
    
    var inputs = newRow.querySelectorAll('input');
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].value = '';
    }
    
    var select = newRow.querySelector('select');
    if (select) select.value = '4.0';
    
    var removeBtn = newRow.querySelector('.remove-btn');
    if (!removeBtn) {
        var btn = document.createElement('button');
        btn.textContent = 'Remove';
        btn.className = 'remove-btn';
        btn.setAttribute('type', 'button');
        btn.onclick = function() {
            var row = this.parentElement;
            var container = document.getElementById('courses');
            if (container.children.length > 1) {
                row.remove();
            } else {
                alert('You must have at least one course!');
            }
        };
        newRow.appendChild(btn);
    }
    
    coursesContainer.appendChild(newRow);
}

function validateForm() {
    var courses = document.querySelectorAll('input[name="course[]"]');
    var credits = document.querySelectorAll('input[name="credits[]"]');
    
    for (var i = 0; i < courses.length; i++) {
        if (courses[i].value.trim() === '') {
            alert('Please enter course name for course ' + (i + 1));
            return false;
        }
    }
    
    for (var i = 0; i < credits.length; i++) {
        var val = parseFloat(credits[i].value);
        if (isNaN(val) || val <= 0) {
            alert('Credits must be a positive number for course ' + (i + 1));
            return false;
        }
    }
    
    return true;
}
