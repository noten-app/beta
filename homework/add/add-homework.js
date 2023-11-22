const subject_selector = document.getElementById('subject-selector');
const task_input = document.getElementById('task-input');
const date_input_input = document.getElementById('date_input-input');
const subject_add_button = document.querySelector('.subject_add');

subject_add_button.addEventListener('click', () => {
    // check if task is empty
    if (task_input.value == "") {
        task_input.style.border = "1px solid red";
        return;
    } else {
        task_input.style.border = "";
    }
    $.ajax({
        url: './add.php',
        type: 'POST',
        data: {
            subject: subject_selector.value,
            type: type,
            date_due: date_input_input.value,
            task: task_input.value
        },
        success: (data) => {
            console.log(data);
            if (data == "success") {
                location.assign("/homework");
            } else {
                console.log(data);
            }
        }
    });
});