import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Hero from '../components/Hero.jsx';
import ImpactStats from '../components/ImpactStats.jsx';
import CampaignCard from '../components/CampaignCard.jsx';
import { AboutTeaser, WhyDonate, Testimonials, Transparency, Newsletter } from '../components/Sections.jsx';
import DonationForm from '../components/DonationForm.jsx';
import { getCampaigns } from '../services/campaignService.js';

export default function Home() {
  const [data, setData] = useState(null);
  useEffect(() => { getCampaigns().then(setData); }, []);
  return (
    <>
      <Hero />
      <ImpactStats />
      <AboutTeaser />
      <section className="container-x py-10">
        <div className="mb-8 flex items-end justify-between"><h2 className="text-4xl font-extrabold text-forest">Our campaigns</h2><Link to="/campaigns" className="font-bold text-leaf">View all</Link></div>
        {!data ? <p>Loading campaigns…</p> : (
          <>
            <div className="grid gap-6 md:grid-cols-3">{data.items.slice(0, 3).map((c) => <CampaignCard key={c.id} campaign={c} />)}</div>
            {data.demo && <p className="mt-3 text-xs text-ink/60">Showing demo campaigns because the API is not reachable.</p>}
          </>
        )}
      </section>
      <WhyDonate />
      <section id="donate" className="container-x py-20">
        <div className="mx-auto mb-8 max-w-xl text-center"><h2 className="text-4xl font-extrabold text-forest">Make your donation</h2><p className="mt-3">Choose an amount and a payment method. It takes about a minute.</p></div>
        <div className="mx-auto max-w-3xl"><DonationForm /></div>
      </section>
      <Testimonials />
      <Transparency />
      <Newsletter />
    </>
  );
}
