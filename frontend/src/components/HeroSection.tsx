import React from 'react';
import { Box, Button, Typography } from '@mui/material';
import KeyboardArrowDownIcon from '@mui/icons-material/KeyboardArrowDown';
import { useAuth } from '../context/AuthContext';
import '../styles/hero-section.css';

interface HeroSectionProps {
  onStartClick?: () => void;
  heroRef?: React.RefObject<HTMLDivElement | null>;
  onScrollDown?: () => void;
}

export default function HeroSection({ onStartClick, heroRef, onScrollDown }: HeroSectionProps) {
  const { user } = useAuth();
  
  return (
    <Box
      ref={heroRef}
      className="hero-section"
      sx={{
        backgroundImage: 'url(/images/landing_page/background.jpg)',
      }}
    >
      <Box className="hero-outer">
        <Box className="hero-content">
          <Box component="span" className="hero-title">
            <Box component="span" className="hero-title-highlight">
                K</Box>
            ADERBLICK
          </Box>
          <Box component="span" className="hero-subtitle">
            DEINEN VEREIN IM BLICK
          </Box>
          {!user ? (
            <Box className="hero-btn-wrapper">
              <Button
                variant="contained"
                color="primary"
                sx={{ 
                  p: 3, 
                  fontSize: '2rem',
                  border: '2px solid #FFF',
                  boxShadow: 3,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    transform: 'translateY(-2px)',
                    boxShadow: 6,
                    border: '2px solid #FFF',
                  },
                  '&:focus': {
                    outline: 'none',
                  },
                  '@media (max-width: 600px)': {
                    p: 2,
                    fontSize: '1.25rem',
                  }
                }}
                onClick={onStartClick}
              >
                Jetzt starten
              </Button>
            </Box>
          ) : (
            <Box className="hero-btn-wrapper" sx={{ visibility: 'hidden' }}>
              <Box sx={{ p: 3, fontSize: '2rem' }}>Platzhalter</Box>
            </Box>
          )}
        </Box>
      </Box>
      
      {/* Scroll Indikator */}
      <Box 
        className="hero-scroll-indicator"
        onClick={onScrollDown}
        sx={{ cursor: onScrollDown ? 'pointer' : 'default' }}
      >
        <Typography variant="body2" className="hero-scroll-text">
          Mehr erfahren
        </Typography>
        <KeyboardArrowDownIcon className="hero-scroll-arrow" />
      </Box>
    </Box>
  );
}
