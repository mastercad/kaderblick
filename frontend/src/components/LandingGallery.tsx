import React, { useState } from 'react';
import { Box, Typography, IconButton, Modal } from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import ArrowBackIosNewIcon from '@mui/icons-material/ArrowBackIosNew';
import ArrowForwardIosIcon from '@mui/icons-material/ArrowForwardIos';

interface GallerySection {
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
  const section = sections[current];
  const additional = section.additionalImages || [];
  const [thumbStart, setThumbStart] = useState(0);
  const thumbsPerPage = 3;

  const handlePrev = () => setCurrent((c) => (c > 0 ? c - 1 : sections.length - 1));
  const handleNext = () => setCurrent((c) => (c < sections.length - 1 ? c + 1 : 0));

  const handleThumbPrev = () => setThumbStart((s) => Math.max(0, s - thumbsPerPage));
  const handleThumbNext = () => setThumbStart((s) => Math.min(additional.length - thumbsPerPage, s + thumbsPerPage));

  return (
    <Box display="flex" pt={14} flexDirection={{ xs: 'column', md: 'row' }} alignItems="stretch" gap={4}>
      {/* Left: Main Image + Thumbnails */}
      <Box flex={{ xs: 'unset', md: 1.2 }} minWidth={0} display="flex" flexDirection="column" alignItems="center">
        <Box position="relative" width="100%" maxWidth={400} mb={2}>
          <img
            src={section.image}
            alt="Vorschau"
            style={{ width: '100%', borderRadius: 12, cursor: 'pointer', boxShadow: '0 4px 24px rgba(0,0,0,0.08)' }}
            onClick={() => setModalImage(section.image)}
          />
          <IconButton
            onClick={handlePrev}
            sx={{ position: 'absolute', top: '50%', left: -32, transform: 'translateY(-50%)', bgcolor: 'background.paper', boxShadow: 1 }}
            size="small"
          >
            <ArrowBackIosNewIcon fontSize="small" />
          </IconButton>
          <IconButton
            onClick={handleNext}
            sx={{ position: 'absolute', top: '50%', right: -32, transform: 'translateY(-50%)', bgcolor: 'background.paper', boxShadow: 1 }}
            size="small"
          >
            <ArrowForwardIosIcon fontSize="small" />
          </IconButton>
        </Box>
        {additional.length > 0 && (
          <Box display="flex" alignItems="center" gap={1}>
            <IconButton onClick={handleThumbPrev} disabled={thumbStart === 0} size="small">
              <ArrowBackIosNewIcon fontSize="small" />
            </IconButton>
            {additional.slice(thumbStart, thumbStart + thumbsPerPage).map((img, i) => (
              <Box key={img} mx={0.5}>
                <img
                  src={img}
                  alt="Zusatzbild"
                  style={{ width: 80, height: 60, objectFit: 'cover', borderRadius: 6, cursor: 'pointer', border: '2px solid #eee' }}
                  onClick={() => setModalImage(img)}
                />
              </Box>
            ))}
            <IconButton onClick={handleThumbNext} disabled={thumbStart + thumbsPerPage >= additional.length} size="small">
              <ArrowForwardIosIcon fontSize="small" />
            </IconButton>
          </Box>
        )}
      </Box>
  {/* Right: Text */}
  <Box flex={{ xs: 'unset', md: 1 }} display="flex" alignItems="center" justifyContent="center">
        <Typography
          variant="body1"
          sx={{ fontSize: { xs: 14, md: 16 }, lineHeight: 1.7, textAlign: 'left' }}
        >
          {section.text}
        </Typography>
      </Box>
      {/* Modal for large image */}
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
    </Box>
  );
}
