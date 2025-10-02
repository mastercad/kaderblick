import React, { useEffect, useState } from 'react';
import ContactModal from '../modals/ContactModal';
import Footer from './Footer';

const FooterWithContact: React.FC = () => {
  const [open, setOpen] = useState(false);

  useEffect(() => {
    const handler = () => setOpen(true);
    window.addEventListener('openContactModal', handler);
    return () => window.removeEventListener('openContactModal', handler);
  }, []);

  return (
    <>
      <Footer />
      <ContactModal open={open} onClose={() => setOpen(false)} />
    </>
  );
};

export default FooterWithContact;
