import React from "react";
import { useLocation, useNavigate } from "react-router-dom";


/**
 * タブヘッダー
 */

type Props = {
    isAdmin? : boolean;
}
const TabHeader = (props : Props) => {
    const location = useLocation();
    const navigate = useNavigate();
    const TAB_SHUTSUBA = 0;
    const TAB_PROFILE = 1;
    const TAB_SCRAPING = 9;
    
    
    /**
     * タブクリックイベント
     * @param tabID
     */
    const tabClickHandler = (tab:number) => {
        if(tab == TAB_SHUTSUBA && location.pathname != "/mobile/race") {
            navigate('/mobile/race');
        }else if(tab == TAB_PROFILE && location.pathname != "/mobile/profile") {
            navigate('/mobile/profile');
        }else if(tab == TAB_SCRAPING && location.pathname != "/mobile/scraping") {
            navigate('/mobile/scraping');
        }
    }

    const getClass = (tab:number) => {
        let active = "";
        if(tab == TAB_SHUTSUBA && location.pathname == "/mobile/race") {
            active = "active";
        }else if(tab == TAB_PROFILE && location.pathname == "/mobile/profile") {
            active = "active";
        }else if(tab == TAB_SCRAPING && location.pathname == "/mobile/scraping") {
            active = "active";
        }
        return "nav-link " + active;
    }

    return (
        <div className="w-100 p-1">
            <ul className="nav nav-tabs mb-2">
                <li className="nav-item">
                    <a className={getClass(TAB_SHUTSUBA)} 
                        data-tab={TAB_SHUTSUBA}
                        onClick={() => tabClickHandler(TAB_SHUTSUBA)}>出馬情報
                    </a>
                </li>
                <li className="nav-item">
                    <a className={getClass(TAB_PROFILE)} 
                        data-tab={TAB_PROFILE}
                        onClick={() => tabClickHandler(TAB_PROFILE)}>プロファイル
                    </a>
                </li>
                {props.isAdmin ?
                <li className="nav-item">
                    <a className={getClass(TAB_SCRAPING)} 
                        data-tab={TAB_SCRAPING}
                        onClick={() => tabClickHandler(TAB_SCRAPING)}>データ取得
                    </a>
                </li>
                : ""}
            </ul>
        </div>
    );
};

export default TabHeader;
