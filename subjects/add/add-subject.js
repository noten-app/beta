const subject_name_input = document.getElementById('name-input');
const grading_option_type_k = document.getElementById('grading_option-type_k');
const grading_option_type_m = document.getElementById('grading_option-type_m');
const grading_option_type_t = document.getElementById('grading_option-type_t');
const grading_option_type_s = document.getElementById('grading_option-type_s');
const subject_color_input = document.getElementById('color_input-input');
const subject_add_button = document.querySelector('.subject_add');

subject_add_button.addEventListener('click', () => {
    $.ajax({
        url: './add.php',
        type: 'POST',
        data: {
            subjectName: subject_name_input.value,
            testCustom: test_custom,
            gradingTypeK: grading_option_type_k.value,
            gradingTypeM: grading_option_type_m.value,
            gradingTypeT: grading_option_type_t.value,
            gradingTypeS: grading_option_type_s.value,
            subjectColor: subject_color_input.value
        },
        success: (data) => {
            console.log(data);
            if (!data.startsWith("missing")) {
                data = JSON.parse(data);
                location.assign("/subjects/grades?subject=" + data.subjectID);
            } else {
                console.log(data);
            }
        }
    });
});