/**
 * Laravelトークンを含むPost処理
 * @param String url 
 * @param String addHeaders 
 * @param {*} bodyData 
 * @param {*} funcSuccess 
 */
async function asyncPost(url, addHeaders, bodyData, funcSuccess) {
    let options = {
        method : "POST",
        headers : {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}
    };
    Object.keys(addHeaders).forEach(function (key) {
        options.headers[key] = addHeaders[key];
    });
    if(bodyData != null || bodyData != undefined) {
        options["body"] = bodyData;
    }

    const response = await fetch(url, options);
    if(response.ok){
        funcSuccess(response);
    }else{
        throw new Error(`response status:${response.status}`);
    }
}

/**
 * Cookieの取得
 * @param String name 
 * @returns 
 */
function getCookie(name) {
    const keyVal = new RegExp(name + '=([^¥S;]*)').exec(document.cookie + ';');
    if(keyVal) {
        return decodeURIComponent(keyVal[1]);;
    }
    return "";
}

/**
 * Cookieの設定
 * @param {*} name 
 * @param {*} value 
 * @param {*} limit 
 */
function setCookie(name, value, limit="") {
    let maxage = (limit == "") ? ""  : "; max-age=" + limit;
    document.cookie = name + "=" + encodeURIComponent(value) + maxage;
}
