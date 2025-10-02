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
        flex: '1 1 0%',
        minHeight: 0,
        display: 'flex',
        flexDirection: 'column',
        background: 'none',
        overflow: 'hidden',
      }}
    >
      <style>{`
        @media (max-width: 600px) {
          .home-outer {
            justify-content: center !important;
            align-items: center !important;
            padding: 0 !important;
          }
          .home-content {
            align-items: center !important;
            text-align: center !important;
            padding: 0 !important;
            margin-top: 55vw !important;
          }
          .home-title {
            font-size: 12vw !important;
            text-align: center !important;
          }
          .home-subtitle {
            font-size: 6vw !important;
            text-align: center !important;
          }
          .home-btn {
            align-self: center !important;
            margin-top: 8vw !important;
            padding-right: 0 !important;
          }
        }
      `}</style>

      <div
        className="home-outer"
        style={{
          flex: 1,
          minHeight: 0,
          display: 'flex',
          flexDirection: 'column',
          justifyContent: 'center',
          alignItems: 'flex-end',
          paddingRight: '7%',
          position: 'relative',
        }}
      >
        <div
          className="home-content"
          style={{
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'flex-end',
            width: '50vw',
            minWidth: 320,
            maxWidth: '90vw',
            textAlign: 'right',
            fontFamily: 'Anton, Impact, "Arial Black", sans-serif',
            paddingRight: 0,
            marginTop: '10vw'
          }}
        >
          <span
            className="home-title"
            style={{
              fontSize: '8vw',
              color: '#fff',
              whiteSpace: 'nowrap',
              lineHeight: 1,
              textAlign: 'right',
            }}
          >
            <span style={{ color: '#018606' }}>K</span>ADERBLICK
          </span>
          <span
            className="home-subtitle"
            style={{
              fontSize: '3.97vw',
              color: '#fff',
              marginTop: 0,
              textAlign: 'right',
              whiteSpace: 'nowrap',
            }}
          >
            DEINEN VEREIN IM BLICK
          </span>
        </div>
        <div
          className="home-btn"
          style={{
            marginTop: '8vw',
            alignSelf: 'flex-end',
            paddingRight: '13%',
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
      {/* Footer wird von App.tsx global gerendert und bleibt immer unten */}
    </div>
  );
}
