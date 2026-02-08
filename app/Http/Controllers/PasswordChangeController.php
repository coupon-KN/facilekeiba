<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\isEmpty;

/**
 * パスワード変更コントローラ
 */
class PasswordChangeController extends Controller
{
    const ERROR_MSG = "error_msg";

    /**
     * 初期表示
     */
    function index(Request $request) {
        if(!$request->session()->exists(config("const.SESSION_LOGIN_USER_NO"))){
            return redirect()->route('login'); 
        }

        $viewData["err_msg"] = $request->session()->get($this::ERROR_MSG);

        return view("password-change", $viewData);
    }

    /**
     * 再設定
     */
    function reset(Request $request) {
        $input = $request->all();
        $nowPass = $input["now_password"];
        $newPass = $input["new_password"];
        $rePass = $input["re_password"];

        // ユーザ情報取得
        $userData = [];
        $loginUserNo = session(config("const.SESSION_LOGIN_USER_NO"));
        $usersJson = json_decode(Storage::disk("public")->get("users.json"), true);
        foreach($usersJson as $val){
            if($loginUserNo == $val["user_no"]){
                $userData = $val;
                break;
            }
        }

        $isError = false;
        $isError = empty($nowPass) or empty($newPass) or empty($rePass);
        $isError = $isError || ($newPass != $rePass);
        $isError = $isError || ($userData["password"] !== hash('sha256', $nowPass));
        if($isError) {
            return redirect()->route("passchg")->withInput()->with($this::ERROR_MSG, '入力に誤りがあります');
        }

        // 再設定
        $userData["password"] = hash('sha256', $newPass);
        $contents = json_encode($usersJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        Storage::disk('public')->put("users.json", $contents);

        $request->session()->flush();
        return redirect()->route('login')->with(config('const.SYSTEM_MSG'), 'パスワードを変更しました');
    }


}
