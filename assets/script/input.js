let show_div = document.getElementById('memo_display_show');
let none_div = document.getElementById('memo_display_none');

show_div.style.display = "none";

function show(){
    show_div.style.display = "contents";
    none_div.style.display = "none";
}


n = new Date();
y = n.getFullYear();
m = n.getMonth() + 1;
d = n.getDate();
if (m < 9) {
    m = "0" + m;
}
if (d < 9) {
    m = "0" + d;
}
document.getElementById("delivery_date").innerHTML = m + "/" + d + "/" + y;
document.getElementById("delivery_date").value = m + "/" + d + "/" + y;

function run() {
    document.getElementById("srt").value = document.getElementById("selected_date").value;
    console.log(document.getElementById("srt").value);
} 
