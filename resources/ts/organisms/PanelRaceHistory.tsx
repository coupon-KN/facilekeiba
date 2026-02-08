import {useEffect} from "react";
import "./PanelRaceHistory.scss"
import { IViewRaceData, IViewHorseData } from "../interface/IHorseRace";


type Props = {
    isOpen : boolean;
    setIsOpen : any;
    selHorseId : string;
    viewData : IViewRaceData;
    markMap : Map<string, string>;
    changeMarkMethod : any;
}

/**
 * 履歴パネル
 */
const PanelRaceHistory = (props : Props) => {

    useEffect(() => {
        if( props.isOpen ){
            let element = document.getElementById("his" + props.selHorseId);
            if(element) element?.scrollIntoView();
        }
    }, [props.isOpen]);

    const HorseHeadTable = (horse : IViewHorseData) => {
        return <>
            <table className={"horse-head waku" + horse.waku}>
                <tr>
                    <td className="text-center fs-3">{horse.umaban}</td>
                    <td className="text-center fs-2" onClick={() => props.changeMarkMethod(horse.horseId)}>
                        {horse.isCancel ? <span className="fs14">除外</span> : props.markMap.get(horse.horseId)}
                    </td>
                    <td>
                        <div className="fs-4">{horse.name}</div>
                        <div className="fs14">
                            <span className="d-inline-block w-25">逃げ{horse.recodeArray.length > 0 ? horse.nigePct : 0}%</span>
                            <span className="d-inline-block w-25">先行{horse.recodeArray.length > 0 ? horse.senkoPct : 0}%</span>
                            <span className="d-inline-block w-25">差し{horse.recodeArray.length > 0 ? horse.sashiPct : 0}%</span>
                            <span className="d-inline-block w-25">追込{horse.recodeArray.length > 0 ? horse.oikomiPct : 0}%</span>
                        </div>
                    </td>
                </tr>
            </table>
        </>;
    }

    return(
        <div className={"history-panel " + (props.isOpen ? "open" : "")}>
            <div className="history-body">
                {props.viewData.horseArray.map((horse) =>
                <div id={"his" + horse.horseId} key={horse.horseId}>
                    {HorseHeadTable(horse)}

                    {!horse.isCancel ? horse.recodeArray.map((his) =>
                        <div className={"row m-0 border fs12 rank" + his.rankNo} key={self.crypto.randomUUID()}>
                            <div className="col-4 p-1">
                                <div>{his.date}</div>
                                <div>{his.baba}{(his.raceGrade != "" ? "  " + his.raceGrade : "")}</div>
                                <div><a href={his.raceUrl} target='_blank'>{his.raceName}</a></div>
                                <div>{his.groundShortName + his.distance + " " + his.condition}</div>
                            </div>
                            <div className="col-1 p-1 fs-4">
                                <div>{his.rankNo}</div>
                            </div>
                            <div className="col-7 p-1">
                                <div>
                                    <span className='w-50 d-inline-block'>{his.jockey}</span>
                                    <span className='w-25 d-inline-block'>{his.kinryo}</span>
                                    <span className='w-25 d-inline-block'>{his.weight}</span>
                                </div>
                                <div>
                                    <span className='w-100 d-inline-block'>{his.horseCount + "頭　" + his.umaban + "番　" + his.ninki + "人気(" + his.odds + ")"}</span>
                                </div>
                                <div>
                                    <span className='w-100 d-inline-block'>{his.time + " (" + his.difference + ")　" + his.firstPace + " - " + his.latterPace + " (" + his.agari600m + ")"}</span>
                                </div>
                                <div>{his.pointTime}</div>
                                <div>{his.winHorse}</div>
                            </div>
                        </div>
                    ) : ""}
                </div>
                )}
            </div>
            <button className="panel-close" onClick={() => props.setIsOpen(false)}>CLOSE</button>
        </div>
    );
};

export default PanelRaceHistory;