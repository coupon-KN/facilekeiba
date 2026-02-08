/**
 * PageFrame
 *   サイトの骨格
 * @param props 
 * @returns 
 */
import "./page_frame.scss"

const PageFrame = (props:any) => {
    return (
    <div className='page-frame'>
        <header></header>
        <div className='page-body'>
            {props.children}
        </div>
    </div>
    );
};

export default PageFrame;
