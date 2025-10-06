import { useNavigate, useLocation } from 'react-router-dom';
import AppBar from '@mui/material/AppBar';
import Toolbar from '@mui/material/Toolbar';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import Box from '@mui/material/Box';
import IconButton from '@mui/material/IconButton';
import Menu from '@mui/material/Menu';
import MenuItem from '@mui/material/MenuItem';
import Avatar from '@mui/material/Avatar';
import Drawer from '@mui/material/Drawer';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemButton from '@mui/material/ListItemButton';
import ListItemText from '@mui/material/ListItemText';
import Divider from '@mui/material/Divider';
import Collapse from '@mui/material/Collapse';
import ExpandLess from '@mui/icons-material/ExpandLess';
import ExpandMore from '@mui/icons-material/ExpandMore';
import MenuIcon from '@mui/icons-material/Menu';
import ArrowDropDownIcon from '@mui/icons-material/ArrowDropDown';
import GroupsIcon from '@mui/icons-material/Groups';
import CenterFocusStrongIcon from '@mui/icons-material/CenterFocusStrong';
import DirectionsRunIcon from '@mui/icons-material/DirectionsRun';
import LayersIcon from '@mui/icons-material/Layers';
import LocalOfferIcon from '@mui/icons-material/LocalOffer';
import ShieldIcon from '@mui/icons-material/Shield';
import PersonBadgeIcon from '@mui/icons-material/Badge';
import PersonIcon from '@mui/icons-material/Person';
import AccountCircleIcon from '@mui/icons-material/AccountCircle';
import LogoutIcon from '@mui/icons-material/Logout';
import RoomIcon from '@mui/icons-material/Room';
import FeedbackIcon from '@mui/icons-material/Feedback';
import NewspaperIcon from '@mui/icons-material/Newspaper';
import SearchIcon from '@mui/icons-material/Search';
import GroupWorkIcon from '@mui/icons-material/GroupWork';
import EmojiEventsIcon from '@mui/icons-material/EmojiEvents';
import PersonAddIcon from '@mui/icons-material/PersonAdd';
import HandshakeIcon from '@mui/icons-material/Handshake';
import SwapHorizIcon from '@mui/icons-material/SwapHoriz';
import BusinessIcon from '@mui/icons-material/Business';
import CheckroomIcon from '@mui/icons-material/Checkroom';
import VideoLibraryIcon from '@mui/icons-material/VideoLibrary';
import ManageAccountsIcon from '@mui/icons-material/ManageAccounts';
import PublicIcon from '@mui/icons-material/Public';
import SchoolIcon from '@mui/icons-material/School';
import React, { useState } from 'react';
import useMediaQuery from '@mui/material/useMediaQuery';
import { useTheme } from '@mui/material/styles';
import { useAuth } from '../context/AuthContext';
import { useHomeScroll } from '../context/HomeScrollContext';
import { NotificationCenter } from './NotificationCenter';
import NavigationMessagesButton from './NavigationMessagesButton';
import { BACKEND_URL } from '../../config';

interface NavigationProps {
  onOpenAuth: () => void;
  onOpenProfile: () => void;
}

