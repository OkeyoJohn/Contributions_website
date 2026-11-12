import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { getCampaign } from '../services/campaignService.js';
import { kes } from '../components/CampaignCard.jsx';

export default function CampaignDetails() {
  const { slug } = useParams();
  const [state, setState] = useState({ loading: true });
  useEffect(() => {
    setState({ loading: true });
    getCampaign(slug).then((r) => setState(r)).catch(() => setState({ error: true }));
  }, [slug]);

  if (state.loading) return <p className="container-x py-24">Loading…</p>;
  if (state.error) return <div className="container-x py-24"><h1 className="text-3xl font-extrabold">Campaign not found</h1><Link to="/campaigns" className="btn-primary mt-6">Back to campaigns</Link></div>;
  const c = state.item;
  const pct = c.goal_amount ? Math.min(100, Math.round((c.raised_amount / c.goal_amount) * 100)) : 0;
  return (
    <div className="container-x grid gap-10 py-16 lg:grid-cols-5">
      <img src={c.image_url} alt={c.title} className="w-full rounded-[2rem] bg-forest/10 object-cover lg:col-span-3" />
      <div className="lg:col-span-2">
        <h1 className="text-4xl font-extrabold text-forest">{c.title}</h1>
        <p className="mt-4 leading-relaxed">{c.long_description || c.description}</p>
        <div className="mt-6 h-3 overflow-hidden rounded-full bg-forest/10"><div className="h-full bg-leaf" style={{ width: `${pct}%` }} /></div>
        <p className="mt-2 text-sm"><b>{kes(c.raised_amount)}</b> raised of {kes(c.goal_amount)} ({pct}%)</p>
        <Link to={`/donate?campaign=${c.id}`} className="btn-gold mt-6">Donate to this campaign</Link>
      </div>
    </div>
  );
}
