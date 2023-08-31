
let updateConfirmModalEle = document.getElementById('update-confirm-modal');
let updateConfirmModal = new bootstrap.Modal(updateConfirmModalEle);
let updateConfirmMessage = document.getElementById('update-confirm-message');

// 未対応品のみ表示 unsupported-recommend-flg
let unsupportedFlg = document.getElementById('only-unsupported-recommend');
const itemWraps = document.getElementsByClassName("item-wrap");
if (JSON.parse(localStorage.getItem("unsupported-recommend-flg"))) {
    unsupportedHidden();
    unsupportedFlg.checked = true;
}

// 「未対応品のみ」のcheck操作
unsupportedFlg.addEventListener('change', () => {
    if (unsupportedFlg.checked) {
        unsupportedHidden();
        localStorage.setItem("unsupported-recommend-flg", JSON.stringify(true));
    } else {
        document.getElementById('main').classList.remove('supported-item-hidden');
        for (const element of itemWraps) {
            element.classList.remove('section-supported');
        }
        localStorage.setItem("unsupported-recommend-flg", JSON.stringify(false));
    }
});

// 未対応品の非表示
function unsupportedHidden() {
    document.getElementById('main').classList.add('supported-item-hidden');
    // itemが全てhiddenの場合はsectionまるごと非表示
    for (const element of itemWraps) {
        checkItemDetailWrap(element);
    }
}

// item-wrap内の表示数と非表示数を比較
function checkItemDetailWrap(element) {
    // 子のitem-detail-wrapをカウント
    let itemDetailCnt = element.querySelectorAll('.item-detail-wrap').length;
    // 子のitem-supportedをカウント
    let itemSupportedCnt = element.querySelectorAll('.item-supported').length;
    // 同数なら親に非表示クラスを付与 section-supported
    if (itemDetailCnt === itemSupportedCnt) {
        element.classList.add('section-supported');
    }
}

// status更新
function btnStatusSet() {
    const UpdateData = document.getElementById('update-data-' + this.dataset.itemCount);
    const parameter = {
        store_id: UpdateData.dataset.storeId,
        uriba_id: UpdateData.dataset.uribaId,
        dai: UpdateData.dataset.dai,
        dan: UpdateData.dataset.dan,
        ichi: UpdateData.dataset.ichi,
        item_id: UpdateData.dataset.itemId,
        RecID: UpdateData.dataset.recId,
        status: UpdateData.dataset.status,
        uridai: UpdateData.dataset.uriDai
    };

    const resultModal = new bootstrap.Modal(document.getElementById('modal-update-status'));
    let messageElement = document.getElementById("modal-update-status-message");
    let itemCount = UpdateData.dataset.itemCount;

    fetch(location.protocol + '//' + location.hostname + '/web/recommend/update-status.php',
        {
            method: 'POST', // HTTP-メソッドを指定
            headers: {'Content-Type': 'application/json'}, // jsonを指定
            body: JSON.stringify(parameter),
        }
    ).then(response => response.json())
        .then(res => {
            // statusが更新されていたら（別の端末操作されていたら）モーダルを表示
            let message = res.message;
            if (res.success === true) {
                message = res.message;
                // 自分以外のボタンを非表示
                document.getElementById('btn-complete-' + itemCount + '-confirm').style.display = "none";
                document.getElementById('btn-pending-' + itemCount + '-confirm').style.display = "none";
                document.getElementById('btn-not-available-' + itemCount + '-confirm').style.display = "none";

                // 自分にdisabledクラスをつけて表示
                let targetBtn;
                if (UpdateData.dataset.status === '1') {
                    targetBtn = document.getElementById('btn-complete-' + itemCount + '-confirm');
                } else if (UpdateData.dataset.status === '2') {
                    targetBtn = document.getElementById('btn-pending-' + itemCount + '-confirm');
                } else {
                    targetBtn = document.getElementById('btn-not-available-' + itemCount + '-confirm');
                }
                targetBtn.classList.add('disabled');
                targetBtn.style.display = "block";

                // 上のwrapに背景色クラス（bg-light-dark）をつける
                document.getElementById( itemCount + '-wrap').classList.add('bg-light-dark', 'item-supported');

                // 売場小まるごと非表示チェック item-wrapB
                let selfBtn = document.getElementById('btn-complete-' + itemCount + '-confirm');
                let parentItemWrap = selfBtn.closest('.item-wrap');
                checkItemDetailWrap(parentItemWrap);

                // 対応数、未対応数の更新
                let unsupportedNumber = parseInt(document.getElementById("unsupported-number").textContent);
                let supportedNumber = parseInt(document.getElementById("supported-number").textContent);
                document.getElementById("unsupported-number").textContent = unsupportedNumber - 1;
                document.getElementById("supported-number").textContent = supportedNumber + 1;
            }

            updateConfirmModal.hide();
            messageElement.innerHTML = message;
            resultModal.show();
        })
        .catch(error => {
            // 通信不可の場合（レスポンスがない）はエラー表示
            updateConfirmModal.hide();
            messageElement.innerHTML = 'ネットワークの接続がありません、もう一度お試しください。<br>解決しない場合は管理者まで連絡してください。';
            resultModal.show();
        });
}

// 実行ボタンのセット
const btnRecChangeStatus = document.getElementById('btn-rec-change-status');
btnRecChangeStatus.addEventListener('click', btnStatusSet, false);

// classの値をもつ要素をすべて取得してクリックイベントを適用
const btnShowStatusUpdateModal = document.getElementsByClassName('btn-show-status-update-modal');
for(const element of btnShowStatusUpdateModal) {
    element.addEventListener('click', setupdateConfirmModal, false);
}

// 実行確認モーダルの設定
function setupdateConfirmModal() {
    // 更新ステータスの確認
    if(this.dataset.status === "1") {
        updateConfirmMessage.innerText = "完了にします。よろしいですか？";
    } else if(this.dataset.status === "2") {
        updateConfirmMessage.innerText = "保留にします。よろしいですか？";
    } else if(this.dataset.status === "3") {
        updateConfirmMessage.innerText = "不可にします。よろしいですか？";
    }
    let UpdateData = document.getElementById('update-data-' + this.dataset.itemCount);
    UpdateData.dataset.status = this.dataset.status;
    updateConfirmModalEle.querySelector('.btn-confirm-submit').dataset.itemCount = this.dataset.itemCount;
    updateConfirmModal.show();
}
