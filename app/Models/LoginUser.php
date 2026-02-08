<?php
namespace App\Models;


/**
 * ログインユーザー情報
 */
class LoginUser
{
    /** ユーザーNo */
    public $userNo;

    /** 名前 */
    public $userName;
    
    /** 管理者 */
    public $isAdmin;

    /** トークン */
    public $token;

    /** トークン */
    public $useStorage;
}
