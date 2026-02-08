import React, { useContext, useState, Fragment } from "react";
import "./FacileKeibaPage.scss"
import * as constants from "../constants";
import { IViewRaceData, IViewHorseData, IScoringItem } from "../interface/IHorseRace";
import UserContext from "../context/userContext";
import TabHeader from "../molecules/TabHeader";
import SelectRaceSchedule from "../organisms/SelectRaceSchedule";
import HorseHistoryCell from "../organisms/HorseHistoryCell";
import PanelAnalysis from "../organisms/PanelAnalysis";
import PanelComment from "../organisms/PanelComment";
import CourseInfoView from "../organisms/CourseInfoView";
import HorseSpinner from "../molecules/HorseSpinner";
import axios, { AxiosResponse }  from "axios";


/**
 * 出馬情報ページ
 */
const FacileKeibaPage = () => {
    const userConTxt = useContext(UserContext);
    const [isLoading, setIsLoading] = useState(false);
    const [selectRaceId, setSelectRaceId] = useState("");

    const [raceData, setRaceData] = useState<IViewRaceData | null>(null);
    const [isOpenHisRowArray, setOpenHisRowArray] = useState<boolean[]>([]);
    const [markMap, setMarkMap] = useState(new Map());
    const [scoreArray, setScoreArray] = useState<IScoringItem[]>([]);

    const [isAnalysisPnlOpen, setAnalysisPnlOpen] = useState(false);
    const [isCommentPnlOpen, setCommentPnlOpen] = useState(false);
    const [isCourse, setIsCourse] = useState(false);


    React.useEffect(() => {
        // レースデータ取得
        getRaceData();

    }, [selectRaceId]);


    /**
     * レースデータ取得
     */
    const getRaceData = async() => {

        // ローカルストレージからデータ取得
        if(userConTxt?.useStorage) {
            let storageData =  window.localStorage.getItem(selectRaceId);
            if(storageData != null) {
                let dt = JSON.parse(storageData);
                FillRaceData(dt);
                return;
            }
        }
        // APIからデータ取得
        setIsLoading(true);
        try{
            await axios.post(constants.HR_RACEDATA_URL, {race_id : selectRaceId}, {withCredentials: true})
            .then((response : AxiosResponse<IViewRaceData>) => {
                if(response.status == 200){
                    FillRaceData(response.data);
                    if(userConTxt?.useStorage) {
                        let storageVal = JSON.stringify(response.data);
                        window.localStorage.setItem(selectRaceId, storageVal);
                    }
                }else{
                    setRaceData(null);
                }
                console.log(response);
            })
            .catch((error) => {
                console.log(error);
            });
        }
        catch(error) {
            console.log(error);
        }
        finally{
            setIsLoading(false);
        }
    }

    /**
     * レースデータを反映
     */
    const FillRaceData = (raceData:IViewRaceData) => {
        // 行を非表示
        let closeArray = new Array<boolean>(raceData.horseCount);
        for(let i=0; i<closeArray.length; i++){
            closeArray[i] = false;
        }
        setOpenHisRowArray(closeArray);

        // マーク情報をMapに格納
        raceData.horseArray.forEach((horse : IViewHorseData) => {
            markMap.set(horse.horseId, constants.getCookie(horse.horseId));
        });
        setMarkMap(new Map(markMap));
        // スコアを初期化
        let scoreTempArray = new Array<string>(raceData.horseCount);
        for(let i=0; i<scoreTempArray.length; i++){
            scoreTempArray[i] = "";
        }
        //setScoreArray(scoreTempArray);
        setScoreArray([]);
        // レース情報を設定
        setRaceData(raceData);
    }


    /**
     * 馬名セルのクリックイベント
     */
    const NameCellClickHandle = (index:number) => {
        let copyArray = isOpenHisRowArray.concat();
        copyArray[index] = !copyArray[index];
        setOpenHisRowArray(copyArray);
    }

    /**
     * 全て開くボタン
     */
    const AllOpenClickHandle = () => {
        let copyArray = isOpenHisRowArray.concat();
        for(let i=0; i<copyArray.length; i++){
            copyArray[i] = true;
        }
        setOpenHisRowArray(copyArray);
    }


    const getRowStatus = (horse : IViewHorseData, index:number) => {
        let strClass = "normal";
        if(horse.isCancel || markMap.get(horse.horseId) == "消") {
            strClass = "delete";
        }
        strClass += (isOpenHisRowArray[index] ? " opend" : "");
        return strClass;
    }

    const MarkClickHandle = (horseId:string) => {
        if(horseId == "") return;
        // マーク変更
        const markArr = ["", "◎", "〇", "▲", "△", "☆", "消"];
        let currentMark = markMap.get(horseId);
        let setMark = "";
        for(let i=0; i<markArr.length-1; i++) {
            if(currentMark == markArr[i]){
                setMark = markArr[i+1]; 
                break;
            }
        }
        markMap.set(horseId, setMark);
        setMarkMap(new Map(markMap));
        constants.setCookie(horseId, setMark, "259200");
    }

    return (
        <div className="facile-keiba">
            <div className="race-info">
                <TabHeader isAdmin={userConTxt?.isAdmin} />

                {/* レース選択 */}
                <SelectRaceSchedule setRaceId={setSelectRaceId} setLoading={setIsLoading} />

                {/* 出馬情報 */}
                {raceData !== null ?
                <div className="shutsuba-info px-2 pb-2">
                    <div className="mb-2">
                        <div className="fs-3">{raceData.raceName}
                            <span className="fs-5">{"(" + raceData.raceGrade + ")"}</span>
                        </div>
                        <div>{raceData.raceInfo}</div>
                    </div>
                    <table className="w-100 tbl-race">
                        <thead className="text-center">
                            <tr className="fs12">
                                <th>枠</th>
                                <th>馬<br />番</th>
                                <th>印</th>
                                <th>馬名</th>
                            </tr>
                        </thead>
                        <tbody>
                            {raceData.horseArray.map((h,index) =>
                            <Fragment key={self.crypto.randomUUID()}>
                                <tr className={"horse-info " + getRowStatus(h, index)}>
                                    <td className={"text-center fs12 waku"+h.waku}>{h.waku}</td>
                                    <td className="text-center fs12">{h.umaban}</td>
                                    <td className="text-center fs-2" data-index={index} onClick={ () => MarkClickHandle(h.horseId)}
                                        >{h.isCancel ? <span className="fs14">除外</span> : markMap.get(h.horseId)}</td>
                                    <td className="ps-1 pe-1" onClick={() => {NameCellClickHandle(index)}}>
                                        <div className="d-inline-block w-75">{h.name}</div>
                                        <div className="d-inline-block w-25 fs12">{h.age + "　" + (scoreArray.length > 0 ? scoreArray[index].score + "点" : "")}</div>
                                        <div className="d-inline-block w-100 fs12">
                                            <span>{h.jockey}</span>
                                            <span>({h.kinryo})</span>
                                            <span className="float-end">{h.recode}&nbsp;&nbsp;勝{h.winRate}%&nbsp;&nbsp;複勝{h.podiumRate}%</span>
                                        </div>
                                        {h.recodeArray.length > 0 ?
                                        <>
                                            <div className="d-inline-block w-25 fs12">逃げ{h.nigePct}%</div>
                                            <div className="d-inline-block w-25 fs12">先行{h.senkoPct}%</div>
                                            <div className="d-inline-block w-25 fs12">差し{h.sashiPct}%</div>
                                            <div className="d-inline-block w-25 fs12">追込{h.oikomiPct}%</div>
                                        </>
                                        : ""}
                                    </td>
                                </tr>
                                <tr key={self.crypto.randomUUID()} className={isOpenHisRowArray[index] ? "open" : "close"}>
                                    <td colSpan={4} className="bg-light">
                                        <HorseHistoryCell horseData={h} />
                                    </td>
                                </tr>
                            </Fragment>
                            )}
                        </tbody>
                    </table>
                    {/* 分析パネル */}
                    <PanelAnalysis isOpen={isAnalysisPnlOpen} setIsOpen={setAnalysisPnlOpen} viewData={raceData} />
                    {/* コメントパネル */}
                    <PanelComment isOpen={isCommentPnlOpen} setIsOpen={setCommentPnlOpen} selHorseId="" viewData={raceData} setLoading={setIsLoading} />
                    {/* コース情報パネル */}
                    <CourseInfoView isOpen={isCourse} setOpen={setIsCourse} src={raceData.courseImage} memo={raceData.courseMemo} />
                </div>
                :
                <div className="p-2">
                    <h5>データがありません</h5>
                </div>
                }
            </div>

            <footer>
                <a className="btn-door" onClick={AllOpenClickHandle}></a>
                <a className="btn-analysis" onClick={() => setAnalysisPnlOpen(true)}></a>
                <a className="btn-comment" onClick={() => setCommentPnlOpen(true)}></a>
                <a className="btn-course" onClick={() => setIsCourse(true)}></a>
            </footer>
            <HorseSpinner isLoading={isLoading} />
        </div>
    );

};


export default FacileKeibaPage;
