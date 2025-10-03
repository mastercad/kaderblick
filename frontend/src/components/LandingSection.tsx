import React, { useState, useMemo, useEffect, useRef } from 'react';
import { Box, Typography, IconButton, Modal, Button } from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import ArrowBackIosNewIcon from '@mui/icons-material/ArrowBackIosNew';
import ArrowForwardIosIcon from '@mui/icons-material/ArrowForwardIos';
import '../styles/landing-section.css';

const callToActionTexts = [
  'Jetzt dabei sein',
  'Jetzt umsehen',
  'Jetzt entdecken',
  'Jetzt loslegen',
  'Jetzt ausprobieren',
  'Jetzt mitmachen',
  'Kostenlos starten',
  'Jetzt anmelden',
  'Mehr erfahren',
  'Los geht\'s',
];

interface LandingSectionProps {
  name: string;
  image: string;
  additionalImages?: string[];
  text: string;
  reverse?: boolean;
  onAuthClick?: () => void;
}

export default function LandingSection({ name, image, additionalImages = [], text, reverse = false, onAuthClick }: LandingSectionProps) {
  const [modalImage, setModalImage] = useState<string | null>(null);
  const [thumbStart, setThumbStart] = useState(0);
  const [isVisible, setIsVisible] = useState(false);
  const sectionRef = useRef<HTMLDivElement>(null);
  const thumbsPerPage = 3;

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => {
        // Setze isVisible basierend darauf, ob die Section sichtbar ist
        setIsVisible(entry.isIntersecting);
      },
      { threshold: 0.1 }
    );

    if (sectionRef.current) {
      observer.observe(sectionRef.current);
    }

    return () => {
      if (sectionRef.current) {
        observer.unobserve(sectionRef.current);
      }
    };
  }, []);

  const ctaText = useMemo(() => {
    const hash = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    return callToActionTexts[hash % callToActionTexts.length];
  }, [name]);

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
      
      {onAuthClick && (
        <Button
          variant="contained"
          color="primary"
          onClick={(e) => {
            e.preventDefault();
            e.stopPropagation();
            onAuthClick();
          }}
          sx={{
            mt: 3,
            py: 1.5,
            px: 4,
            fontSize: '1.1rem',
            fontWeight: 600,
            textTransform: 'none',
            borderRadius: 2,
            boxShadow: 3,
            transition: 'all 0.3s',
            '&:hover': {
              transform: 'translateY(-2px)',
              boxShadow: 6,
            },
          }}
        >
          {ctaText}
        </Button>
      )}
    </Box>
  );

  return (
    <>
      <Box ref={sectionRef} className={`landing-section ${isVisible ? 'visible' : ''}`}>
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

