function deleteSubject() {
    const confirmation = confirm("Are you sure you want to delete this subject?");
    if (confirmation) $.ajax({
        url: "./delete.php",
        type: "POST",
        data: { id: subjectID },
        success: function (data) {
            console.log(data);
            if (data == "success") location.assign("/subjects/");
            else alert("There was an error deleting this subject.");
        }
    });
}