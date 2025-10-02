import React, { useState } from 'react';
import { Box, Typography, IconButton, Modal, Tooltip } from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import ArrowBackIosNewIcon from '@mui/icons-material/ArrowBackIosNew';
import ArrowForwardIosIcon from '@mui/icons-material/ArrowForwardIos';

interface GallerySection {
  name: string;
  image: string;
  additionalImages?: string[];
  text: string;
}

interface LandingGalleryProps {
  sections: GallerySection[];
}

export default function LandingGallery({ sections }: LandingGalleryProps) {
  const [current, setCurrent] = useState(0);
  const [modalImage, setModalImage] = useState<string | null>(null);
  const [transitioning, setTransitioning] = useState(false);
  const [showNext, setShowNext] = useState(false);
  const [nextIndex, setNextIndex] = useState<number | null>(null);
  const section = sections[current];
  const additional = section.additionalImages || [];
  const [thumbStart, setThumbStart] = useState(0);
  const thumbsPerPage = 3;

  const handleSwitch = (dir: number) => {
    if (transitioning) return;
    
    const nextIdx = dir === 1 
      ? (current < sections.length - 1 ? current + 1 : 0) 
      : (current > 0 ? current - 1 : sections.length - 1);
    
    setNextIndex(nextIdx);
    setShowNext(true);
    
    setTimeout(() => {
      setTransitioning(true);
    }, 50);
    
    setTimeout(() => {
      setCurrent(nextIdx);
      setShowNext(false);
      setNextIndex(null);
      setTransitioning(false);
    }, 1050);
  };

  const handlePrev = () => handleSwitch(-1);
  const handleNext = () => handleSwitch(1);
  const handleThumbPrev = () => setThumbStart((s) => Math.max(0, s - thumbsPerPage));
  const handleThumbNext = () => setThumbStart((s) => Math.min(additional.length - thumbsPerPage, s + thumbsPerPage));

  return (
    <>
      <Box pt={14} width="100%" display="block" sx={{ margin: '0 auto' }}>
        <Box
          width="90vw"
          display="flex"
          flexDirection={{ xs: 'column', md: 'row' }}
          gap={{ xs: 0, md: 10 }}
          sx={{ margin: '0 auto', justifyContent: 'center', alignItems: { xs: 'center', md: 'stretch' } }}
        >
          {/* Left: Main Image + Thumbnails */}
          <Box
            minWidth={0}
            display="flex"
            flexDirection="column"
            alignItems="center"
            sx={{ width: { xs: '95%', sm: '90%', md: '50%' }, minWidth: 0, maxWidth: { xs: '100%', md: '50%' }, mx: 'auto' }}
          >
            <>
              <Box position="relative" width="100%" mb={4} sx={{
                mt: { xs: 2, md: 0 },
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
              }}>
                <Box sx={{
                  position: 'relative',
                  width: '100%',
                  overflow: 'hidden',
                  border: 0,
                  borderRadius: '12px',
                  boxShadow: '0 4px 24px rgba(0,0,0,0.08)',
                  aspectRatio: '2 / 1',
                }}>
                  {/* Aktuelles Bild */}
                  {!showNext && (
                    <img
                      src={section.image}
                      alt="Vorschau"
                      style={{
                        width: '100%',
                        height: '100%',
                        objectFit: 'cover',
                        cursor: 'pointer',
                        position: 'absolute',
                        left: 0,
                        top: 0,
                      }}
                      onClick={() => setModalImage(section.image)}
                    />
                  )}
                  {/* Bild während Transition - scrollt nach oben raus */}
                  {showNext && (
                    <img
                      src={section.image}
                      alt="Alt"
                      style={{
                        width: '100%',
                        height: '100%',
                        objectFit: 'cover',
                        cursor: 'pointer',
                        position: 'absolute',
                        left: 0,
                        top: 0,
                        transition: 'transform 1s cubic-bezier(.77,0,.18,1)',
                        transform: transitioning ? 'translateY(-100%)' : 'translateY(0%)',
                        zIndex: 1,
                      }}
                    />
                  )}
                  {/* Nächstes Bild - scrollt von unten rein */}
                  {showNext && nextIndex !== null && (
                    <img
                      src={sections[nextIndex].image}
                      alt="Nächstes Bild"
                      style={{
                        width: '100%',
                        height: '100%',
                        objectFit: 'cover',
                        cursor: 'pointer',
                        position: 'absolute',
                        left: 0,
                        top: 0,
                        transition: 'transform 1s cubic-bezier(.77,0,.18,1)',
                        transform: transitioning ? 'translateY(0%)' : 'translateY(100%)',
                        zIndex: 2,
                      }}
                    />
                  )}
                </Box>
                <IconButton
                  onClick={handlePrev}
                  sx={{
                    position: 'absolute',
                    top: '50%',
                    left: -20,
                    transform: 'translateY(-50%)',
                    bgcolor: 'rgba(255,255,255,0.15)',
                    boxShadow: 1,
                    transition: 'all 0.3s',
                    zIndex: 10,
                    '& svg': {
                      color: 'rgba(0,0,0,0.4)',
                      transition: 'color 0.3s',
                    },
                    '&:hover': {
                      bgcolor: 'rgba(255,255,255,0.95)',
                      boxShadow: 2,
                      '& svg': {
                        color: 'rgba(0,0,0,0.87)',
                      },
                    },
                  }}
                  size="small"
                  disabled={sections.length <= 1}
                >
                  <ArrowBackIosNewIcon fontSize="small" />
                </IconButton>
                <IconButton
                  onClick={handleNext}
                  sx={{
                    position: 'absolute',
                    top: '50%',
                    right: -20,
                    transform: 'translateY(-50%)',
                    bgcolor: 'rgba(255,255,255,0.15)',
                    boxShadow: 1,
                    transition: 'all 0.3s',
                    zIndex: 10,
                    '& svg': {
                      color: 'rgba(0,0,0,0.4)',
                      transition: 'color 0.3s',
                    },
                    '&:hover': {
                      bgcolor: 'rgba(255,255,255,0.95)',
                      boxShadow: 2,
                      '& svg': {
                        color: 'rgba(0,0,0,0.87)',
                      },
                    },
                  }}
                  size="small"
                  disabled={sections.length <= 1}
                >
                  <ArrowForwardIosIcon fontSize="small" />
                </IconButton>
              </Box>
              
              {/* Punkt-Navigation */}
              {sections.length > 1 && (
                <Box 
                  display="flex" 
                  justifyContent="center" 
                  gap={1.5} 
                  sx={{ mt: 2, mb: 2 }}
                >
                  {sections.map((section, idx) => (
                    <Tooltip key={idx} title={section.name} arrow placement="top">
                      <Box
                        onClick={() => {
                          if (idx !== current && !transitioning) {
                            const dir = idx > current ? 1 : -1;
                            setNextIndex(idx);
                            setShowNext(true);
                            setTimeout(() => setTransitioning(true), 50);
                            setTimeout(() => {
                              setCurrent(idx);
                              setShowNext(false);
                              setNextIndex(null);
                              setTransitioning(false);
                            }, 1050);
                          }
                        }}
                        sx={{
                          width: 12,
                        height: 12,
                        borderRadius: '50%',
                        bgcolor: idx === current ? 'primary.main' : 'rgba(0,0,0,0.2)',
                        cursor: 'pointer',
                        transition: 'all 0.3s',
                        '&:hover': {
                          bgcolor: idx === current ? 'primary.main' : 'rgba(0,0,0,0.4)',
                          transform: 'scale(1.2)',
                        },
                      }}
                    />
                    </Tooltip>
                  ))}
                </Box>
              )}
              
              {additional.length > 0 && (
                <Box display="flex" alignItems="center" gap={1} sx={{ mt: { xs: 2, md: 0 } }}>
                  <IconButton
                    onClick={handleThumbPrev}
                    disabled={thumbStart === 0}
                    size="small"
                    sx={{
                      bgcolor: 'transparent',
                      transition: 'background 0.2s',
                      '&:hover': {
                        bgcolor: 'background.paper',
                      },
                    }}
                  >
                    <ArrowBackIosNewIcon fontSize="small" />
                  </IconButton>
                  {additional.slice(thumbStart, thumbStart + thumbsPerPage).map((img: string) => (
                    <Box key={img} mx={0.5}>
                      <img
                        src={img}
                        alt="Zusatzbild"
                        style={{ width: '100%', height: 75, objectFit: 'cover', borderRadius: 6, cursor: 'pointer', border: '2px solid #eee' }}
                        onClick={() => setModalImage(img)}
                      />
                    </Box>
                  ))}
                  <IconButton
                    onClick={handleThumbNext}
                    disabled={thumbStart + thumbsPerPage >= additional.length}
                    size="small"
                    sx={{
                      bgcolor: 'transparent',
                      transition: 'background 0.2s',
                      '&:hover': {
                        bgcolor: 'background.paper',
                      },
                    }}
                  >
                    <ArrowForwardIosIcon fontSize="small" />
                  </IconButton>
                </Box>
              )}
            </>
          </Box>
          {/* Right: Text */}
          <Box
            display="flex"
            justifyContent="center"
            sx={ {
              width: { xs: '95%', sm: '90%', md: '50%' },
              minWidth: 0,
              maxWidth: { xs: '100%', md: '50%' },
              mx: 'auto',
              mt: { xs: 4, md: 0 },
              mb: { xs: 4, md: 0 },
              overflow: 'hidden',
              position: 'relative',
              height: '340px',
              alignItems: 'center',
            } }
          >
            {/* Slide-Container für Text */}
            <Box sx={{ position: 'relative', width: '100%', height: '100%', overflow: 'hidden' }}>
              <Box
                width="100%"
                sx={{
                  position: 'absolute',
                  left: 0,
                  top: 0,
                  zIndex: 2,
                }}
              >
                <Typography
                  variant="h6"
                  sx={{
                    fontWeight: 700,
                    letterSpacing: 2,
                    mb: 2,
                    textTransform: 'uppercase',
                    textAlign: 'left',
                    fontSize: 'clamp(1.5rem, 5vw, 3rem)',
                    color: (theme) => theme.palette.primary.main
                  }}
                >
                  {section.name?.toUpperCase() || ''}
                </Typography>
                <Typography
                  variant="body1"
                  sx={{ fontSize: 16, lineHeight: 1.7, textAlign: 'left', width: '100%' }}
                >
                  {section.text}
                </Typography>
              </Box>
            </Box>
          </Box>
        </Box>
      </Box>
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
            onClick={e => { e.stopPropagation(); setModalImage(null); }}
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
            style={{ maxHeight: '90vh', maxWidth: '90vw', borderRadius: 12, boxShadow: '0 8px 32px rgba(0,0,0,0.2)', cursor: 'auto' }}
            onClick={e => e.stopPropagation()}
          />
        </Box>
      </Modal>
    </>
  );
}