/// <reference types="vite/client" />

// Deklaration für Bildimporte mit ?url Query
declare module '*.png?url' {
  const value: string;
  export default value;
}

declare module '*.jpg?url' {
  const value: string;
  export default value;
}

declare module '*.jpeg?url' {
  const value: string;
  export default value;
}

declare module '*.svg?url' {
  const value: string;
  export default value;
}

declare module '*.gif?url' {
  const value: string;
  export default value;
}

declare module '*.webp?url' {
  const value: string;
  export default value;
}

// Standard Bildimporte ohne Query
declare module '*.png' {
  const value: string;
  export default value;
}

declare module '*.jpg' {
  const value: string;
  export default value;
}

declare module '*.jpeg' {
  const value: string;
  export default value;
}

declare module '*.svg' {
  const value: string;
  export default value;
}

declare module '*.gif' {
  const value: string;
  export default value;
}

declare module '*.webp' {
  const value: string;
  export default value;
}
