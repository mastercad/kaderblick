import Button from '@mui/material/Button';
import GoogleIcon from '@mui/icons-material/Google';
import { BACKEND_URL } from '../../config';
import { apiJson } from '../utils/api';

export default function GoogleLoginButton() {
    const handleGoogleLogin = () => {
        const width = 500, height = 600;
        const left = (screen.width/2)-(width/2);
        const top = (screen.height/2)-(height/2);
        const popup = window.open(`${BACKEND_URL}/connect/google`, 'GoogleLogin', `width=${width},height=${height},top=${top},left=${left}`);
        if (!popup) return;
        const pollTimer = setInterval(function() {
            if (popup.closed) {
                clearInterval(pollTimer);
                apiJson('/api/about-me')
                    .then(() => window.location.reload())
                    .catch(() => {/* do nothing */});
            }
        }, 500);
    };

    return (
        <Button
            variant="outlined"
            startIcon={<GoogleIcon />}
            onClick={handleGoogleLogin}
            sx={{
                background: '#fff',
                color: '#444',
                borderColor: '#ddd',
                textTransform: 'none',
                fontWeight: 500,
                fontSize: 16,
                boxShadow: '0 1px 2px rgba(0,0,0,0.05)',
                mt: 1,
                '&:hover': {
                    background: '#f5f5f5',
                    borderColor: '#bbb',
                },
        }}
        fullWidth
        >
            Mit Google anmelden
        </Button>
    );
}
