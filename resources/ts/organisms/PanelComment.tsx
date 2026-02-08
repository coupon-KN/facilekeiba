import { useState, useEffect } from "react";
import "./PanelComment.scss"
import * as constants from "../constants";
import { IViewRaceData } from "../interface/IHorseRace";
import axios from "axios";


type Props = {
    isOpen : boolean;
    setIsOpen : any;
    selHorseId : string;
    viewData : IViewRaceData;
    setLoading : any;
}

/**
 * コメントパネル
 */
const PanelComment = (props : Props) => {
    const [commentMap, setCommentMap] = useState(new Map());

    useEffect(() => {
        // レースコメント
        commentMap.set(props.viewData.raceId, props.viewData.raceComment);
        // 馬
        props.viewData.horseArray.forEach((horse) => {
            commentMap.set(horse.horseId, horse.userComment);
        });
        setCommentMap(new Map(commentMap));

    }, []);

    const panelOpen = () => {
        if( props.isOpen ){
            if( props.selHorseId != "" ) {
                let element = document.getElementById("com" + props.selHorseId);
                if(element) element?.scrollIntoView();
            }
            return "open";
        }
        return "";
    }

    /**
     * コメント変更イベント
     */
    const blurCommentHandle : React.FocusEventHandler<HTMLInputElement> = async(e) => {
        let raceId = (e.target.dataset["raceId"] != undefined) ? e.target.dataset["raceId"] : "";
        let horseId = (e.target.dataset["horseId"] != undefined) ? e.target.dataset["horseId"] : "";
        let comment = e.target.value;
        let targetId = (raceId != "") ? raceId : horseId;

        if(commentMap.get(targetId) != comment) {
            commentMap.set(targetId, comment);
            setCommentMap(new Map(commentMap));

            // APIの呼び出し
            props.setLoading(true);
            let data = {
                race_id : raceId,
                horse_id : horseId,
                comment : comment
            };
            try{
                await axios.post(constants.HR_WRITE_COMMENT_URL, data, {withCredentials: true})
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
    }

    return(
        <div className={"comment-panel " + panelOpen()}>
            <div className="comment-body">
                <div className={"horse-head fs-3 rece-head"}>レースコメント</div>
                <CommentInput defVal={props.viewData.raceComment} raceId={props.viewData.raceId} blurEvent={blurCommentHandle} />
                {props.viewData.horseArray.map((horse) =>
                <div id={"com" + horse.horseId} key={"com" + horse.horseId}>
                    <div className={"horse-head fs-3 waku"+horse.waku}>
                        <div className="d-inline-block">{horse.umaban}</div>
                        <div className="d-inline-block">{horse.name}</div>
                    </div>
                    <CommentInput defVal={horse.userComment} horseId={horse.horseId} blurEvent={blurCommentHandle} />
                </div>
                )}
            </div>
            <button className="comment-close" onClick={() => props.setIsOpen(false)}>CLOSE</button>
        </div>
    );
};


type CommentProps = {
    defVal : string;
    raceId? : string;
    horseId? : string;
    blurEvent : any;
}
const CommentInput = (props:CommentProps) => {
    const [text, setText] = useState<string>("");
    const [isInputMode, setInputMode] = useState<boolean>(false);

    useEffect(() => {
        setText(props.defVal);
    }, [props.defVal]);

    const blurMethod : React.FocusEventHandler<HTMLTextAreaElement> = (e) => {
        setInputMode(false);
        props.blurEvent(e);
    }

    if(isInputMode){
        return (
            <textarea className="w-100 border-0" rows={3}
                onChange={(e) => setText(e.target.value)}
                data-race-id={props.raceId}
                data-horse-id={props.horseId}
                onBlur={(e) => blurMethod(e)}
            >{text}</textarea>
        );
    }else{
        return (
            <pre className="comment-comp w-100 border-0 p-2 mb-0 white-space"
                onClick={() => setInputMode(true)}
            >{text}</pre>
        );
    }
};
export default PanelComment;
