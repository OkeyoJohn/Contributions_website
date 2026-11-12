import { useEffect, useRef, useState } from 'react';
import { Users, Wallet, Megaphone, MapPinned } from 'lucide-react';

// DEMO values. Replace with a backend endpoint (e.g. GET /stats) when real data exists.
const stats = [
  { icon: Users, value: 12500, label: 'People helped' },
  { icon: Wallet, value: 4200000, label: 'Donations received (KES)', compact: true },
  { icon: Megaphone, value: 12, label: 'Active campaigns' },
  { icon: MapPinned, value: 28, label: 'Communities supported' },
];

function Counter({ to, compact }) {
  const ref = useRef(null);
  const [n, setN] = useState(0);
  useEffect(() => {
    const io = new IntersectionObserver(([e]) => {
      if (!e.isIntersecting) return;
      io.disconnect();
      const start = performance.now();
      const tick = (t) => {
        const p = Math.min((t - start) / 1400, 1);
        setN(Math.round(to * (1 - Math.pow(1 - p, 3))));
        if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    });
    io.observe(ref.current);
    return () => io.disconnect();
  }, [to]);
  const text = compact ? new Intl.NumberFormat('en', { notation: 'compact', maximumFractionDigits: 1 }).format(n) : n.toLocaleString();
  return <span ref={ref}>{text}</span>;
}

export default function ImpactStats() {
  return (
    <section id="impact" className="container-x py-20">
      <div className="card grid gap-8 p-8 sm:grid-cols-2 lg:grid-cols-4">
        {stats.map(({ icon: Icon, value, label, compact }) => (
          <div key={label}>
            <Icon className="text-leaf" />
            <p className="mt-3 text-4xl font-extrabold text-forest">
              <Counter to={value} compact={compact} />+
            </p>
            <p className="text-sm text-ink/70">{label}</p>
          </div>
        ))}
      </div>
      <p className="mt-3 text-center text-xs text-ink/60">Demonstration figures. Live statistics will appear once connected to the HopeRise database.</p>
    </section>
  );
}
