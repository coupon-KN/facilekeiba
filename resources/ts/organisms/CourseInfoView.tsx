/**
 * コース情報のモーダル
 */
import React, { useState } from "react";
import "./CourseInfoView.scss"

type Props = {
    isOpen : boolean;
    setOpen : any;
    src : string;
    memo : string;
}

const CourseInfoView = (props : Props) => {
    if(props.isOpen){
        return(
            <div className="course-view" onClick={() => props.setOpen(false)}>
                <div>
                    <img src={props.src} />
                    <div className="p-2">{props.memo}</div>
                </div>
            </div>
        );
    }else{
        return (<></>);
    }
};

export default CourseInfoView;