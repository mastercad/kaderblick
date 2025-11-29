import React from "react";
import RoomIcon from '@mui/icons-material/Room';
import Link from '@mui/material/Link';

export interface LocationDisplayProps {
  id: number;
  name: string;
  latitude?: number;
  longitude?: number;
  address?: string;
}

const Location: React.FC<LocationDisplayProps> = ({ name, latitude, longitude, address }) => {
  // Erzeuge Google Maps Link
  let mapsUrl = '';
  if (latitude && longitude) {
    mapsUrl = `https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`;
  } else if (address) {
    mapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`;
  }

  return (
    <span>
      {mapsUrl ? (
        <Link
          href={mapsUrl}
          underline="always"
          sx={{
            display: 'inline-flex',
            alignItems: 'center',
            gap: 0.5,
            color: 'primary.main',
            cursor: 'pointer',
            textDecoration: 'underline !important',
            fontWeight: 500
          }}
          onClick={e => {
            e.preventDefault();
            e.stopPropagation();
            window.open(mapsUrl, '_blank', 'noopener,noreferrer');
          }}
        >
          <RoomIcon fontSize="small" color="primary" />
          {name}
        </Link>
      ) : (
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 4 }}>
          <RoomIcon fontSize="small" color="disabled" />
          {name}
        </span>
      )}
    </span>
  );
};

export default Location;