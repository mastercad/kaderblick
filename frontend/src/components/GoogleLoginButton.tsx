import Button from '@mui/material/Button';
import GoogleIcon from '@mui/icons-material/Google';
import { BACKEND_URL } from '../../config';

export default function GoogleLoginButton() {
    const handleGoogleLogin = () => {
        const width = 500, height = 600;
        const left = window.screenX + (window.outerWidth - width) / 2;
        const top = window.screenY + (window.outerHeight - height) / 2;
        window.open(
            `${BACKEND_URL}/connect/google`,
            'GoogleLogin',
            `width=${width},height=${height},left=${left},top=${top},popup=yes`
        );
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