export default function Navigation({ onOpenAuth, onOpenProfile }: NavigationProps) {
  const { user, isAuthenticated, logout } = useAuth();
  const { isOnHeroSection } = useHomeScroll();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const [anchorEl, setAnchorEl] = useState<null | HTMLElement>(null);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [trainerDrawerOpen, setTrainerDrawerOpen] = useState(false);
  const navigate = useNavigate();
  const location = useLocation();
  const isHome = location.pathname === '/' || location.pathname === '';

  // Show button: either not on home page, OR on home page but on hero section
  const showLoginButton = !isHome || (isHome && isOnHeroSection);

  // Navigation items configuration
  const navigationItems = [
    { key: 'home', label: 'Home', disabled: false },
    { key: 'dashboard', label: 'Dashboard', disabled: false },
    { key: 'surveys', label: 'Umfragen', disabled: false },
    { key: 'teams', label: 'Teams', disabled: false },
    { key: 'games', label: 'Spiele', disabled: false },
    { key: 'reports', label: 'Reports', disabled: false },
    { key: 'calendar', label: 'Kalender', disabled: false },
  ];

  const trainerMenuItems = [
    { key: 'team-size-guide', label: 'Team Size Guide', icon: <CheckroomIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
    { key: 'formations', label: 'Aufstellungen', page: 'formations', icon: <GroupWorkIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
  ];

  const adminMenuSections = [
    {
      section: 'Stammdaten',
      items: [
        { label: 'Altersgruppen', page: 'ageGroups', icon: <GroupsIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Ligen', page: 'leagues', icon: <EmojiEventsIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Positionen', page: 'positions', icon: <CenterFocusStrongIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Füße', page: 'strongFeets', icon: <DirectionsRunIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Beläge', page: 'surfaceTypes', icon: <LayersIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Ereignistypen', page: 'gameEventTypes', icon: <LocalOfferIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Nationalitäten', page: 'nationalities', icon: <PublicIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Trainer-Lizensen', page: 'coachLicenses', icon: <SchoolIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
      ],
    },
    {
      section: 'Verwaltung',
      items: [
        { label: 'Vereine', page: 'clubs', icon: <ShieldIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Trainer', page: 'coaches', icon: <PersonBadgeIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Spieler', page: 'players', icon: <PersonIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Spielstätten', page: 'locations', icon: <RoomIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Teams', page: 'teams', icon: <GroupsIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Feedback', page: 'admin/feedback', icon: <FeedbackIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Neuigkeiten Management', page: 'news', icon: <NewspaperIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Datenkonsistenz', href: 'admin/consistency', icon: <SearchIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Aufstellungen', page: 'formations', icon: <GroupWorkIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
        { label: 'Aufgaben', page: 'tasks', icon: <ManageAccountsIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
      ],
    },
    {
      section: 'Zuweisungen',
      items: [
        { label: 'Benutzer', page: 'admin/user-relations', icon: <ManageAccountsIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
{/*        { label: 'Spieler zu Team', href: '/api/player_team_assignments', icon: <PersonAddIcon fontSize="small" sx={{ color: 'text.secondary', mr: 1 }} /> },
        { label: 'Spieler zu Verein', href: '/api/player_club_assignments', icon: <HandshakeIcon fontSize="small" sx={{ color: 'text.secondary', mr: 1 }} /> },
        { label: 'Coach zu Team', href: '/api/coach_team_assignments', icon: <SwapHorizIcon fontSize="small" sx={{ color: 'text.secondary', mr: 1 }} /> },
        { label: 'Coach zu Verein', href: '/api/coach_club_assignments', icon: <BusinessIcon fontSize="small" sx={{ color: 'text.secondary', mr: 1 }} /> }*/},
        { label: 'Videos', href: '/videos/upload', icon: <VideoLibraryIcon fontSize="small" sx={{ color: 'text.primary', mr: 1 }} /> },
      ],
    },
  ];

  const [adminMenuAnchor, setAdminMenuAnchor] = useState<null | HTMLElement>(null);
  const handleAdminMenuOpen = (event: React.MouseEvent<HTMLElement>) => {
    setAdminMenuAnchor(event.currentTarget);
  };
  const handleAdminMenuClose = () => {
    setAdminMenuAnchor(null);
  };

  const [trainerMenuAnchor, setTrainerMenuAnchor] = useState<null | HTMLElement>(null);
  const handleTrainerMenuOpen = (event: React.MouseEvent<HTMLElement>) => {
    setTrainerMenuAnchor(event.currentTarget);
  };
  const handleTrainerMenuClose = () => {
    setTrainerMenuAnchor(null);
  };
  const handleTrainerMenuClick = (key: string) => {
    navigate(`/${key}`);
    handleTrainerMenuClose();
    handleMobileMenuClose();
  };

  const handleMenu = (event: React.MouseEvent<HTMLElement>) => {
    setAnchorEl(event.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleLogout = async () => {
    await logout();
    handleClose();
  };

  const handleMobileMenuToggle = () => {
    setMobileMenuOpen(!mobileMenuOpen);
  };

  const handleMobileMenuClose = () => {
    setMobileMenuOpen(false);
  };

  const handlePageChangeAndClose = (page: string) => {
    navigate(`/${page}`);
    handleMobileMenuClose();
  };

  const rolesArray = Object.values(user?.roles || {});
  const isAdmin = rolesArray.includes('ROLE_ADMIN') || rolesArray.includes('ROLE_SUPERADMIN');

  return (
    <>
      {/* Platzhalter für festen Header, damit der Seiteninhalt nicht überlappt */}
      <Box sx={{ height: { xs: 56, md: 64 } }} />
      
      <AppBar
        position="fixed"
        sx={{
          background: isHome
            ? 'transparent'
            : `linear-gradient(135deg, ${theme.palette.primary.main} 0%, ${theme.palette.secondary.main} 100%)`,
          backgroundColor: 'transparent',
          boxShadow: 'none',
          color: isHome
            ? '#fff'
            : 'primary.contrastText',
          transition: 'background 0.3s',
        }}
      >
        <Toolbar sx={{ 
          color: isHome
            ? '#fff'
            : 'primary.contrastText',
          }}>
          <Typography
            variant="h6"
            component="div"
            sx={{ flexGrow: 1, cursor: 'pointer', userSelect: 'none' }}
            onClick={() => navigate('/')}
            title="Zur Startseite"
            style={{ fontFamily: 'ImpactWeb, Impact, \"Arial Black\", sans-serif', fontSize: '2rem' }}
          >
            {location.pathname !== '/' && (
              <>
                <span style={{ color: '#018606', textShadow: '0 1px 6px #fff, 0 0px 2px #fff' }}>K</span>ADERBLICK
              </>
            )}
          </Typography>

          {isAuthenticated ? (
            <>
              {/* Desktop Navigation */}
              {!isMobile && (
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                  {navigationItems.map((item) => (
                    item.key === 'trainer' ? null : (
                      <Button
                        key={item.key}
                        disabled={item.disabled}
                        onClick={() => !item.disabled && navigate(`/${item.key}`)}
                        className="navigation-transparent-btn"
                        sx={{
                          color: isHome
                            ? '#fff'
                            : theme.palette.primary.contrastText,
                          fontWeight: 500,
                          borderRadius: 2,
                          minWidth: 'auto',
                          px: 2,
                          py: 1,
                        }}
                      >
                        {item.label}
                      </Button>
                    )
                  ))}
                  {/* Trainer Dropdown */}
                  {user?.isCoach && (
                    <>
                      <Button
                        onClick={handleTrainerMenuOpen}
                        className="navigation-transparent-btn"
                        sx={{
                          color: isHome
                            ? '#fff'
                            : theme.palette.primary.contrastText,
                          fontWeight: 500,
                          borderRadius: 2,
                          minWidth: 'auto',
                          px: 2,
                          py: 1,
                        }}
                        endIcon={<ArrowDropDownIcon />}
                      >
                        Trainer
                      </Button>
                      <Menu
                        anchorEl={trainerMenuAnchor}
                        open={Boolean(trainerMenuAnchor)}
                        onClose={handleTrainerMenuClose}
                      >
                        {trainerMenuItems.map((item) => (
                          <MenuItem
                            key={item.key}
                            selected={location.pathname === `/${item.key}`}
                            onClick={() => handleTrainerMenuClick(item.key)}
                          >
                            {item.icon}
                            {item.label}
                          </MenuItem>
                        ))}
                      </Menu>
                    </>
                  )}
                  {/* Admin Dropdown */}
                  {isAdmin && (
                    <>
                      <Button
                        onClick={handleAdminMenuOpen}
                        className="navigation-transparent-btn"
                        sx={{
                          color: isHome
                            ? '#fff'
                            : theme.palette.primary.contrastText,
                          fontWeight: 500,
                          borderRadius: 2,
                          minWidth: 'auto',
                          px: 2,
                          py: 1,
                        }}
                        endIcon={<ArrowDropDownIcon />}
                      >
                        Administration
                      </Button>
                      <Menu
                        anchorEl={adminMenuAnchor}
                        open={Boolean(adminMenuAnchor)}
                        onClose={handleAdminMenuClose}
                        MenuListProps={{ sx: { minWidth: 250 } }}
                      >
                        {adminMenuSections.map((section) => (
                          <Box key={section.section}>
                            <MenuItem disabled>
                              <Typography variant="subtitle2"
                                sx={{
                                  color: isHome
                                    ? '#fff'
                                    : 'text.primary',
                                }}
                              >
                                {section.section}
                              </Typography>
                            </MenuItem>
                            {section.items.map((item) => (
                              <MenuItem
                                key={item.label}
                                sx={{
                                  pl: 3,
                                  display: 'flex',
                                  alignItems: 'center',
                                }}
                                onClick={() => {
                                  handleAdminMenuClose();
                                  if (item.page) {
                                    navigate(`/${item.page}`);
                                  }
                                }}
                              >
                                {item.icon}
                                {item.label}
                              </MenuItem>
                            ))}
                            <Divider />
                          </Box>
                        ))}
                      </Menu>
                    </>
                  )}
                </Box>
              )}

              {/* Mobile Navigation Button */}
              {isMobile && (
                <IconButton
                  onClick={handleMobileMenuToggle}
                  sx={{ 
                    mr: 1,
                    color: isHome
                      ? '#fff'
                      : theme.palette.primary.contrastText,
                  }}
                >
                  <MenuIcon />
                </IconButton>
              )}

              {/* Common Controls */}
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                <NotificationCenter />
                
                <IconButton
                  size="large"
                  aria-label="account of current user"
                  aria-controls="menu-appbar"
                  aria-haspopup="true"
                  onClick={handleMenu}
                  sx={{ 
                    color: isHome
                      ? '#fff'
                      : theme.palette.primary.contrastText,
                  }}
                >
                  <Avatar sx={{ width: 32, height: 32 }}>
                    {user?.avatarFile ? (
                      <img
                        src={`${BACKEND_URL}/uploads/avatar/${user.avatarFile}`}
                        alt={user?.firstName || user?.email || 'Avatar'}
                        style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                      />
                    ) : (
                      user?.firstName?.charAt(0) || user?.email?.charAt(0) || 'U'
                    )}
                  </Avatar>
                </IconButton>
              </Box>
            </>
          ) : (
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              {!user && showLoginButton && (
                <Button
                  variant="contained"
                  onClick={onOpenAuth}
                  sx={{
                    fontWeight: 500,
                    borderRadius: 2,
                    minWidth: 'auto',
                    px: 2,
                    py: 1,
                    color: isHome
                      ? '#fff'
                      : theme.palette.primary.contrastText,
                    '&:hover': {
                      backgroundColor: 'primary.dark',
                      boxShadow: 3,
                    },
                    transition: 'all 0.3s ease',
                  }}
                >
                  Login / Register
                </Button>
              )}
            </Box>
          )}
        </Toolbar>
      </AppBar>

      {/* Mobile Navigation Drawer */}
      <Drawer
        anchor="left"
        open={mobileMenuOpen}
        onClose={handleMobileMenuClose}
        sx={{
          '& .MuiDrawer-paper': {
            width: 280,
            boxSizing: 'border-box',
          },
        }}
      >
        <Box sx={{ pt: 2, pb: 1 }}>
          <Typography variant="h6" sx={{ px: 2, mb: 1 }}>
            Navigation
          </Typography>
          <Divider />
          <List>
            {navigationItems.map((item) => (
              item.key === 'trainer' ? null : (
                <ListItem key={item.key} disablePadding>
                  <ListItemButton
                    selected={location.pathname === `/${item.key}`}
                    disabled={item.disabled}
                    onClick={() => !item.disabled && handlePageChangeAndClose(item.key as any)}
                  >
                    <ListItemText primary={item.label} />
                  </ListItemButton>
                </ListItem>
              )
            ))}
            {/* Trainer Untermenü im Drawer (Accordion) */}
            {user?.isCoach && (
              <>
                <ListItem disablePadding>
                  <ListItemButton
                    selected={location.pathname?.startsWith('/trainer-')}
                    onClick={() => setTrainerDrawerOpen((prev: boolean) => !prev)}
                  >
                    <ListItemText primary="Trainer" />
                    {trainerDrawerOpen ? <ExpandLess /> : <ExpandMore />}
                  </ListItemButton>
                </ListItem>
                <Collapse in={trainerDrawerOpen} timeout="auto" unmountOnExit>
                  {trainerMenuItems.map((item) => (
                    <ListItem key={item.key} disablePadding sx={{ pl: 3 }}>
                      <ListItemButton
                        selected={location.pathname === `/${item.key}`}
                        onClick={() => handleTrainerMenuClick(item.key)}
                      >
                        <ListItemText primary={item.label} />
                      </ListItemButton>
                    </ListItem>
                  ))}
                </Collapse>
              </>
            )}
            {/* Admin Untermenü (Accordion) entfernt, da Admin-Dropdown jetzt über adminMenuSections läuft */}
          </List>
        </Box>
      </Drawer>

      {/* User Menu */}
      <Menu
        id="menu-appbar"
        anchorEl={anchorEl}
        anchorOrigin={{
          vertical: 'top',
          horizontal: 'right',
        }}
        keepMounted
        transformOrigin={{
          vertical: 'top',
          horizontal: 'right',
        }}
        open={Boolean(anchorEl)}
        onClose={handleClose}
      >
        <MenuItem disabled>
          <Box>
            <Typography variant="subtitle2">{user?.name}</Typography>
            <Typography variant="caption" 
              sx={{
                color: isHome
                  ? '#fff'
                  : 'text.primary',
                }}
            >
              {user?.email}
            </Typography>
          </Box>
        </MenuItem>
        <MenuItem onClick={() => { handleClose(); onOpenProfile(); }}>
          <AccountCircleIcon fontSize="small" 
            sx={{
              color: isHome
                ? '#fff'
                : 'text.primary',
              mr: 1
            }} />
          Profil
        </MenuItem>
        <NavigationMessagesButton 
          variant="icon-with-text" 
          text="Nachrichten" 
        />
        <MenuItem onClick={handleLogout}>
          <LogoutIcon fontSize="small" sx={{
            color: isHome
              ? '#fff'
              : 'text.primary',
            mr: 1 }}
          />
          Logout
        </MenuItem>
      </Menu>
    </>
  );
}
