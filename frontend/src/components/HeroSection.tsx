import React from 'react';
import { Box, Button, Typography } from '@mui/material';
import { useAuth } from '../context/AuthContext';
import '../styles/hero-section.css';

interface HeroSectionProps {
  onStartClick?: () => void;
  heroRef?: React.RefObject<HTMLDivElement | null>;
}

export default function HeroSection({ onStartClick, heroRef }: HeroSectionProps) {
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
          {!user && (
            <Box className="hero-btn-wrapper">
              <Button
                variant="contained"
                color="primary"
                sx={{ 
                  p: 3, 
                  fontSize: '2rem',
                  border: '2px solid #FFF',
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
          )}
        </Box>
      </Box>
    </Box>
  );
}
