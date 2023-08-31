
var login_id = document.getElementById('input-login-id');
var password = document.getElementById('input-password');

// form submit
function LoginCheck() {

    var parameter = {
        login_id: login_id.value,
        password: password.value,
    };

    var url = location.protocol + '//' + location.hostname + '/login/login.php';

    postData(url, parameter, function (error, data) {
        if (error) {
            alert(error);
        } else {
            if (data.status === "error") {
                alert(data.message);
                return;
            }
            window.location.href = data.redirect_url;
        }
    });

    return false;

}

function postData(url, data, callback) {
    var xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                callback(null, response);
            } else {
//                callback(new Error('Request failed: ' + xhr.status));
                callback(new Error('ネットワークに接続されていません。接続を確認してもう一度お試しください。'));
            }
        }
    };

    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.send(JSON.stringify(data));
}
