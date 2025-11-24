import { apiJson } from '../utils/api';
import { BACKEND_URL } from '../../config';

export interface VideoSegment {
  id: number;
  videoId: number;
  videoName: string;
  startMinute: number;
  lengthSeconds: number;
  title: string | null;
  subTitle: string | null;
  includeAudio: boolean;
  sortOrder: number;
  createdAt?: string;
  updatedAt?: string;
}

export interface VideoSegmentInput {
  videoId: number;
  startMinute: number;
  lengthSeconds: number;
  title?: string | null;
  subTitle?: string | null;
  includeAudio?: boolean;
  sortOrder?: number;
}

export async function fetchVideoSegments(gameId: number): Promise<VideoSegment[]> {
  const result = await apiJson<VideoSegment[]>(`/video-segments?gameId=${gameId}`);
  if ('error' in result) {
    throw new Error(result.error);
  }
  return result;
}

export async function fetchVideoSegmentsByVideo(videoId: number): Promise<VideoSegment[]> {
  const result = await apiJson<VideoSegment[]>(`/video-segments?videoId=${videoId}`);
  if ('error' in result) {
    throw new Error(result.error);
  }
  return result;
}

export async function saveVideoSegment(data: VideoSegmentInput): Promise<VideoSegment> {
  const result = await apiJson<VideoSegment>('/video-segments/save', {
    method: 'POST',
    body: data,
    headers: { 'Content-Type': 'application/json' }
  });
  if ('error' in result) {
    throw new Error(result.error);
  }
  return result;
}

export async function updateVideoSegment(id: number, data: Partial<VideoSegmentInput>): Promise<VideoSegment> {
  const result = await apiJson<VideoSegment>(`/video-segments/update/${id}`, {
    method: 'POST',
    body: data,
    headers: { 'Content-Type': 'application/json' }
  });
  if ('error' in result) {
    throw new Error(result.error);
  }
  return result;
}

export async function deleteVideoSegment(id: number): Promise<{ success: boolean }> {
  const result = await apiJson<{ success: boolean }>(`/video-segments/delete/${id}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' }
  });
  if ('error' in result) {
    throw new Error(result.error);
  }
  return result;
}

export async function exportVideoSegments(gameId: number): Promise<Blob> {
  const response = await fetch(`${BACKEND_URL}/video-segments/export/${gameId}`, {
    method: 'GET',
    credentials: 'include',
  });

  if (!response.ok) {
    throw new Error('Export fehlgeschlagen');
  }

  return response.blob();
}
