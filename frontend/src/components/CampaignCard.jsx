import { Link } from 'react-router-dom';

export const kes = (n) => `KES ${Number(n || 0).toLocaleString()}`;

export default function CampaignCard({ campaign: c }) {
  const pct = c.goal_amount ? Math.min(100, Math.round((c.raised_amount / c.goal_amount) * 100)) : 0;
  return (
    <article className="card group flex flex-col overflow-hidden transition hover:-translate-y-1">
      <Link to={`/campaigns/${c.slug}`} className="block overflow-hidden">
        <img src={c.image_url} alt={c.title} loading="lazy" className="h-52 w-full bg-forest/10 object-cover transition duration-500 group-hover:scale-105" />
      </Link>
      <div className="flex flex-1 flex-col p-6">
        <h3 className="text-xl font-extrabold text-forest">{c.title}</h3>
        <p className="mt-2 flex-1 text-sm leading-relaxed text-ink/80">{c.description}</p>
        <div className="mt-5" role="progressbar" aria-valuenow={pct} aria-valuemin="0" aria-valuemax="100" aria-label={`${c.title} progress`}>
          <div className="h-2.5 overflow-hidden rounded-full bg-forest/10"><div className="h-full rounded-full bg-leaf" style={{ width: `${pct}%` }} /></div>
          <div className="mt-2 flex justify-between text-sm"><span><b>{kes(c.raised_amount)}</b> raised</span><span>Goal {kes(c.goal_amount)}</span></div>
        </div>
        <Link to={`/donate?campaign=${c.id}`} className="btn-primary mt-5">Donate</Link>
      </div>
    </article>
  );
}
