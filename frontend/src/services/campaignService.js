import api, { unwrap } from './api.js';

// Demo campaigns, used ONLY when the API is unreachable. Flagged with demo: true.
export const demoCampaigns = [
  { id: 1, slug: 'education-for-every-child', title: 'Education for Every Child', demo: true,
    description: 'School fees, uniforms and books for children who would otherwise stay home.',
    goal_amount: 500000, raised_amount: 0,
    image_url: 'https://images.unsplash.com/photo-1497486751825-1233686d5d80?auto=format&fit=crop&w=900&q=80' },
  { id: 2, slug: 'feed-a-family', title: 'Feed a Family', demo: true,
    description: 'Monthly food packages for families facing hunger.',
    goal_amount: 300000, raised_amount: 0,
    image_url: 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=900&q=80' },
  { id: 3, slug: 'community-healthcare', title: 'Community Healthcare', demo: true,
    description: 'Mobile clinics and essential medicine for remote communities.',
    goal_amount: 800000, raised_amount: 0,
    image_url: 'https://images.unsplash.com/photo-1576765608535-5f04d1e3f289?auto=format&fit=crop&w=900&q=80' },
];

export async function getCampaigns() {
  try { return { items: unwrap(await api.get('/campaigns')), demo: false }; }
  catch { return { items: demoCampaigns, demo: true }; }
}

export async function getCampaign(slug) {
  try { return { item: unwrap(await api.get(`/campaigns/${slug}`)), demo: false }; }
  catch (e) {
    const d = demoCampaigns.find((c) => c.slug === slug);
    if (d && !e.response) return { item: d, demo: true };
    throw e;
  }
}
