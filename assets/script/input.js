// メモの追加

let show_div = document.getElementById('memo_display_show');
let none_div = document.getElementById('memo_display_none');
let add_display_style = document.querySelector("section.display-info");
let main_element = document.querySelectorAll('.order-memo');
let display_info_element = document.querySelector(".display-info");

document.getElementById("memo-event").addEventListener("click", function () {
    main_element[0].classList.add('show');
    none_div.style.display = "none";
    display_info_element.classList.add("change-style");
});

// 引渡し日に値を入れる
function run() {
    var dateInput = document.getElementById("delivery").value;
    var dateArray = dateInput.split("-");
    var formattedDate = dateArray[2] + " / " + dateArray[1] + " / " + dateArray[0];
    document.getElementById("srt").value = formattedDate;
}

// 文字制限

function checkInputLength() {
    var input = document.getElementById("myTextarea").value;
    var limitedInput = "";

    for (var i = 0; i < input.length; i++) {
      var char = input.charAt(i);

      if (isFullWidth(char) && limitedInput.length < 70) {
        limitedInput += char;
      } else if (!isFullWidth(char) && limitedInput.length < 140) {
        limitedInput += char;
      }
    }

    document.getElementById("myTextarea").value = limitedInput;
  }

  function isFullWidth(char) {
    var charCode = char.charCodeAt(0);

    if (
      (charCode >= 0x1100 && charCode <= 0x11FF) ||
      (charCode >= 0x2E80 && charCode <= 0xA4CF) ||
      (charCode >= 0xAC00 && charCode <= 0xD7AF) ||
      (charCode >= 0xF900 && charCode <= 0xFAFF) ||
      (charCode >= 0xFE10 && charCode <= 0xFEFF) ||
      (charCode >= 0xFF00 && charCode <= 0xFF60) ||
      (charCode >= 0xFFE0 && charCode <= 0xFFE6)
    ) {
      return true; // Full-width character
    }

    return false; // Half-width character
  }

  