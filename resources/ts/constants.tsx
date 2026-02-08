/*
export const API_URL = "http://127.0.0.1:8000";
/*/
export const API_URL = "https://facilekeiba.sakuraweb.com";
//*/
export const LOGIN_CHECK_URL = API_URL + "/api/horserace/logincheck";
export const LOGIN_URL = API_URL + "/api/horserace/login";
export const HR_SCHDULE_URL = API_URL + "/api/horserace/schdule";
export const HR_RACEDATA_URL = API_URL + "/api/horserace/racedata";
export const HR_KAISAI_BABA_URL = API_URL + "/api/horserace/kaisaibaba";
export const HR_RACE_LIST_URL = API_URL + "/api/horserace/racelist";
export const HR_SCRAPING_RACE_URL = API_URL + "/api/horserace/scraping/racedata";
export const HR_SCRAPING_KAISAIBABA_URL = API_URL + "/api/horserace/scraping/kaisaibaba";
export const HR_WRITE_COMMENT_URL = API_URL + "/api/horserace/writecomment";
export const PROFILE_UPDATE_URL = API_URL + "/api/horserace/profile";

export const LOGIN_TOKEN = "facile-token";

export const getCookie = (name:string) => {
    const keyVal = new RegExp(name + '=([^¥S;]*)').exec(document.cookie + ';');
    if(keyVal) {
        return decodeURIComponent(keyVal[1]);;
    }
    return "";
}
export const setCookie = (name:string, value:string, limit:string = "") => {
    let maxage = (limit == "") ? ""  : "; max-age=" + limit;
    document.cookie = name + "=" + encodeURIComponent(value) + maxage;
}
