
const tableStockConfirmation = document.getElementById('table-stock-confirmation');
const selectFilter = new bootstrap.Modal(document.getElementById('select-filter'));

// 初期設定
const uridai = document.getElementById("select-uriba-dai");
const uridaiDefault = uridai.dataset.defaultCategories.split(',');
const urichu = document.getElementById("select-uriba-chu");
const urisho = document.getElementById("select-uriba-sho");
uridai.addEventListener('change', selectCategory, false);
urichu.addEventListener('change', selectCategory, false);
urisho.addEventListener('change', selectCategory, false);

const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);

// カテゴリセット
window.onload = function() {
    setFilter();
    // getから該当のカテゴリをセレクトを選択済みにする
    for (const [key, value] of urlParams.entries()) {
        if (key === "large_cat") {
            uridai.value = value
        }
        if (key === "middle_cat") {
            urichu.value = value
        }
        if (key === "small_cat") {
            urisho.value = value
        }
    }
}

// プルダウン選択時
function selectCategory() {
    // 大の選択は中と小をALLに
    if (this === uridai) {
        urichu.selectedIndex = 0;
        urisho.selectedIndex = 0
    }

    // 中の選択は小をALLに
    if (this === urichu) {
        urisho.selectedIndex = 0
    }

    document.filter.submit();
}

// プルダウンの反映
function setFilter() {
    // 選択された売場大IDを取得
    let uridaiId = uridai.value;
    let urichuId = urichu.value;
    let urishoId = urisho.value;

    // 売場中の選択肢をリセット
    urichu.selectedIndex = 0;
    urichu.disabled = true;

    // 売場小の選択肢をリセット
    urisho.selectedIndex = 0;
    urisho.disabled = true;

    // 売場大の全ての選択肢を一旦非表示にする
    for (const element of uridai.options) {
        if (element.value !== "" && !uridaiDefault.includes(element.value)) {
            element.style.display = "none";
        }
    }

    // 売場中の全ての選択肢を一旦非表示にする
    for (const element of urichu.options) {
        if(element.value !== ""){
            element.style.display = "none";
        }
    }

    // 売場小の全ての選択肢を一旦非表示にする
    for (const element of urisho.options) {
        if(element.value !== ""){
            element.style.display = "none";
        }
    }

    // 売場大がALLの場合はここで終了
    if (uridaiId === "") {
        return;
    }

    // 売場大が選択されてるので売場中の開放
    urichu.disabled = false;

    // 売場大がALL以外の場合は、対応する売場中と売場小の選択肢だけを表示する
    for (const element of urichu.options) {
        let split = element.value.split('-');
        if (split[0] === uridaiId) {
            element.style.display = "block";
        }
    }

    // 売場中がALLの場合はここで終了
    if (urichuId === "") {
        return;
    }

    // 売場中が選択されているので売場小の開放
    urisho.disabled = false;

    for (const element of urisho.options) {
        let split = element.value.split('-');
        if(urichuId === ""){
            if (split[0] === uridaiId) {
                element.style.display = "block";
            }
        } else {
            if (split[0] + "-" + split[1] === urichuId) {
                element.style.display = "block";
            }
        }
    }

    // 中変更時は再設定
    if (this.id === urichu.id) {
        urichu.value = urichuId;
    }
}

// マイナス在庫
const chkMinusStock = document.getElementById('chk-minus-stock');
chkMinusStock.addEventListener('change', () => {
    let flg = chkMinusStock.checked ? "on" : "off";

    // URLを取得
    let url = new URL(window.location.href);

    // URLSearchParamsオブジェクトを取得
    if (url.searchParams.get('flg_minus')) {
        url.searchParams.set('flg_minus', flg);
    } else {
        url.searchParams.append('flg_minus', flg);
    }
    url.searchParams.delete('page');
    location.href = url;
});
