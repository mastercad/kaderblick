import React from "react";

export const SunIcon = ({ size = 64, color = "currentColor" }) => (
  <svg viewBox="0 0 64 64" width={size} height={size} fill={color}>
    <circle cx="32" cy="32" r="14" />
    <g stroke={color} strokeWidth="4">
      <line x1="32" y1="2" x2="32" y2="14" />
      <line x1="32" y1="50" x2="32" y2="62" />
      <line x1="2" y1="32" x2="14" y2="32" />
      <line x1="50" y1="32" x2="62" y2="32" />
      <line x1="10" y1="10" x2="20" y2="20" />
      <line x1="44" y1="44" x2="54" y2="54" />
      <line x1="10" y1="54" x2="20" y2="44" />
      <line x1="44" y1="20" x2="54" y2="10" />
    </g>
  </svg>
);
