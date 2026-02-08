/**
 * 競走馬スピナー
 */
import "./HorseSpinner.scss"

type Props = {
    isLoading : boolean;
}

const HorseSpinner = (props : Props) => {
    if(props.isLoading){
        return(
            <div className="horse-spinner"></div>
        );
    }else{
        return (<></>);
    }
};

export default HorseSpinner;