const submit_button = document.getElementById("submit_button");

submit_button.addEventListener("click", function () {
    let transfersubjects = [];
    for (const checkbox of document.getElementsByClassName("copy_subject_checkbox")) {
        if (checkbox.checked) {
            transfersubjects.push(checkbox.value);
        }
    }
    const year_name = document.getElementById("year-name-input").value;
    if (year_name == "") {
        alert("Please enter a year name");
        return;
    }
    $.ajax({
        type: "POST",
        url: "add-year.php",
        data: {
            year_name: year_name,
            transfer_subjects: transfersubjects
        },
        success: function (response) {
            if (response == "success") {
                window.location.href = "/";
            } else {
                alert(response);
            }
        }
    });
});