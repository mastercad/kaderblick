import React, { useEffect } from 'react';
import { Button } from '@mui/material';
import { useNavigate } from 'react-router-dom';
import "@fontsource/anton";

export default function Home() {
  const navigate = useNavigate();

  useEffect(() => {
    const original = document.body.style.background;
    document.body.style.background = `url(/images/landing_page/background.jpg) center center / cover no-repeat fixed`;
    return () => {
      document.body.style.background = original;
    };
  }, []);

  return (
    <div
      style={{
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        display: 'flex',
        flexDirection: 'column',
        background: 'none',
      }}
    >
      <style>{`
        .home-outer {
          flex: 1;
          display: flex;
          flex-direction: column;
          justify-content: flex-start;
          align-items: flex-end;
          padding-right: 7%;
          padding-top: 7vh;
          position: relative;
        }
        
        @media (max-width: 600px) {
          .home-outer {
            justify-content: flex-start;
            align-items: center;
            padding: 0;
            padding-top: 70%;
          }
          .home-content {
            align-items: center !important;
            text-align: center !important;
            width: 80%;
          }
          .home-title {
            font-size: clamp(3rem, 16vw, 6rem) !important;
            text-align: center !important;
            width: 100%;
            white-space: normal !important;
            word-wrap: break-word;
          }
          .home-subtitle {
            font-size: clamp(1.5rem, 8vw, 3rem) !important;
            text-align: center !important;
            margin-top: 0 !important;
            width: 100%;
            white-space: normal !important;
            word-wrap: break-word;
          }
          .home-btn {
            align-self: center !important;
            margin-top: 10rem !important;
            padding-right: 0 !important;
          }
          .home-btn button {
            padding: 1rem 2rem !important;
            font-size: 1.25rem !important;
          }
        }
      `}</style>

      <div className="home-outer">
        <div
          className="home-content"
          style={{
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'flex-end',
            textAlign: 'right',
            fontFamily: 'ImpactWeb, Anton, Impact, "Arial Black", sans-serif',
          }}
        >
          <span
            className="home-title"
            style={{
              fontSize: 'clamp(4rem, 12vw, 12rem)',
              color: '#fff',
              whiteSpace: 'nowrap',
              lineHeight: 1,
            }}
          >
            <span style={{ color: '#018606' }}>K</span>ADERBLICK
          </span>
          <span
            className="home-subtitle"
            style={{
              fontSize: 'clamp(2rem, 5.97vw, 5.97rem)',
              color: '#fff',
              whiteSpace: 'nowrap',
              lineHeight: 1,
            }}
          >
            DEINEN VEREIN IM BLICK
          </span>
        </div>
        <div
          className="home-btn"
          style={{
            marginTop: '10rem',
            alignSelf: 'flex-end',
            paddingRight: '12%',
          }}
        >
          <Button
            variant="contained"
            color="success"
            sx={{ p: 3, fontSize: '2rem' }}
            onClick={() => navigate('/landing')}
          >
            Jetzt starten
          </Button>
        </div>
      </div>
    </div>
  );
}
