import { useTheme } from './context/ThemeContext';
import { useState, useEffect } from 'react';
import { ThemeProvider as MuiThemeProvider } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import { lightTheme, darkTheme } from './theme/theme';
import { NotificationProvider } from './context/NotificationContext';
import { HomeScrollProvider, useHomeScroll } from './context/HomeScrollContext';
import { useAuth } from './context/AuthContext';
import { Routes, Route, Navigate, useLocation } from 'react-router-dom';
import useMediaQuery from '@mui/material/useMediaQuery';
import { useTheme as useMuiTheme } from '@mui/material/styles';
import Home from './pages/Home';
import Dashboard from './pages/Dashboard';
import Calendar from './pages/Calendar';
import Reports from './pages/ReportsOverview';
import GamesContainer from './pages/GamesContainer';
import GameDetails from './pages/GameDetails';
import TournamentDetails from './pages/TournamentDetails';
import TestPage from './pages/TestPage';
import SizeGuide from './pages/SizeGuide';
import News from './pages/News';
import NewsDetail from './pages/NewsDetail';
import UserRelations from './pages/UserRelations';
import SurveyList from './pages/SurveyList';
import SurveyFill from './pages/SurveyFill';
import ProtectedRoute from './pages/ProtectedRoute';
import AuthModal from './modals/AuthModal';
import ProfileModal from './modals/ProfileModal';
import Navigation from './components/Navigation';
import FooterWithContact from './components/FooterWithContact';
import Formations from './pages/Formations';
import FeedbackAdmin from './pages/Feedback';
import AdminTitleXpOverview from './pages/admin/AdminTitleXpOverview';
import FabStackRoot from './components/FabStackRoot';
import Locations from './pages/Locations';
import Clubs from './pages/Clubs';
import Players from './pages/Players';
import Coaches from './pages/Coaches';
import AgeGroups from './pages/AgeGroups';
import Positions from './pages/Positions';
import StrongFeets from './pages/StrongFeets';
import SurfaceTypes from './pages/SurfaceTypes';
import GameEventTypes from './pages/GameEventTypes';
import Tasks from './pages/Tasks';
import Nationalities from './pages/Nationalities';
import CoachLicenses from './pages/CoachLicenses';
import Leagues from './pages/Leagues';
import Cameras from './pages/Cameras';
import VideoTypes from './pages/VideoTypes';
import Imprint from './pages/Imprint';
import Privacy from './pages/Privacy';
import Teams from './pages/Teams';
import Footer from './components/Footer';
import VerifyEmail from './pages/VerifyEmail';
import ForgotPassword from './pages/ForgotPassword';
import ResetPassword from './pages/ResetPassword';
import { PullToRefresh } from './components/PullToRefresh';
import { PushWarningBanner } from './components/PushWarningBanner';


