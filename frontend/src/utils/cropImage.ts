// Liefert ein DataURL des gecroppten Bildes
// imageSrc: string (URL oder DataURL), crop: { x, y, width, height }
export default function getCroppedImg(imageSrc: string, crop: { x: number, y: number, width: number, height: number }): Promise<string> {
  return new Promise((resolve, reject) => {
    const image = new window.Image();
    image.crossOrigin = 'anonymous';
    image.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = crop.width;
      canvas.height = crop.height;
      const ctx = canvas.getContext('2d');
      if (!ctx) return reject('No 2d context');
      ctx.drawImage(
        image,
        crop.x,
        crop.y,
        crop.width,
        crop.height,
        0,
        0,
        crop.width,
        crop.height
      );
      resolve(canvas.toDataURL('image/png'));
    };
    image.onerror = reject;
    image.src = imageSrc;
  });
}
