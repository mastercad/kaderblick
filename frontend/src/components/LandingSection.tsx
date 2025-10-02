import React, { useState } from 'react';
import { Box, Typography, IconButton, Modal } from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import ArrowBackIosNewIcon from '@mui/icons-material/ArrowBackIosNew';
import ArrowForwardIosIcon from '@mui/icons-material/ArrowForwardIos';
import '../styles/landing-section.css';

interface LandingSectionProps {
  name: string;
  image: string;
  additionalImages?: string[];
  text: string;
  reverse?: boolean;
}

export default function LandingSection({ name, image, additionalImages = [], text, reverse = false }: LandingSectionProps) {
  const [modalImage, setModalImage] = useState<string | null>(null);
  const [thumbStart, setThumbStart] = useState(0);
  const thumbsPerPage = 3;

  const handleThumbPrev = () => setThumbStart((s) => Math.max(0, s - thumbsPerPage));
  const handleThumbNext = () => setThumbStart((s) => Math.min(additionalImages.length - thumbsPerPage, s + thumbsPerPage));

  const imageSection = (
    <Box className="landing-section-image-wrapper">
      <Box className="landing-section-main-image">
        <img
          src={image}
          alt={name}
          onClick={() => setModalImage(image)}
        />
      </Box>

      {additionalImages.length > 0 && (
        <Box className="landing-section-thumbnails">
          <IconButton
            onClick={handleThumbPrev}
            disabled={thumbStart === 0}
            size="small"
            sx={{
              bgcolor: 'transparent',
              color: '#fff',
              transition: 'all 0.2s',
              '&:hover': {
                bgcolor: 'rgba(255, 255, 255, 0.1)',
              },
              '&:disabled': {
                color: 'rgba(255, 255, 255, 0.3)',
              },
            }}
          >
            <ArrowBackIosNewIcon fontSize="small" />
          </IconButton>
          {additionalImages.slice(thumbStart, thumbStart + thumbsPerPage).map((img: string, idx: number) => (
            <Box key={idx} className="landing-section-thumbnail">
              <img
                src={img}
                alt={`${name} Zusatzbild ${idx + 1}`}
                onClick={() => setModalImage(img)}
              />
            </Box>
          ))}
          <IconButton
            onClick={handleThumbNext}
            disabled={thumbStart + thumbsPerPage >= additionalImages.length}
            size="small"
            sx={{
              bgcolor: 'transparent',
              color: '#fff',
              transition: 'all 0.2s',
              '&:hover': {
                bgcolor: 'rgba(255, 255, 255, 0.1)',
              },
              '&:disabled': {
                color: 'rgba(255, 255, 255, 0.3)',
              },
            }}
          >
            <ArrowForwardIosIcon fontSize="small" />
          </IconButton>
        </Box>
      )}
    </Box>
  );

  const textSection = (
    <Box className="landing-section-text-wrapper">
      <Box className="landing-section-title">
        <Box component="span" className="landing-section-title-highlight">
          {name.charAt(0).toUpperCase()}
        </Box>
        {name.slice(1).toUpperCase()}
      </Box>
      <Typography
        variant="body1"
        className="landing-section-text"
      >
        {text}
      </Typography>
    </Box>
  );

  return (
    <>
      <Box className="landing-section">
        <Box className="landing-section-content">
          {reverse ? (
            <>
              {textSection}
              {imageSection}
            </>
          ) : (
            <>
              {imageSection}
              {textSection}
            </>
          )}
        </Box>
        
        {/* Branding Section at bottom left */}
        <Box className="landing-section-branding">
          <Box className="landing-section-branding-content">
            <Box component="span" className="landing-section-branding-title">
              <Box component="span" className="landing-section-branding-title-highlight">
                K
              </Box>
              ADERBLICK
            </Box>
            <Box component="span" className="landing-section-branding-subtitle">
              DEINEN VEREIN IM BLICK
            </Box>
          </Box>
        </Box>
      </Box>

      {/* Modal for full-screen image view */}
      <Modal open={!!modalImage} onClose={() => setModalImage(null)}>
        <Box
          display="flex"
          alignItems="center"
          justifyContent="center"
          height="100vh"
          position="relative"
          onClick={() => setModalImage(null)}
          sx={{ cursor: 'pointer', background: 'rgba(0,0,0,0.15)' }}
        >
          <IconButton
            onClick={(e) => {
              e.stopPropagation();
              setModalImage(null);
            }}
            sx={{
              position: 'absolute',
              top: 24,
              right: 24,
              zIndex: 2,
              bgcolor: 'rgba(255,255,255,0.7)',
              boxShadow: 2,
              transition: 'background 0.2s',
              '&:hover': {
                bgcolor: '#222',
                '& svg': { color: '#fff' },
              },
            }}
            size="large"
            aria-label="Schließen"
          >
            <CloseIcon fontSize="large" sx={{ color: '#222', transition: 'color 0.2s' }} />
          </IconButton>
          <img
            src={modalImage || ''}
            alt="Großansicht"
            style={{
              maxHeight: '90vh',
              maxWidth: '90vw',
              borderRadius: 12,
              boxShadow: '0 8px 32px rgba(0,0,0,0.2)',
              cursor: 'auto',
            }}
            onClick={(e) => e.stopPropagation()}
          />
        </Box>
      </Modal>
    </>
  );
}

