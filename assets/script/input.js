let show_div = document.getElementById('memo_display_show');
let none_div = document.getElementById('memo_display_none');

show_div.style.display = "none";

function show(){
    show_div.style.display = "contents";
    none_div.style.display = "none";
}

function run() {
    var dateInput = document.getElementById("delivery").value;
    var dateArray = dateInput.split("-");
    var formattedDate = dateArray[2] + "/" + dateArray[1] + "/" + dateArray[0];
    document.getElementById("srt").value = formattedDate;
} 
