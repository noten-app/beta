const submit_button = document.getElementById("submit_button");

submit_button.addEventListener("click", function () {
    const year_name = document.getElementById("year-name-input").value;
    if (year_name == "") {
        alert("Please enter a year name");
        return;
    }
    $.ajax({
        type: "POST",
        url: "update.php",
        data: {
            year_name: year_name
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

const delete_button = document.getElementById("delete_button");

delete_button.addEventListener("click", function () {
    if (confirm("Are you sure you want to delete this year?")) {
        $.ajax({
            type: "POST",
            url: "delete.php",
            data: {
                next: document.getElementById("nextyear").innerText
            },
            success: function (response) {
                if (response == "success") {
                    window.location.href = "/";
                } else {
                    alert(response);
                }
            }
        });
    }
});