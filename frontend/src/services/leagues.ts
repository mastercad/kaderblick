import { apiJson } from '../utils/api';
import { League } from '../types/league';

export async function fetchLeagues(): Promise<League[]> {
  const res = await apiJson<{ leagues: League[] }>('/api/leagues');
  return res.leagues || [];
}
