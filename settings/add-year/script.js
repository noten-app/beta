const submit_button = document.getElementById("submit_button");

submit_button.addEventListener("click", function () {
    let transferClasses = [];
    for (const checkbox of document.getElementsByClassName("copy_class_checkbox")) {
        if (checkbox.checked) {
            transferClasses.push(checkbox.value);
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
            transfer_classes: transferClasses
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