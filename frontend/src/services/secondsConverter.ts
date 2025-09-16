export async function convertSecondsToMinutes(seconds: number) {
    return parseInt(Math.floor(seconds / 60).toString());
}
