import { Link } from 'react-router-dom';
import { Facebook, Instagram, Twitter, Mail, Phone, MapPin } from 'lucide-react';
import { Logo } from './Navbar.jsx';

export default function Footer() {
  return (
    <footer className="mt-24 bg-forest text-white/80">
      <div className="container-x grid gap-10 py-16 md:grid-cols-4">
        <div className="md:col-span-1">
          <Logo light />
          <p className="mt-4 text-sm">Small acts, big changes. Supporting children, families, education and healthcare.</p>
          <div className="mt-5 flex gap-3">
            {[Facebook, Instagram, Twitter].map((I, i) => (
              <a key={i} href="#" aria-label="Social link" className="grid h-9 w-9 place-items-center rounded-full bg-white/10 hover:bg-gold hover:text-forest"><I size={16} /></a>
            ))}
          </div>
        </div>
        <div>
          <h3 className="mb-3 font-bold text-white">Quick links</h3>
          <ul className="space-y-2 text-sm">
            <li><Link to="/about">About Us</Link></li><li><Link to="/campaigns">Campaigns</Link></li>
            <li><Link to="/donate">Donate</Link></li><li><Link to="/contact">Contact</Link></li>
          </ul>
        </div>
        <div>
          <h3 className="mb-3 font-bold text-white">Campaigns</h3>
          <ul className="space-y-2 text-sm">
            <li><Link to="/campaigns/education-for-every-child">Education</Link></li>
            <li><Link to="/campaigns/feed-a-family">Feed a Family</Link></li>
            <li><Link to="/campaigns/community-healthcare">Healthcare</Link></li>
          </ul>
        </div>
        <div>
          <h3 className="mb-3 font-bold text-white">Contact</h3>
          <ul className="space-y-2 text-sm">
            <li className="flex gap-2"><MapPin size={16} /> [Office address]</li>
            <li className="flex gap-2"><Phone size={16} /> [Phone number]</li>
            <li className="flex gap-2"><Mail size={16} /> [info@yourdomain]</li>
          </ul>
        </div>
      </div>
      <div className="border-t border-white/10 py-5 text-center text-sm">
        © {new Date().getFullYear()} HopeRise. <Link to="/privacy" className="underline">Privacy Policy</Link> · <Link to="/privacy#terms" className="underline">Terms and Conditions</Link>
      </div>
    </footer>
  );
}
