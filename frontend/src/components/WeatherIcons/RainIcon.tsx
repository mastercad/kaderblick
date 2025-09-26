import React from "react";

export const RainIcon = ({ size = 64, color = "currentColor" }) => (
  <svg viewBox="0 0 64 64" width={size} height={size} fill={color}>
    <path d="M20 36h24c8 0 14-6 14-14s-6-14-14-14c-1.8 0-3.5.3-5 .9C36.3 2.7 30.7-2 24-2c-8.8 0-16 7.2-16 16 0 1.7.3 3.3.9 4.8C5.6 21.3 2 26 2 32c0 6.6 5.4 12 12 12h6z" />
    <g stroke={color} strokeWidth="2" fill="none">
      <line x1="20" y1="48" x2="18" y2="60" />
      <line x1="30" y1="48" x2="28" y2="60" />
      <line x1="40" y1="48" x2="38" y2="60" />
    </g>
  </svg>
);
