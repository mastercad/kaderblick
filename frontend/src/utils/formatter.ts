const formatDateTime = (dateString: string) => {
    const date = new Date(dateString);
    const pad = (n: number) => n.toString().padStart(2, '0');
    return `${pad(date.getUTCDate())}.${pad(date.getUTCMonth() + 1)}.${date.getUTCFullYear()} ${pad(date.getUTCHours())}:${pad(date.getUTCMinutes())}`;
};

const formatEventTime = (eventDate: string, gameStartDate: string) => {
    const eventTime = new Date(eventDate);
    const gameStart = new Date(gameStartDate);
    const diffMs = eventTime.getTime() - gameStart.getTime();
    const diffMinutes = Math.floor(diffMs / (1000 * 60));
    return `${diffMinutes}'`;
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    const pad = (n: number) => n.toString().padStart(2, '0');
    return `${pad(date.getUTCHours())}:${pad(date.getUTCMinutes())}`;
};

export { formatDateTime, formatEventTime, formatTime };