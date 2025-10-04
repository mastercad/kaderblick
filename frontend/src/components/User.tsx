import React from "react";
import Avatar from "@mui/material/Avatar";
import Box from "@mui/material/Box";
import Typography from "@mui/material/Typography";
import Badge from "@mui/material/Badge";
import { FaUserAlt } from "react-icons/fa";
import { BACKEND_URL } from "../../config";
// Alternativ: import { FaFutbol } from "react-icons/fa";

interface UserProps {
  icon: React.ReactNode | string; // Avatar-URL oder Icon-Komponente
  name: string;
  title?: string;
  level?: number;
  xp?: number;
  avatarSize?: number; // px
  fontSize?: number; // px
}

export const User: React.FC<UserProps> = ({ icon, name, avatarSize = 48, fontSize = 16 }) => {
  // Icon size for react-icon inside Avatar (slightly smaller than avatarSize)
  const iconInnerSize = Math.round(avatarSize * 0.6);
  let avatarContent: React.ReactNode;
  if (typeof icon === "string") {
    if (icon && icon.trim() !== "") {
      avatarContent = <Avatar src={`${BACKEND_URL}/uploads/avatar/${icon}`} alt="Avatar" sx={{ width: avatarSize, height: avatarSize }} />;
    } else {
      avatarContent = <Avatar sx={{ width: avatarSize, height: avatarSize }}><FaUserAlt size={iconInnerSize} /></Avatar>;
    }
  } else if (icon) {
    avatarContent = <Avatar sx={{ width: avatarSize, height: avatarSize }}>{icon}</Avatar>;
  } else {
    avatarContent = <Avatar sx={{ width: avatarSize, height: avatarSize }}><FaUserAlt size={iconInnerSize} /></Avatar>;
  }

  return (
    <Box display="flex" alignItems="center" sx={{ display: 'inline-flex' }}>
      <Badge
        overlap="circular"
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
        badgeContent={null}
        sx={{
          mr: 1,
          '& .MuiBadge-badge': {
            border: '2px solid #fff',
            padding: 0,
          },
        }}
      >
        {avatarContent}
      </Badge>
      <Typography variant="subtitle1" fontWeight={500} fontSize={fontSize}>
        {name}
      </Typography>
    </Box>
  );
};

export default User;
