import { useState } from 'react';
import { Link, NavLink } from 'react-router-dom';
import { Heart, Menu, X } from 'lucide-react';

const links = [
  ['/', 'Home'], ['/about', 'About Us'], ['/campaigns', 'Our Campaigns'],
  ['/#impact', 'Our Impact'], ['/contact', 'Contact'],
];

export function Logo({ light }) {
  return (
    <Link to="/" className={`flex items-center gap-2 text-xl font-extrabold ${light ? 'text-white' : 'text-forest'}`}>
      <span className="grid h-9 w-9 place-items-center rounded-full bg-gold text-forest"><Heart size={18} fill="currentColor" /></span>
      HopeRise
    </Link>
  );
}

export default function Navbar() {
  const [open, setOpen] = useState(false);
  const cls = ({ isActive }) => `font-medium transition hover:text-leaf ${isActive ? 'text-leaf' : 'text-ink'}`;
  return (
    <header className="sticky top-0 z-40 border-b border-forest/10 bg-mist/90 backdrop-blur">
      <div className="container-x flex h-20 items-center justify-between">
        <Logo />
        <nav className="hidden items-center gap-8 lg:flex" aria-label="Main">
          {links.map(([to, label]) => to.includes('#')
            ? <Link key={to} to={to} className="font-medium text-ink transition hover:text-leaf">{label}</Link>
            : <NavLink key={to} to={to} end={to === '/'} className={cls}>{label}</NavLink>)}
          <Link to="/donate" className="btn-primary !py-2.5">Donate Now</Link>
        </nav>
        <button className="lg:hidden" onClick={() => setOpen(!open)} aria-label="Toggle menu" aria-expanded={open}>
          {open ? <X /> : <Menu />}
        </button>
      </div>
      {open && (
        <nav className="container-x flex flex-col gap-4 pb-6 lg:hidden" aria-label="Mobile">
          {links.map(([to, label]) => (
            <Link key={to} to={to} onClick={() => setOpen(false)} className="py-1 font-medium">{label}</Link>
          ))}
          <Link to="/donate" onClick={() => setOpen(false)} className="btn-primary">Donate Now</Link>
        </nav>
      )}
    </header>
  );
}
