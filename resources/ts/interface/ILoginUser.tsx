
// ユーザーデータ
export interface ILoginUser {
    /** ユーザーNo */
    userNo : string;

    /** 名前 */
    userName : string;
    
    /** 管理者 */
    isAdmin : boolean;

    /** トークン */
    token : string;

    /** ローカルストレージの使用 */
    useStorage : boolean;

    /** 更新用メソッド */
    setUserData: (data:ILoginUser) => void;
}
