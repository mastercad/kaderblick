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
  return apiJson<VideoSegment[]>(`/video-segments?gameId=${gameId}`);
}

export async function fetchVideoSegmentsByVideo(videoId: number): Promise<VideoSegment[]> {
  return apiJson<VideoSegment[]>(`/video-segments?videoId=${videoId}`);
}

export async function saveVideoSegment(data: VideoSegmentInput): Promise<VideoSegment> {
  return apiJson<VideoSegment>('/video-segments/save', {
    method: 'POST',
    body: data,
    headers: { 'Content-Type': 'application/json' }
  });
}

export async function updateVideoSegment(id: number, data: Partial<VideoSegmentInput>): Promise<VideoSegment> {
  return apiJson<VideoSegment>(`/video-segments/update/${id}`, {
    method: 'POST',
    body: data,
    headers: { 'Content-Type': 'application/json' }
  });
}

export async function deleteVideoSegment(id: number): Promise<{ success: boolean }> {
  return apiJson<{ success: boolean }>(`/video-segments/delete/${id}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' }
  });
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
