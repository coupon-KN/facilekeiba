/**
 * 競馬に関するインターフェース
 */

// スケジュール
export interface IRaceScheduleItem {
    id : string;
    name : string;
}

// レース情報
export interface IViewRaceData {
    /** レースID */
    raceId : string;
    /** レースNo */
    raceNo : string;
    /** 開催馬場コード */
    babaCode : number;
    /** 開催馬場名 */
    babaName : string;
    /** 開催場 */
    kaisai : string;
    /** レース名 */
    raceName : string;
    /** 出走時刻 */
    startingTime : string;
    /** 種類(1:芝、2:ダート、3:障害) */
    groundType : number;
    /** 距離 */
    distance : number;
    /** 向き(1:左、2:右) */
    direction : number;
    /** レース等級 */
    raceGrade : string;
    /** 頭数 */
    horseCount : number;
    /** レース情報 */
    raceInfo : string;
    /** コースメモ */
    courseMemo : string;
    /** コースイメージ */
    courseImage : string;
    /** レースコメント */
    raceComment : string;
    /** 競走馬情報配列 */
    horseArray : IViewHorseData[];
};

// 競走馬情報
export interface IViewHorseData {
    /** 馬ID */
    horseId : string;
    /** 枠番 */
    waku : number;
    /** 馬番 */
    umaban : number;
    /** 名前 */
    name : string;
    /** 年齢 */
    age : string;
    /** 斤量 */
    kinryo : string;
    /** 騎手 */
    jockey : string;
    /** 除外フラグ */
    isCancel : Boolean;
    /** 成績 */
    recode : string;
    /** 獲得賞金 */
    totalPrize : string;
    /** 勝率 */
    winRate : string;
    /** 複勝率 */
    podiumRate : string;
    /** 脚質(逃げ) */
    nigePct : number;
    /** 脚質(先行) */
    senkoPct : number;
    /** 脚質(差し) */
    sashiPct : number;
    /** 脚質(追込) */
    oikomiPct : number;
    /** ユーザーコメント */
    userComment : string;

    /** 父親の情報 */
    dad : IParentHorse;
    /** 父方の祖父の情報 */
    dadSohu : IParentHorse;
    /** 父方の祖母の情報 */
    dadSobo : IParentHorse;
    /** 母親の情報 */
    mam : IParentHorse;
    /** 母方の祖父の情報 */
    mamSohu : IParentHorse;
    /** 母方の祖母の情報 */
    mamSobo : IParentHorse;

    /** タイプ：スピード(比率) */
    speed : number;
    /** タイプ：パワー(比率) */
    power : number;
    /** タイプ：スタミナ(比率) */
    stamina : number;
    /** スプリント(1200m) */
    sprint : number;
    /** マイル(1600m) */
    mile : number;
    /** 中距離(2000m) */
    middle : number;
    /** 長距離(2400m) */
    long : number;

    /** タイプ文字列 */
    typeString : string;
    /** 距離文字列 */
    distanceString : string;
    /** 集計結果 */
    historySummary : IHistorySummary;

    /** 履歴 */
    recodeArray : IRaceHistory[];
}

// レース履歴
export interface IRaceHistory {
    /** 日付 */
    date : string;
    /** 開催馬場 */
    baba : string;
    /** 天気 */
    tenki : string;
    /** レースNo */
    raceNo : number;
    /** レース名 */
    raceName : string;
    /** レースグレード */
    raceGrade : string;
    /** レースURL */
    raceUrl : string;
    /** 頭数 */
    horseCount : number;
    /** 枠番 */
    waku : number;
    /** 馬番 */
    umaban : number;
    /** オッズ */
    odds : number;
    /** 人気 */
    ninki : number;
    /** 着順 */
    rankNo : string;
    /** 騎手 */
    jockey : string;
    /** 斤量 */
    kinryo : number;
    /** 距離 */
    distance : number;
    /** 種類(1:芝、2:ダート、3:障害) */
    groundType : number;
    /** 種類略称(1:芝、2:ダ、3:障) */
    groundShortName : string;
    /** 馬場状態 */
    condition : string;
    /** タイム */
    time : string;
    /** 着差 */
    difference : string;
    /** 通過 */
    pointTime : string;
    /** ペース(前半3ハロン) */
    firstPace : string;
    /** ペース(後半3ハロン) */
    latterPace : string;
    /** ペース区分(S,M,H) */
    paceKbn : string;
    /** 上り3ハロン */
    agari600m : string;
    /** 上りランク */
    agariRank : number;
    /** 馬体重 */
    weight : string;
    /** 勝ち馬 */
    winHorse : string;
}

// 親馬情報
export interface IParentHorse {
    /** 馬ID */
    horseId : string;
    /** 名前 */
    name : string;
    /** 成績 */
    recode : string;
    /** 勝率 */
    winRate : string;
    /** 複勝率 */
    podiumRate : string;

    /** タイプ：スピード(比率) */
    speed : number;
    /** タイプ：パワー(比率) */
    power : number;
    /** タイプ：スタミナ(比率) */
    stamina : number;
    /** スプリント(1200m) */
    sprint : number;
    /** マイル(1600m) */
    mile : number;
    /** 中距離(2000m) */
    middle : number;
    /** 長距離(2400m) */
    long : number;
    /** タイプ文字列 */
    typeString : string;
    /** 距離文字列 */
    distanceString : string;
}

// 集計情報
export interface IHistorySummary {
    /** 前走からの間隔 */
    intervalDay : number;
    /** 好走した中で一番高いクラス等級 */
    highestRank : string;
    /** 距離別スコア配列 */
    distanceScores : ISelectItem[];
    /**  馬場別スコア配列 */
    trackScores : ISelectItem[];
}

// keyValue
export interface ISelectItem {
    key : string;
    value : string;
}

// レース情報アイテム
export interface IRaceListItem {
    id : string;
    name : string;
    status : number;
}

// 設定用スケジュールデータ
export interface ISettingSchedule {
    central : ISelectItem[];
    region : ISelectItem[];
    schedule : ISettingScheduleItem[];
}

export interface ISettingScheduleItem {
    id : string;
    name : string;
    num : number;
}

// スコアデータ
export interface IScoringItem {
    id : string;
    waku : string,
    umaban : number;
    name : string;
    interval_day : number,
    highest_rank : string,
    distance_scores : ISelectItem[],
    track_scores : ISelectItem[],
    score : number;
    anomaly : number;
}
