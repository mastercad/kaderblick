import { apiJson } from '../utils/api';

// Video type definitions (minimal, can be extended)
export interface Video {
  id: number;
  name: string;
  url: string | null;
  youtubeId?: string | null;
  filePath?: string | null;
  gameStart?: number | null;
  sort?: number | null;
  videoType?: { id: number; name: string; sort: number } | null;
  camera?: { id: number; name: string } | null;
  length?: number | null;
  createdAt?: string;
  updatedAt?: string;
}

export interface VideoType {
  id: number;
  name: string;
  sort: number;
}

export interface Camera {
  id: number;
  name: string;
}

export interface YoutubeLink {
  [gameEventId: number]: {
    youtubeUrl: string;
  };
}

export interface VideoListResponse {
  videos: Video[];
  videoTypes: VideoType[];
  cameras: Camera[];
  youtubeLinks: YoutubeLink[];
}

export async function fetchVideos(gameId: number): Promise<VideoListResponse> {
  return apiJson(`/videos/${gameId}`);
}

export async function saveVideo(gameId: number, data: any): Promise<{ success: boolean; video: Video }> {
  return apiJson(`/videos/save/${gameId}`, {
    method: 'POST',
    body: data,
    headers: { 'Content-Type': 'application/json' }
  });
}

export async function deleteVideo(videoId: number): Promise<{ success: boolean }> {
  return apiJson(`/videos/delete/${videoId}`, {
    method: 'DELETE',
    headers: { 'Content-Type': 'application/json' }
  });
}
