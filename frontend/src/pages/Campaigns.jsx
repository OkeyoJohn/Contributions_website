import { useEffect, useState } from 'react';
import CampaignCard from '../components/CampaignCard.jsx';
import { getCampaigns } from '../services/campaignService.js';

export default function Campaigns() {
  const [data, setData] = useState(null);
  useEffect(() => { getCampaigns().then(setData); }, []);
  return (
    <div className="container-x py-16">
      <h1 className="text-5xl font-extrabold text-forest">Our campaigns</h1>
      {!data ? <p className="mt-8">Loading…</p> : (
        <>
          {data.demo && <p className="mt-3 text-sm text-ink/60">Demo campaigns shown: the API is not reachable.</p>}
          <div className="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">{data.items.map((c) => <CampaignCard key={c.id} campaign={c} />)}</div>
        </>
      )}
    </div>
  );
}
