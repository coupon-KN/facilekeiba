import React, { useState } from "react";
import { useNavigate  } from 'react-router-dom';
import * as constants from "../constants";
import { ILoginUser } from "../interface/ILoginUser";
import HorseSpinner from "../molecules/HorseSpinner";
import axios, { AxiosResponse }  from "axios";


/**
 * 認証ページ
 */
const LoginPage = () => {
    const [isLoading, setIsLoading] = useState(false);
    const [userId, setUserId] = useState("");
    const [userPass, setUserPass] = useState("");
    const navigate = useNavigate();


    /**
     * ログインボタン
     */
    const loginButtonHandle = async() => {
        setIsLoading(true);
        try{
            axios.post(constants.LOGIN_URL, {user_id : userId, password : userPass})
            .then((response : AxiosResponse<ILoginUser>) => {
                if(response.data.token != ""){
                    document.cookie = constants.LOGIN_TOKEN + "=" + response.data.token + ";path=/;max-age=28800";
                    navigate('/mobile/race');
                    setIsLoading(false);
                }
            })
            .catch((error) => {
                console.log(error);
                setIsLoading(false);
            });
        }
        catch(error) {
            console.error(error);
            setIsLoading(false);
        }
    }

    return (
        <div className="auth-page w-100 h-100" style={{position: "relative"}}>
            <div className="mx-auto login-block" style={{width : "250px"}}>
                <div className="pt-4">
                    <label className="form-label w-100">ID
                        <input type="text" className="form-control" value={userId} onChange={(e) => setUserId(e.target.value)} />
                    </label>
                    <label className="form-label w-100">パスワード
                        <input type="password" className="form-control" value={userPass} onChange={(e) => setUserPass(e.target.value)} />
                    </label>
                </div>
                <div className="pt-4">
                    <button type="button" className="btn btn-primary btn-lg w-100" onClick={loginButtonHandle}>ログイン</button>
                </div>
            </div>

            <HorseSpinner isLoading={isLoading} />
        </div>
    );
};

export default LoginPage;
