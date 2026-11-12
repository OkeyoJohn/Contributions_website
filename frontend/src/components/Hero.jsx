import { Link } from 'react-router-dom';
import { HeartHandshake } from 'lucide-react';

export default function Hero() {
  return (
    <section className="bg-cream">
      <div className="container-x grid items-center gap-12 py-16 lg:grid-cols-2 lg:py-24">
        <div>
          <p className="mb-4 text-sm font-bold tracking-widest text-leaf">TOGETHER WE CAN MAKE A DIFFERENCE</p>
          <h1 className="text-5xl font-extrabold leading-[1.05] text-forest sm:text-6xl">Small Acts.<br />Big Changes.</h1>
          <p className="mt-6 max-w-md text-lg leading-relaxed">Your generosity puts children in school, food on family tables and medicine in the hands of those who need it. Every shilling is accounted for.</p>
          <div className="mt-8 flex flex-wrap gap-4">
            <Link to="/donate" className="btn-gold">Donate Today</Link>
            <Link to="/campaigns" className="btn-outline">Explore Our Causes</Link>
          </div>
        </div>
        <div className="relative">
          <img src="https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=1100&q=80"
               alt="Children smiling together" className="aspect-[4/5] w-full rounded-[2.5rem] bg-forest/10 object-cover shadow-soft" />
          <div className="card absolute -bottom-6 left-4 flex items-center gap-3 p-4 sm:-left-6">
            <span className="grid h-11 w-11 place-items-center rounded-full bg-leaf/15 text-leaf"><HeartHandshake /></span>
            <div>
              <p className="text-2xl font-extrabold text-forest">12,500+</p>
              <p className="text-xs text-ink/70">People helped (demo value)</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
