import { useTheme } from './context/ThemeContext';
import { useState } from 'react';
import { ThemeProvider as MuiThemeProvider } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import Box from '@mui/material/Box';
import Button from '@mui/material/Button';
import { lightTheme, darkTheme } from './theme/theme';
import { NotificationProvider } from './context/NotificationContext';
import { useAuth } from './context/AuthContext';
import { Routes, Route, Navigate } from 'react-router-dom';
import Home from './pages/Home';
import Dashboard from './pages/Dashboard';
import Calendar from './pages/Calendar';
import Reports from './pages/ReportsOverview';
import GamesContainer from './pages/GamesContainer';
import TestPage from './pages/TestPage';
import TeamOutfit from './pages/TeamOutfit';
import News from './pages/News';
import UserRelations from './pages/UserRelations';
import SurveyList from './pages/SurveyList';
import SurveyFill from './pages/SurveyFill';
import ProtectedRoute from './pages/ProtectedRoute';
import AuthModal from './components/AuthModal';
import ProfileModal from './modals/ProfileModal';
import Navigation from './components/Navigation';
import FooterWithContact from './components/FooterWithContact';
import Formations from './pages/Formations';
import FeedbackAdmin from './pages/Feedback';
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
import Imprint from './pages/Imprint';
import Privacy from './pages/Privacy';

function App() {
  const { user, isLoading } = useAuth();
  const { mode } = useTheme();
  const currentTheme = mode === 'dark' ? darkTheme : lightTheme;
  const [showAuth, setShowAuth] = useState(false);
  const [showProfile, setShowProfile] = useState(false);

  if (isLoading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', flexGrow: 1, minHeight: '100vh', background: 'linear-gradient(135deg, #43a047 0%, #a5d6a7 100%)' }}>
        <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
          <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <radialGradient id="glowGreen" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                <stop offset="0%" stopColor="#b9f6ca" stopOpacity="0.8" />
                <stop offset="100%" stopColor="#43a047" stopOpacity="0" />
              </radialGradient>
              <linearGradient id="ballGradientGreen" x1="0" y1="0" x2="120" y2="120" gradientUnits="userSpaceOnUse">
                <stop stopColor="#a5d6a7" />
                <stop offset="1" stopColor="#43a047" />
              </linearGradient>
            </defs>
            <circle cx="60" cy="60" r="48" fill="url(#ballGradientGreen)" stroke="#fff" strokeWidth="4" />
            <circle cx="60" cy="60" r="54" fill="url(#glowGreen)">
              <animate attributeName="r" values="54;60;54" dur="1.2s" repeatCount="indefinite" />
              <animate attributeName="opacity" values="0.7;1;0.7" dur="1.2s" repeatCount="indefinite" />
            </circle>
            <g>
              <polygon points="60,40 70,60 60,80 50,60" fill="#fff" stroke="#388e3c" strokeWidth="2">
                <animateTransform attributeName="transform" type="rotate" from="0 60 60" to="360 60 60" dur="1.8s" repeatCount="indefinite" />
              </polygon>
              <circle cx="60" cy="60" r="10" fill="#b9f6ca" stroke="#388e3c" strokeWidth="2">
                <animateTransform attributeName="transform" type="scale" from="1" to="1.2" begin="0s" dur="0.8s" repeatCount="indefinite" />
              </circle>
            </g>
          </svg>
          <Box sx={{ mt: 3, color: '#fff', fontWeight: 700, fontSize: 22, letterSpacing: 2, textShadow: '0 2px 8px #388e3c', textAlign: 'center' }}>
            Kaderblick wird geladen...
          </Box>
        </Box>
      </Box>
    );
  }

  return (
    <MuiThemeProvider theme={currentTheme}>
      <CssBaseline />
      <NotificationProvider>
        <FabStackRoot>
          <Box sx={{ display: 'flex', flexDirection: 'column', minHeight: '100vh' }}>
            {user && (
              <Navigation
                onOpenAuth={() => setShowAuth(true)}
                onOpenProfile={() => setShowProfile(true)}
              />
            )}
            <Box component="main" sx={{ flexGrow: 1, width: '100%', position: 'relative' }}>
              <Routes>
                <Route path="/" element={<Home />} />
                <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
                <Route path="/surveys" element={<ProtectedRoute><SurveyList /></ProtectedRoute>} />
                <Route path="/trainer-outfits" element={<ProtectedRoute><TeamOutfit /></ProtectedRoute>} />
                <Route path="/games" element={<ProtectedRoute><GamesContainer /></ProtectedRoute>} />
                <Route path="/calendar" element={<ProtectedRoute><Calendar setCalendarFabHandler={() => {}} /></ProtectedRoute>} />
                <Route path="/reports" element={<ProtectedRoute><Reports /></ProtectedRoute>} />
                <Route path="/test" element={<ProtectedRoute><TestPage /></ProtectedRoute>} />
                <Route path="/admin/feedback" element={<ProtectedRoute><FeedbackAdmin /></ProtectedRoute>} />
                <Route path="/admin/user-relations" element={<ProtectedRoute><UserRelations /></ProtectedRoute>} />
                <Route path="news" element={<ProtectedRoute><News /></ProtectedRoute>} />
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
                <Route path="coachLicenses" element={<ProtectedRoute><CoachLicenses /></ProtectedRoute>} />
                <Route path="/api/surveys/:surveyId" element={<ProtectedRoute><SurveyFill /></ProtectedRoute>} />
                <Route path="/imprint" element={<ProtectedRoute><Imprint /></ProtectedRoute>} />
                <Route path="/privacy" element={<ProtectedRoute><Privacy /></ProtectedRoute>} />
                <Route path="*" element={<Navigate to="/" />} />
              </Routes>
              {!user && (
                <Box sx={{ position: 'fixed', top: 24, right: 24, zIndex: 2000 }}>
                  <Button variant="contained" onClick={() => setShowAuth(true)}>
                    Login / Register
                  </Button>
                </Box>
              )}
            </Box>
            <AuthModal open={showAuth} onClose={() => setShowAuth(false)} />
            <ProfileModal open={showProfile} onClose={() => setShowProfile(false)} />
            <FooterWithContact />
          </Box>
        </FabStackRoot>
      </NotificationProvider>
    </MuiThemeProvider>
  );
}

export default App;
