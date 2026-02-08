/**
 * Appファイル
 *   ルーティングを定義
 */

import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from "react-router-dom";
import PageFrame from "./templetes/page_frame";
import LoginPage from "./pages/LoginPage";
import FacileKeibaPage from "./pages/FacileKeibaPage";
import ProfilePage from "./pages/ProfilePage";
import ScrapingPage from "./pages/ScrapingPage";
import CheckAuth from "./molecules/CheckAuth";


const App = () => {
  return (
    <BrowserRouter>
        <PageFrame>
            <Routes>
                <Route path='/mobile/login' element={<LoginPage />} />
                <Route path='/mobile' element={<CheckAuth />}>
                    <Route path='/mobile/race' element={<FacileKeibaPage />} />
                    <Route path='/mobile/profile' element={<ProfilePage />} />
                    <Route path='/mobile/scraping' element={<ScrapingPage />} />
                </Route>
            </Routes>
        </PageFrame>
    </BrowserRouter>
    );
};

const container = document.getElementById('reactContents');
const root = createRoot(container!);
root.render(<App />);
