import React, { useState } from "react";
import { useLocation, useNavigate, Outlet } from 'react-router-dom';
import * as constants from "../constants";
import { ILoginUser } from "../interface/ILoginUser";
import HorseSpinner from "./HorseSpinner";
import UserContext from "../context/userContext";
import axios, { AxiosResponse }  from "axios";


/**
 * 認証ページ
 */
const CheckAuth = () => {
    const [isLogin, setIsLogin] = useState(false);
    const [userConTxt, setUserConTxt] = useState<ILoginUser|null>(null);
    const navigate = useNavigate();
    const location = useLocation();

    React.useEffect(() => {
        loginCheck();
    }, [isLogin]);


    /**
     * ログインチェック
     */
    const loginCheck = async() => {
        let loginCookie = constants.getCookie(constants.LOGIN_TOKEN);
        if(loginCookie == null || loginCookie.length == 0) {
            navigate('/mobile/login');
            return;
        }

        try{
            await axios.post(constants.LOGIN_CHECK_URL,[], { withCredentials: true})
            .then((response : AxiosResponse<ILoginUser>) => {
                setUserConTxt({
                    userNo: response.data.userNo,
                    userName: response.data.userName,
                    isAdmin: response.data.isAdmin,
                    token: response.data.token,
                    useStorage: response.data.useStorage,
                    setUserData: setUserConTxt
                });
                setIsLogin(true);
                if(!response.data.useStorage) {
                    window.localStorage.clear();
                }
            })
            .catch((error) => {
                console.log(error);
                navigate('/mobile/login');
            });
        }
        catch(error) {
            console.log(error);
            navigate('/mobile/login');
        }
        finally{
        }
        if(location.pathname == "/mobile") {
            navigate('/mobile/race');
        }

    }
    

    if(isLogin){
        return (
            <UserContext.Provider value={userConTxt}>
                <Outlet />
            </UserContext.Provider>
        );
    }else{
        return (<HorseSpinner isLoading={true} />);
    }
};

export default CheckAuth;
