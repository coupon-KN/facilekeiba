import React from "react";
import {ILoginUser} from "../interface/ILoginUser";

const UserContext = React.createContext<ILoginUser|null>(null);
export default UserContext;
