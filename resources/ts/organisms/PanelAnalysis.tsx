import "./PanelAnalysis.scss"
import { IViewRaceData, IParentHorse } from "../interface/IHorseRace";


type Props = {
    isOpen : boolean;
    setIsOpen : any;
    viewData : IViewRaceData;
}

/**
 * 分析パネル
 */
const PanelAnalysis = (props : Props) => {
    const panelOpen = () => {
        if( props.isOpen ){
            return "open";
        }
        return "";
    }

    const parentView = (parent:IParentHorse) => {
        var str = parent.name;
        if( parent.typeString !== "" ){
            str += " (" + parent.typeString + ")";
        }
        if( parent.distanceString !== "" ){
            str += " (" + parent.distanceString + ")";
        }
        return str;
    }

    return(
        <div className={"analysis-panel " + panelOpen()}>
            <div className="parent-body p-2">
                <table className="w-100 fs14">
                    <thead className="text-center">
                        <tr>
                            <th>集計</th>
                        </tr>
                    </thead>
                    <tbody>
                    {props.viewData.horseArray.map((horse) =>
                        <>
                            <tr key={"prt" + horse.horseId}>
                                <td className={"ps-3 waku" + horse.waku}>{horse.umaban + "　" + horse.name}</td>
                            </tr>
                            <tr key={"sub" + horse.horseId}>
                                <td>
                                    {horse.historySummary.intervalDay !== 0 ? 
                                        <div>{"前走間隔：" + horse.historySummary.intervalDay + "日"}</div>
                                        : ""
                                    }
                                    <div>{"最高クラス：" + horse.historySummary.highestRank}</div>
                                    {horse.typeString !== "" ? 
                                        <div>{"タイプ：" + horse.typeString}</div>
                                        : ""
                                    }
                                    {horse.distanceString !== "" ? 
                                        <div>{"距離：" + horse.distanceString}</div>
                                        : ""
                                    }
                                    <div>血統</div>
                                    <div className="fs12">&nbsp;　父：{parentView(horse.dad)}</div>
                                    <div className="fs12">&nbsp;母父：{parentView(horse.mamSohu)}</div>
                                    <div className="fs12">&nbsp;　母：{parentView(horse.mam)}</div>
                                    {horse.historySummary.distanceScores.length > 0 ?
                                        <>
                                            <div>距離別スコア</div>
                                            {horse.historySummary.distanceScores.map((item) => (
                                                <div>{"　" + item.key + "m　　" + item.value + "点"}</div>
                                            ))}
                                        </>
                                        : ""}
                                    {horse.historySummary.trackScores.length > 0 ?
                                        <>
                                            <div>馬場別スコア</div>
                                            {horse.historySummary.trackScores.map((item) => (
                                                <div>{"　" + item.key + "　　" + item.value + "点"}</div>
                                            ))}
                                        </>
                                        : ""}
                                </td>
                            </tr>
                        </>
                    )}
                    </tbody>
                </table>
            </div>
            <button className="panel-close" onClick={() => props.setIsOpen(false)}>CLOSE</button>
        </div>
    );
};

export default PanelAnalysis;