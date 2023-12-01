function loadYear(yearID) {
    $.ajax({
        url: "/settings/school_years.php",
        type: "POST",
        data: {
            school_year: yearID
        },
        success: function (data) {
            console.log(data);
            location.reload();
        }
    });
}