

function run() {
    var dateInput = document.getElementById("delivery").value;
    var dateArray = dateInput.split("-");
    var formattedDate = dateArray[2] + "/" + dateArray[1] + "/" + dateArray[0];
    document.getElementById("srt").value = formattedDate;
    console.log(formattedDate);
} 



