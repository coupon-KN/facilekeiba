import React, { useContext, useState } from "react";
import "./ScrapingPage.scss"
import * as constants from "../constants";
import TabHeader from "../molecules/TabHeader";
import HorseSpinner from "../molecules/HorseSpinner";
import UserContext from "../context/userContext";
import { ILoginUser } from "../interface/ILoginUser";
import axios from "axios";


/**
 * プロファイルコンテンツ
 */
const ProfilePage = () => {
    const userConTxt = useContext(UserContext);
    const [isLoading, setIsLoading] = useState(false);
    const [lsData, setLsData] = useState<string[]>([]);


    React.useEffect(() => {
        let tmpArr = [];
         for (var i = 0, length = localStorage.length; i < length; ++i) {
            let key = localStorage.key(i);
            if(key) tmpArr.push(key);
        }
        setLsData(tmpArr);
    }, []);

    /**
     * ローカルストレージ使用フラグの更新
     */
    const ChangeLocalStorage = async (flg:boolean) => {
        setIsLoading(true);
        try{
            await axios.post(constants.PROFILE_UPDATE_URL, {use_storage : flg}, { withCredentials: true})
            .then(() => {
                let newUserData: ILoginUser = Object.assign({}, userConTxt);
                newUserData.useStorage = flg;
                userConTxt?.setUserData(newUserData);
            })
            .catch((error) => {
                console.log(error);
            });
        }
        catch(error) {
            console.error(error);
        }
        finally{
            setIsLoading(false);
        }
    }

    /**
     * ローカルストレージの削除
     */
    const clearLocalstorage = (key : string) => {
        if(key !== ""){
            window.localStorage.removeItem(key);
            let tmpArr = lsData.filter(item => item !== key);
            setLsData(tmpArr);
        }else{
            window.localStorage.clear();
            setLsData([]);
        }
    }

    return (
        <div className="profile">
            <TabHeader isAdmin={userConTxt?.isAdmin} />

            <div className="p-2">
                <label className="form-label w-100">ユーザー名
                    <input type="text" className="form-control" disabled={true} value={userConTxt?.userName} />
                </label>
            </div>
            <div className="p-2">
                <span className="me-3">ローカルストレージの使用</span>
                {userConTxt?.useStorage ?
                    <button type="button" className="btn btn-primary btn-sm" onClick={() => ChangeLocalStorage(false)}>ON</button>
                : 
                    <button type="button" className="btn btn-secondary btn-sm" onClick={() => ChangeLocalStorage(true)}>OFF</button>
                }
            </div>

            <div className="p-2">
                <span className="me-3">ローカルストレージの一覧</span>
                <button type="button" className="btn btn-warning btn-sm" onClick={() => clearLocalstorage("")}>全データ削除</button>
            </div>
            <div className="p-2">
                { lsData.map((item, index) => (
                    <div key={index}>
                        <span className="mx-3">{item}</span>
                        <button type="button" className="btn btn-warning btn-sm" onClick={() => clearLocalstorage(item)}>削除</button>
                    </div>
                ))}
            </div>
            <HorseSpinner isLoading={isLoading} />
        </div>
    );
};

export default ProfilePage;
