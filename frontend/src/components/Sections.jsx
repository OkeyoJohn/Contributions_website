import { useState } from 'react';
import { Link } from 'react-router-dom';
import { ShieldCheck, FileBarChart, HandHeart, CreditCard, Quote, Mail } from 'lucide-react';
import { useToast } from './Toast.jsx';

export function AboutTeaser() {
  return (
    <section className="container-x grid items-center gap-12 py-20 lg:grid-cols-2">
      <img src="https://images.unsplash.com/photo-1509099836639-18ba1795216d?auto=format&fit=crop&w=1000&q=80" alt="Volunteers working with the community" loading="lazy" className="aspect-[5/4] w-full rounded-[2rem] bg-forest/10 object-cover shadow-soft" />
      <div>
        <h2 className="text-4xl font-extrabold text-forest">We exist so no child is left behind</h2>
        <p className="mt-5 max-w-prose leading-relaxed">HopeRise is a charity that connects generous people with vulnerable children and struggling families. We fund education, food, healthcare and community projects, and we show you where every donation goes.</p>
        <Link to="/about" className="btn-outline mt-7">Read our story</Link>
      </div>
    </section>
  );
}

const why = [
  [ShieldCheck, 'Secure donations', 'Card details are handled only by Paystack and PayPal.'],
  [FileBarChart, 'Transparent reporting', 'See how funds are used in our regular reports.'],
  [HandHeart, 'Direct community impact', 'Donations go to projects run with local communities.'],
  [CreditCard, 'Trusted payment options', 'Pay with M-Pesa, Visa, Mastercard or PayPal.'],
];
export function WhyDonate() {
  return (
    <section className="bg-cream py-20">
      <div className="container-x">
        <h2 className="max-w-xl text-4xl font-extrabold text-forest">Why donate with us</h2>
        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {why.map(([I, t, d]) => (
            <div key={t} className="card p-6"><I className="text-leaf" /><h3 className="mt-4 font-extrabold text-forest">{t}</h3><p className="mt-2 text-sm">{d}</p></div>
          ))}
        </div>
      </div>
    </section>
  );
}

// DEMO content: replace with real donor stories (with permission).
const quotes = [
  ['Sample donor', 'Giving was quick, and I got a clear update on what my gift achieved.'],
  ['Sample donor', 'I chose a campaign, paid by M-Pesa and received confirmation within minutes.'],
  ['Sample donor', 'It is rare to see a charity explain its spending this clearly.'],
];
export function Testimonials() {
  return (
    <section className="container-x py-20">
      <h2 className="text-4xl font-extrabold text-forest">What donors say</h2>
      <div className="mt-10 grid gap-6 md:grid-cols-3">
        {quotes.map(([n, q], i) => (
          <figure key={i} className="card p-7"><Quote className="text-gold" /><blockquote className="mt-3 leading-relaxed">{q}</blockquote><figcaption className="mt-4 text-sm font-bold text-forest">{n}</figcaption></figure>
        ))}
      </div>
      <p className="mt-4 text-xs text-ink/60">Demonstration testimonials. Replace with real donor stories.</p>
    </section>
  );
}

export function Transparency() {
  return (
    <section className="container-x py-10">
      <div className="rounded-[2rem] bg-forest p-10 text-white md:flex md:items-center md:justify-between md:gap-10">
        <div><h2 className="text-3xl font-extrabold">Every shilling accounted for</h2>
          <p className="mt-3 max-w-xl text-white/80">We publish how donations are spent so you can see the difference you make.</p></div>
        <a href="#" className="btn-gold mt-6 md:mt-0">View financial reports</a>
      </div>
    </section>
  );
}

export function Newsletter() {
  const toast = useToast();
  const [email, setEmail] = useState('');
  const submit = (e) => {
    e.preventDefault();
    if (!/^\S+@\S+\.\S+$/.test(email)) return toast('Enter a valid email address.', 'error');
    // TODO: POST /newsletter once the Laravel endpoint exists
    toast('Subscription will be enabled when the backend endpoint is connected.');
  };
  return (
    <section className="container-x py-20 text-center">
      <Mail className="mx-auto text-leaf" />
      <h2 className="mt-3 text-3xl font-extrabold text-forest">Stay close to the impact</h2>
      <form onSubmit={submit} className="mx-auto mt-6 flex max-w-md flex-col gap-3 sm:flex-row" noValidate>
        <label htmlFor="nl" className="sr-only">Email address</label>
        <input id="nl" type="email" className="input" placeholder="you@example.com" value={email} onChange={(e) => setEmail(e.target.value)} />
        <button className="btn-primary">Subscribe</button>
      </form>
    </section>
  );
}
