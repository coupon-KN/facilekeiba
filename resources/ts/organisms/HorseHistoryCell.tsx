import "./HorseHistoryCell.scss"
import { IViewHorseData } from "../interface/IHorseRace";


type Props = {
    horseData? : IViewHorseData;
}

/**
 * 競走馬の競争歴セル
 */
const HorseHistoryCell = (props : Props) => {
    const horseData = props.horseData;

    if(horseData && horseData.recodeArray && horseData.recodeArray.length > 0) {
        return (
            <div className="horse-history-cell bg-white">
                {horseData.recodeArray.map((his, index) =>
                <div key={index} className={"row m-0 border fs12 rank" + his.rankNo}>
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
                            <span className='w-100 d-inline-block'>{his.time + " (" + his.difference + ")　" + his.firstPace + " - " + his.latterPace + "　3F：" + his.agari600m + (his.agariRank<=3 ? "(" + his.agariRank + "位)" : "")}</span>
                        </div>
                        <div>{his.pointTime}</div>
                        <div>{his.winHorse}</div>
                    </div>
                </div>
                )}
            </div>
        );
    }
    else
    {
        return <div></div>
    }
};

export default HorseHistoryCell;