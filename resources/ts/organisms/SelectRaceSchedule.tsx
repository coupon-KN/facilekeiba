import React, {useState} from 'react';
import "./SelectRaceSchedule.scss"
import * as constants from "../constants";
import { IRaceScheduleItem } from "../interface/IHorseRace";
import axios, { AxiosResponse }  from "axios";


type Props = {
    setRaceId : any;
    setLoading : any;
}

/**
 * レース選択コンポーネント
 */
const SelectRaceSchedule = (props : Props) => {
    const [targetDate, setTargetDate] = useState(new Date().toLocaleDateString('sv-SE'));
    const [scheduleArr, setScheduleArr] = useState<IRaceScheduleItem[]>();

    React.useEffect(() => {
        getSchedule(targetDate);
    }, []);

    /**
     * スケジュールデータ取得APIをコール
     */
    const getSchedule = async (selDate : string) => {
        props.setLoading(true);
        try{
            await axios.post(constants.HR_SCHDULE_URL, {sel_date : selDate}, {withCredentials: true})
            .then((response : AxiosResponse<IRaceScheduleItem[]>) => {
                setScheduleArr(response.data);
                props.setRaceId("");
            })
            .catch((error) => {
                console.log(error);
            });
        }
        catch(error) {
            console.log(error);
        }
        finally{
            props.setLoading(false);
        }
    }

    /**
     * 日付変更イベント
     */
    const changeDateHandle : React.ChangeEventHandler<HTMLInputElement> = async(e) => {
        setTargetDate(e.target.value);
        getSchedule(e.target.value);
    }

    /**
     * スケジュールチェンジイベント
     */
    const changeRaceIdHandle : React.ChangeEventHandler<HTMLSelectElement> = async(e) => {
        props.setRaceId(e.target.value);
    }
    
    return(
        <div className='sel-race-sche w-100 px-2'>
            <input type="date" className="p-1" value={targetDate} onChange={changeDateHandle} />
            <select className="p-1" onChange={changeRaceIdHandle}>
                { scheduleArr && scheduleArr.map((value) => 
                    <option value={value.id} key={value.id}>{value.name}</option>
                )}
            </select>
        </div>
    );
};

export default SelectRaceSchedule;