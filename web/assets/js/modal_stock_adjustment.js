
// 初期設定 クリックイベント等
const modalStockAdjustment = new bootstrap.Modal(document.getElementById('modal-stock-adjustment'));
const correctionTable = document.getElementById('correction-table');
const btnDo = document.getElementById('modal-btn-do');
btnDo.addEventListener('click', createOrEdit, false);
const tableRows = document.getElementsByClassName('table-td-link');
for(const element of tableRows) {
    element.addEventListener('click', modalOpen, false);
}

// 更新ボタンクリック
function createOrEdit() {
    const storeId = document.getElementById('modal-store-id').innerText; // 店舗ID
    const itemId = document.getElementById('modal-jan-code').innerText; // 基準商品ID
    const date = document.getElementById('modal-date').dataset.datetime; // 日付
    const userId = document.getElementById('modal-btn-do').dataset.userId; // 担当者ID
    const correctionInventoryCount = document.getElementById('modal-correction-inventory-count').innerText; // 商品ID
    const stockMin = document.getElementById('modal-stock-min-change').innerText; // 最低在庫数
    const stockMax = document.getElementById('modal-stock-max-change').innerText; // 最大在庫
    const radioMax = document.getElementsByName('radio-max-inventory-control');
    const isEdit = btnDo.dataset.isEdit;
    let isRadioChange = false;
    let flgMaxInventoryControl = 0; // 最大在庫抑制
    for (let i = 0; i < radioMax.length; i++) {
        if (radioMax[i].checked) {
            flgMaxInventoryControl = radioMax[i].value;
            if (radioMax[i].dataset.checkDefault != "1") {
                isRadioChange = true;
            }
        }
    }
    const radioAutoOrder = document.getElementsByName('radio-auto-order');
    let flgAutoOrder = 0; // 自動発注
    for (let i = 0; i < radioAutoOrder.length; i++) {
        if (radioAutoOrder[i].checked) {
            flgAutoOrder = radioAutoOrder[i].value;
            if (radioAutoOrder[i].dataset.checkDefault != "1") {
                isRadioChange = true;
            }
        }
    }

    // レコード単位ループ取得？
    const inputRows = document.querySelectorAll('.input-row');
    let itemRowData = {};
    inputRows.forEach((element, index) => {
        let tmpData = {};
        tmpData['storeId'] = document.getElementById('modal-store-id').innerText;
        tmpData['date'] = document.getElementById('modal-date').dataset.datetime;
        tmpData['itemId'] = element.dataset.itemId;
        tmpData['baseItemId'] = element.dataset.baseItemId;
        tmpData['dai'] = element.dataset.dai;
        tmpData['dan'] = element.dataset.dan;
        tmpData['ichi'] = element.dataset.ichi;
        tmpData['bara'] = element.dataset.bara;
        tmpData['stock'] = element.dataset.stock;
        tmpData['stockSum'] = element.dataset.stockSum;
        tmpData['min'] = element.dataset.min;
        tmpData['max'] = element.dataset.max;
        tmpData['flgMaxInventoryControl'] = flgMaxInventoryControl;
        tmpData['flgAutoOrder'] = flgAutoOrder;
        tmpData['userId'] = document.getElementById('modal-btn-do').dataset.userId;
        itemRowData[index] = tmpData;
    });

    // 最大在庫抑制、自動発注のラジオボタンを変更する時にはアラートを表示
    if (isRadioChange) {
        alert('ラジオボタンが変更になりました。');
    }

    // アラート用メッセージ
    const alertMessage = "下記の内容で登録していいですか？\n" +
        "・バラ在庫換算数合計（補正値）： " + correctionInventoryCount + "\n" +
        "・最低在庫数変更値： " + stockMin + "\n" +
        "・最大在庫数変更値： " + stockMax + "\n" +
        "・最大在庫抑制： " + (flgMaxInventoryControl === "1" ? "オン" : "オフ") + "\n" +
        "・自動発注： " + (flgAutoOrder === "1" ? "オン" : "オフ") + "\n" +
        "・商品コード： " + itemId
    ;

    if( confirm(alertMessage) ) {
        const url = '../function/modal_stock_update.php';
        const data = {
            storeId: storeId,
            userId: userId,
            stockMin: removeComma(stockMin),
            stockMax: removeComma(stockMax),
            flgMaxInventoryControl: flgMaxInventoryControl,
            flgAutoOrder: flgAutoOrder,
            itemId: itemId,
            itemRowData: itemRowData,
            isEdit: isEdit
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(result => {
                alert('更新を完了しました。');
                const pageName = btnDo.dataset.page;
                if(pageName === "stock_confirmation") { // 在庫確認はリロード
                    location.reload();
                } else if(pageName === "recommend") { // レコメンドは在庫補正モーダルを閉じる
                    modalStockAdjustment.hide();
                }
            })
            .catch(error => {
                alert(error);
            });
    }

}

// モーダル起動
function modalOpen(){
    const weekItems = ["日", "月", "火", "水", "木", "金", "土"];
    let tDatetime = new Date(this.dataset.date);
    document.getElementById('modal-date').innerText = tDatetime.getFullYear() + '年' + (tDatetime.getMonth() + 1) + '月' + tDatetime.getDate() + '日（' + weekItems[tDatetime.getDay()] + '）';
    document.getElementById('modal-date').dataset.datetime = tDatetime.getFullYear() + '-' + (tDatetime.getMonth() + 1).toString().padStart(2, "0")+ '-' + tDatetime.getDate().toString().padStart(2, "0") + ' 00:00:00';
    document.getElementById('modal-store-id').innerText = this.dataset.storeId;
    document.getElementById('modal-store-name').innerText = this.dataset.storeName;
    document.getElementById('modal-jan-code').innerText = this.dataset.itemId;
    document.getElementById('modal-item-name').innerText = this.dataset.itemName;
    document.getElementById('modal-now-inventory-count').innerText = this.dataset.yesterdayStock;
    document.getElementById('modal-btn-do').dataset.userId = this.dataset.userId;
    document.getElementById('modal-btn-do').dataset.page = this.dataset.page;

    // パラメータを元にデータ取得
    const params = {
        storeId: this.dataset.storeId,
        itemId: this.dataset.itemId,
        date: document.getElementById('modal-date').dataset.datetime
    };

    const urlParams = new URLSearchParams(params);
    const url = `../function/modal_stock_getdata.php?${urlParams}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            // 値設定
            document.getElementById('modal-products-estimated-count').innerText = data.estimated_count; // 当日納品予定数
            document.getElementById('modal-stock-min-now').innerText = data.auto_order.最低在庫; // 当日納品予定数
            document.getElementById('modal-stock-max-now').innerText = data.auto_order.最大在庫; // 当日納品予定数

            // 編集時に元の値設定
            let min = 0;
            let max = 0;
            document.getElementById('modal-radio-max-inventory-control-off').checked = true;
            document.getElementById('modal-radio-max-inventory-control-off').dataset.checkDefault = "1";
            document.getElementById('modal-radio-max-inventory-control-on').dataset.checkDefault = "0";
            document.getElementById('modal-radio-auto-order-off').checked = true;
            document.getElementById('modal-radio-auto-order-off').dataset.checkDefault = "1";
            document.getElementById('modal-radio-auto-order-on').dataset.checkDefault = "0";
            document.getElementById('modal-correction-inventory-count').innerText = addComma(data.correction_inventory);

            // 最大在庫抑制と自動発注にプロシージャの値設定
            if (data.stock_calculate.最大在庫抑制 === "1") {
                document.getElementById('modal-radio-max-inventory-control-on').checked = true;
                document.getElementById('modal-radio-max-inventory-control-on').dataset.checkDefault = "1";
                document.getElementById('modal-radio-max-inventory-control-off').dataset.checkDefault = "0";
            }
            if (data.stock_calculate.自動発注 === "1") {
                document.getElementById('modal-radio-auto-order-on').checked = true;
                document.getElementById('modal-radio-auto-order-on').dataset.checkDefault = "1";
                document.getElementById('modal-radio-auto-order-off').dataset.checkDefault = "0";
            }

            btnDo.dataset.isEdit = false;

            // 更新_最大最低在庫にレコードがある場合
            if (typeof data.max_min !== "undefined") {
                btnDo.dataset.isEdit = true;
                document.getElementById('modal-radio-max-inventory-control-off').checked = true;
                document.getElementById('modal-radio-auto-order-off').checked = true;

                if (data.max_min.最大在庫抑制 == "1") {
                    document.getElementById('modal-radio-max-inventory-control-on').checked = true;
                    document.getElementById('modal-radio-max-inventory-control-on').dataset.checkDefault = "1";
                    document.getElementById('modal-radio-max-inventory-control-off').dataset.checkDefault = "0";
                } else {
                    document.getElementById('modal-radio-max-inventory-control-on').checked = false;
                    document.getElementById('modal-radio-max-inventory-control-on').dataset.checkDefault = "0";
                    document.getElementById('modal-radio-max-inventory-control-off').dataset.checkDefault = "1";
                }
                if (data.max_min.自動発注 == "1") {
                    document.getElementById('modal-radio-auto-order-on').checked = true;
                    document.getElementById('modal-radio-auto-order-on').dataset.checkDefault = "1";
                    document.getElementById('modal-radio-auto-order-off').dataset.checkDefault = "0";
                } else {
                    document.getElementById('modal-radio-auto-order-on').checked = false;
                    document.getElementById('modal-radio-auto-order-on').dataset.checkDefault = "0";
                    document.getElementById('modal-radio-auto-order-off').dataset.checkDefault = "1";
                }
            }

            // テーブルのHTMLを動的生成
            makeTableHtml(data.item_back, data.item_bara);
            modalStockAdjustment.show();
        })
        .catch(error => {
            console.log(error);
            alert(error);
        });
}


// テーブルinput要素のEvent設定
document.getElementById("correction-table").addEventListener("change", (e) => {
    let targetElement = e.target;
    if (targetElement.tagName === "INPUT") {
        const parentInputRow = e.target.closest('.input-row');
        // 在庫数の場合
        if(e.target.getAttribute('data-target-bara')) {
            // 乗算してバラ在庫缶算数をいれる
            const baraId = e.target.getAttribute('data-target-bara');
            const subtotalId = e.target.getAttribute('data-target-bara-subtotal');
            const inputNum = removeComma(e.target.value);
            const subCalc = inputNum * parseInt(document.getElementById(baraId).textContent, 10);
            parentInputRow.dataset.stock = inputNum;
            parentInputRow.dataset.stockSum = subCalc;
            document.getElementById(subtotalId).textContent = addComma(subCalc);

            // バラ換算在庫数をすべて足してバラ換算在庫数合計（補正値）に表示
            const totalId = e.target.getAttribute('data-target-total');
            const subtotals = document.querySelectorAll('.bara-subtotal');
            let sum = 0;
            subtotals.forEach(element => {
                const removeCommaNum = removeComma(element.textContent);
                const numberValue = parseInt(removeCommaNum, 10);
                if (!isNaN(numberValue)) {
                    sum += numberValue;
                }
            });
            document.getElementById(totalId).textContent = addComma(sum);
            e.target.value = addComma(inputNum);

        } else if(e.target.getAttribute('data-target-min')) { // 最低陳列数
            parentInputRow.dataset.min = removeComma(targetElement.value);
            const targetId = e.target.getAttribute('data-target-min');
            const targetColumns = document.querySelectorAll('.stock-min');
            let sum = 0;
            targetColumns.forEach(element => {
                const removeCommaNum = removeComma(element.value);
                const numberValue = parseInt(removeCommaNum, 10);
                if (!isNaN(numberValue)) {
                    sum += numberValue;
                    element.value = addComma(numberValue);
                }
            });
            document.getElementById(targetId).textContent = addComma(sum);
        } else { // 陳列可能数
            parentInputRow.dataset.max = removeComma(targetElement.value);
            const targetId = e.target.getAttribute('data-target-max');
            const targetColumns = document.querySelectorAll('.stock-max');
            let sum = 0;
            targetColumns.forEach(element => {
                const removeCommaNum = removeComma(element.value);
                const numberValue = parseInt(removeCommaNum, 10);
                if (!isNaN(numberValue)) {
                    sum += numberValue;
                    element.value = addComma(numberValue);
                }
            });
            document.getElementById(targetId).textContent = addComma(sum);
        }
    }
}, true);


// テーブル生成
function makeTableHtml(item_back, item_bara) {
    // テーブルを一度リセットしてから生成
    while (correctionTable.firstChild) {
        correctionTable.removeChild(correctionTable.firstChild);
    }

    let tmp_add_html = "";
    // テーブルヘッダー追記
    tmp_add_html += '<div class="row-table">' +
        '<div class="thead th-location">在庫ロケーション</div>' +
        '<div class="thead th-bara">バラ換算数</div>' +
        '<div class="thead th-inventory">商品在庫数</div>' +
        '<div class="thead th-bara-inventory">バラ換算在庫数</div>';

    if (item_back.length === 1) {
        tmp_add_html += '<div class="blank information-change-text">陳列情報変更</div>';
    } else {
        tmp_add_html += '<div class="blank"></div>';
    }
    tmp_add_html += '</div>';

    // 行カウンター
    let cnt = 0;

    // バックヤード追記
    item_back.forEach(function (element, index, array) {
        tmp_add_html += '<div class="row-table input-row" id="input-row-' + cnt + '" data-item-id="' + element.商品ID + '" data-base-item-id="' + element.基準商品ID + '" data-dai="" data-dan="" data-ichi="" data-bara="' + element.換算数 + '" data-stock="' + element.商品在庫数 + '" data-stock-sum="' + element.バラ在庫数 + '">' +
            '<div class="tbody td-location">バックヤード<br>' + element.商品ID + ' ' + element.商品名 + '</div>' +
            '<div class="tbody td-bara " id="bara-' + cnt + '">' + addComma(element.換算数) + '</div>' +
            '<div class="tbody td-inventory td-input"><input type="text" name="" value="' + addComma(element.商品在庫数) + '" class="" min="0" max="1000000" step="1" data-target-bara="bara-' + cnt + '" data-target-bara-subtotal="bara-subtotal-' + cnt + '" data-target-total="modal-correction-inventory-count"></div>' +
            '<div class="tbody td-inventory bara-subtotal" id="bara-subtotal-' + cnt + '">' + addComma(element.バラ在庫数) + '</div>';
        if (item_back.length === index + 2) {
            tmp_add_html += '<div class="blank information-change-text">陳列情報変更</div>';
        } else if (item_back.length === index + 1) {
            tmp_add_html += '<div class="thead th-face">フェイス数</div>' +
                '<div class="thead th-stack">積上数</div>' +
                '<div class="thead th-depth">奥行数</div>' +
                '<div class="thead th-min">最低陳列数</div>' +
                '<div class="thead th-possible">陳列可能数</div>';
        } else {
            tmp_add_html += '<div class="blank"></div>';
        }
        tmp_add_html += '</div>';
        cnt++;
    });

    // 個別アイテム追記
    let min = max = 0;
    item_bara.forEach(function (elem, index2, array2) {
        tmp_add_html += '<div class="row-table input-row" id="input-row-' + cnt + '" data-item-id="' + elem.商品ID + '" data-base-item-id="' + elem.基準商品ID + '" ' +
            'data-dai="' + elem.台 + '" data-dan="' + elem.段 + '" data-ichi="' + elem.位置 + '" data-bara="' + elem.換算数 + '" data-stock="' + elem.商品在庫数 + '" ' +
            'data-stock-sum="' + elem.バラ在庫数 + '" data-min="' + elem.min + '" data-max="' + elem.max + '">' +
            '<div class="tbody td-location">' +
            elem.売場大名 + ' ' + elem.売場中名 + ' ' + elem.売場小名 + ' ' + elem.台 + '/' + elem.段 + '/' + elem.位置 + '<br>' + elem.商品ID + ' ' + elem.商品名 +
            '</div>' +
            '<div class="tbody td-bara" id="bara-' + cnt + '">' + elem.換算数 + '</div>' +
            '<div class="tbody td-inventory td-input"><input type="text" name="" value="' + addComma(elem.商品在庫数) + '" class="" min="0" max="1000000" step="1" data-target-bara="bara-' + cnt + '" data-target-bara-subtotal="bara-subtotal-' + cnt + '" data-target-total="modal-correction-inventory-count"></div>' +
            '<div class="tbody td-inventory bara-subtotal" id="bara-subtotal-' + cnt + '">' + addComma(elem.バラ在庫数) + '</div>' +
            '<div class="tbody td-face">' + elem.フェイス数 + '</div>' +
            '<div class="tbody td-stack">' + elem.積上数 + '</div>' +
            '<div class="tbody td-depth">' + elem.奥行陳列数 + '</div>' +
            '<div class="tbody td-min td-input"><input type="text" name="" value="' + addComma(elem.min) + '" class="stock-min" min="0" max="1000000" step="1" data-target-min="modal-stock-min-change"></div>' +
            '<div class="tbody td-possible td-input"><input type="text" name="" value="' + addComma(elem.max) + '" class="stock-max" min="0" max="1000000" step="1" data-target-max="modal-stock-max-change"></div>' +
            '</div>';
        cnt++;

        min += Number(elem.min);
        max += Number(elem.max);
    });

    // 最低在庫、最大在庫のセット TODO
    document.getElementById('modal-stock-min-change').innerText = addComma(min);
    document.getElementById('modal-stock-max-change').innerText = addComma(max);

    // htmlを追記
    correctionTable.insertAdjacentHTML('beforeend', tmp_add_html);
}

function addComma(num) {
    const number = parseFloat(num); // 入力値を数値に変換
    if (isNaN(number)) {
        return num; // 入力が数値ではない場合はそのまま返す
    }
    const formattedNumber = number.toLocaleString(); // 数値をカンマ区切り形式に変換
    return formattedNumber;
}

function removeComma(num) {
    const numberString = num.replace(/,/g, ''); // カンマを削除
    const number = parseFloat(numberString); // 数値に変換
    return isNaN(number) ? num : number; // 数値がNaNであれば入力値を返す
}
