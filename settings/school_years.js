function loadYear(yearID) {
    $.ajax({
        url: "school_years.php",
        type: "POST",
        data: {
            school_year: yearID
        },
        success: function (data) {
            location.reload();
        }
    });
}