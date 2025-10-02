import React, { useState, useEffect } from 'react';
import { Box, Tooltip } from '@mui/material';
import '../styles/section-navigation.css';

interface SectionNavigationProps {
  sections: Array<{ name: string }>;
  containerRef: React.RefObject<HTMLDivElement | null>;
}

export default function SectionNavigation({ sections, containerRef }: SectionNavigationProps) {
  const [activeIndex, setActiveIndex] = useState(0);

  useEffect(() => {
    const container = containerRef.current;
    if (!container) return;

    const handleScroll = () => {
      const scrollTop = container.scrollTop;
      const windowHeight = container.clientHeight;
      
      // +1 für Hero Section
      const totalSections = sections.length + 1;
      const currentSection = Math.round(scrollTop / windowHeight);
      
      // Hero = 0, erste Landing Section = 1, etc.
      setActiveIndex(Math.min(currentSection, totalSections - 1));
    };

    container.addEventListener('scroll', handleScroll);
    return () => container.removeEventListener('scroll', handleScroll);
  }, [sections.length, containerRef]);

  const scrollToSection = (index: number) => {
    const container = containerRef.current;
    if (!container) return;

    // index 0 = Hero, index 1+ = Landing Sections
    const targetSection = container.children[index] as HTMLElement;
    if (targetSection) {
      targetSection.scrollIntoView({ behavior: 'smooth' });
    }
  };

  return (
    <Box className="section-navigation">
      {/* Hero Section Dot */}
      <Tooltip title="Home" placement="left" arrow>
        <Box
          className={`section-nav-dot ${activeIndex === 0 ? 'active' : ''}`}
          onClick={() => scrollToSection(0)}
        />
      </Tooltip>

      {/* Landing Sections Dots */}
      {sections.map((section, index) => (
        <Tooltip key={index} title={section.name} placement="left" arrow>
          <Box
            className={`section-nav-dot ${activeIndex === index + 1 ? 'active' : ''}`}
            onClick={() => scrollToSection(index + 1)}
          />
        </Tooltip>
      ))}
    </Box>
  );
}