function App() {
  const { user, isLoading } = useAuth();
  const { mode } = useTheme();
  const currentTheme = mode === 'dark' ? darkTheme : lightTheme;
  const muiTheme = useMuiTheme();
  const isMobile = useMediaQuery(muiTheme.breakpoints.down('md'));
  const [showAuth, setShowAuth] = useState(false);
  const [showProfile, setShowProfile] = useState(false);
  const location = useLocation();
  const { isOnHeroSection } = useHomeScroll();

  const isHome = location.pathname === '/' || location.pathname === '';
  const showLoginButton = !isHome || (isHome && isOnHeroSection);

  // Refresh-Funktion
  const handleRefresh = async () => {
    await new Promise(resolve => setTimeout(resolve, 500)); // Kurze Verzögerung für UX
    window.location.reload();
  };

  // Signal when app is ready (auth loaded)
  useEffect(() => {
    if (!isLoading) {
      window.dispatchEvent(new Event('app-ready'));
    }
  }, [isLoading]);

  // Keep rendering even during loading - preload screen will stay visible
  if (isLoading) {
    return null; // Return null while loading, preload screen stays visible
  }

  return (
    <MuiThemeProvider theme={currentTheme}>
      <CssBaseline />
      <NotificationProvider>
        <HomeScrollProvider>
          <FabStackRoot>
            <PullToRefresh
              onRefresh={handleRefresh}
              isEnabled={isMobile}
              isPullToRefreshEnabled={true}
            >
              <Box sx={{ display: 'flex', flexDirection: 'column', minHeight: '100vh' }}>
                <Navigation
                  onOpenAuth={() => setShowAuth(true)}
                  onOpenProfile={() => setShowProfile(true)}
                />
                <PushWarningBanner />
              <Box component="main" sx={{ flex: 1, width: '100%', position: 'relative' }}>
                <Routes>
                  <Route path="/" element={<Home />} />
                  <Route path="/verify-email/:token" element={<VerifyEmail />} />
                  <Route path="/forgot-password" element={<ForgotPassword />} />
                  <Route path="/reset-password/:token" element={<ResetPassword />} />
                  <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
                  <Route path="/surveys" element={<ProtectedRoute><SurveyList /></ProtectedRoute>} />
                  <Route path="/team-size-guide" element={<ProtectedRoute><SizeGuide /></ProtectedRoute>} />
                  <Route path="/games" element={<ProtectedRoute><GamesContainer /></ProtectedRoute>} />
                  <Route path="/games/:id" element={<ProtectedRoute><GameDetails /></ProtectedRoute>} />
                  <Route path="/tournaments/:id" element={<ProtectedRoute><TournamentDetails /></ProtectedRoute>} />
                  <Route path="/calendar" element={<ProtectedRoute><Calendar setCalendarFabHandler={() => {}} /></ProtectedRoute>} />
                  <Route path="/reports" element={<ProtectedRoute><Reports /></ProtectedRoute>} />
                  <Route path="/test" element={<ProtectedRoute><TestPage /></ProtectedRoute>} />
                  <Route path="/admin/feedback" element={<ProtectedRoute><FeedbackAdmin /></ProtectedRoute>} />
                  <Route path="/admin/user-relations" element={<ProtectedRoute><UserRelations /></ProtectedRoute>} />
                  <Route path="/admin/title-xp-overview" element={<ProtectedRoute><AdminTitleXpOverview /></ProtectedRoute>} />
                  <Route path="/news" element={<ProtectedRoute><News /></ProtectedRoute>} />
                  <Route path="/news/:id" element={<ProtectedRoute><NewsDetail /></ProtectedRoute>} />
                  <Route path="locations" element={<ProtectedRoute><Locations /></ProtectedRoute>} />
                  <Route path="formations" element={<ProtectedRoute><Formations /></ProtectedRoute>} />
                  <Route path="clubs" element={<ProtectedRoute><Clubs /></ProtectedRoute>} />
                  <Route path="coaches" element={<ProtectedRoute><Coaches /></ProtectedRoute>} />
                  <Route path="players" element={<ProtectedRoute><Players /></ProtectedRoute>} />
                  <Route path="ageGroups" element={<ProtectedRoute><AgeGroups /></ProtectedRoute>} />
                  <Route path="positions" element={<ProtectedRoute><Positions /></ProtectedRoute>} />
                  <Route path="strongFeets" element={<ProtectedRoute><StrongFeets /></ProtectedRoute>} />
                  <Route path="surfaceTypes" element={<ProtectedRoute><SurfaceTypes /></ProtectedRoute>} />
                  <Route path="gameEventTypes" element={<ProtectedRoute><GameEventTypes /></ProtectedRoute>} />
                  <Route path="tasks" element={<ProtectedRoute><Tasks /></ProtectedRoute>} />
                  <Route path="nationalities" element={<ProtectedRoute><Nationalities /></ProtectedRoute>} />
                  <Route path="leagues" element={<ProtectedRoute><Leagues /></ProtectedRoute>} />
                  <Route path="teams" element={<ProtectedRoute><Teams /></ProtectedRoute>} />
                  <Route path="coachLicenses" element={<ProtectedRoute><CoachLicenses /></ProtectedRoute>} />
                  <Route path="cameras" element={<ProtectedRoute><Cameras /></ProtectedRoute>} />
                  <Route path="videoTypes" element={<ProtectedRoute><VideoTypes /></ProtectedRoute>} />
                  <Route path="/survey/fill/:surveyId" element={<ProtectedRoute><SurveyFill /></ProtectedRoute>} />
                  <Route path="/imprint" element={<Imprint />} />
                  <Route path="/privacy" element={<Privacy />} />
                  <Route path="*" element={<Navigate to="/" />} />
                </Routes>
              </Box>
              <AuthModal open={showAuth} onClose={() => setShowAuth(false)} />
              <ProfileModal open={showProfile} onClose={() => setShowProfile(false)} />
              { user ? (<FooterWithContact />) : (<Footer />) }
            </Box>
            </PullToRefresh>
          </FabStackRoot>
        </HomeScrollProvider>
      </NotificationProvider>
    </MuiThemeProvider>
  );
}

export default App;