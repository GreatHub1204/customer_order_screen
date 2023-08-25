let show_div = document.getElementById('memo_display_show');
let none_div = document.getElementById('memo_display_none');
let add_display_style = document.querySelector("section.display-info");
show_div.style.display = "none";
add_display_style.style.margin = "51px 0";

function show(){
    show_div.style.display = "inline-flex";
    none_div.style.display = "none";
    add_display_style.style.margin = "9px 0 0 0";
}

function run() {
    var dateInput = document.getElementById("delivery").value;
    var dateArray = dateInput.split("-");
    var formattedDate = dateArray[2] + " / " + dateArray[1] + " / " + dateArray[0];
    document.getElementById("srt").value = formattedDate;
} 