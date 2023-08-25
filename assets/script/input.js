
// n = new Date();
// y = n.getFullYear();
// m = n.getMonth() + 1;
// d = n.getDate();
// if (m < 9) {
//     m = "0" + m;
// }
// if (d < 9) {
//     m = "0" + d;
// }
// document.getElementById("delivery_date").innerHTML = m + "/" + d + "/" + y;
// document.getElementById("delivery_date").value = m + "/" + d + "/" + y;

function run() {
    var dateInput = document.getElementById("delivery").value;
    var dateArray = dateInput.split("-");
    var formattedDate = dateArray[2] + "/" + dateArray[1] + "/" + dateArray[0];
    document.getElementById("srt").value = formattedDate;
    console.log(formattedDate);
} 

// インプットタグに現在日付をデフォルトで現示

// const currentDate = new Date().toISOString().split("T")[0];
// let nodeList = document.querySelectorAll("input[type]");
// nodeList[2].value = currentDate;
// nodeList[4].value = currentDate;


