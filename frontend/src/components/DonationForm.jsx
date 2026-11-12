import { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Loader2, Lock } from 'lucide-react';
import PaymentMethods from './PaymentMethods.jsx';
import { getCampaigns } from '../services/campaignService.js';
import { createDonation } from '../services/donationService.js';
import { errorMessage, validationErrors } from '../services/api.js';
import { useToast } from './Toast.jsx';

const presets = [100, 250, 500, 1000, 2500, 5000];
const init = { name: '', email: '', phone: '', amount: 1000, campaign_id: '', anonymous: false, message: '', payment_method: 'mpesa' };

// Keep this outside the component: redefining it on every render would make
// React remount the inputs and drop focus after each keystroke.
function Field({ id, label, error, ...props }) {
  return (
    <div>
      <label htmlFor={id} className="label">{label}</label>
      <input id={id} className="input" aria-invalid={!!error} aria-describedby={error ? `${id}-e` : undefined} {...props} />
      {error && <p id={`${id}-e`} className="err">{error}</p>}
    </div>
  );
}

function validate(f) {
  const e = {};
  if (f.name.trim().length < 2) e.name = 'Enter your full name.';
  if (!/^\S+@\S+\.\S+$/.test(f.email)) e.email = 'Enter a valid email address.';
  if (!(Number(f.amount) >= 50)) e.amount = 'The minimum donation is KES 50.';
  if (f.payment_method === 'mpesa' && !/^(?:\+?254|0)?[71]\d{8}$/.test(f.phone.replace(/\s/g, '')))
    e.phone = 'Enter a Kenyan number such as 0712 345 678.';
  return e;
}

export default function DonationForm() {
  const [params] = useSearchParams();
  const toast = useToast();
  const [f, setF] = useState({ ...init, campaign_id: params.get('campaign') || '' });
  const [errors, setErrors] = useState({});
  const [campaigns, setCampaigns] = useState([]);
  const [busy, setBusy] = useState(false);

  useEffect(() => { getCampaigns().then((r) => setCampaigns(r.items)); }, []);
  const set = (k, v) => setF((p) => ({ ...p, [k]: v }));

  async function submit(e) {
    e.preventDefault();
    const v = validate(f);
    setErrors(v);
    if (Object.keys(v).length) return toast('Please fix the highlighted fields.', 'error');
    setBusy(true);
    try {
      const d = await createDonation({ ...f, amount: Number(f.amount), currency: 'KES', campaign_id: f.campaign_id || null });
      if (d.redirect_url) return (window.location.href = d.redirect_url); // Paystack / PayPal hosted pages
      toast('Check your phone and enter your M-Pesa PIN.');
      window.location.href = `/donation/success?reference=${d.reference}`; // page polls Laravel for the verified status
    } catch (err) {
      setErrors(validationErrors(err));
      toast(errorMessage(err), 'error');
      setBusy(false);
    }
  }

  return (
    <form onSubmit={submit} noValidate className="card space-y-6 p-6 sm:p-10">
      <div className="grid gap-5 sm:grid-cols-2">
        <Field id="name" label="Full name" value={f.name} error={errors.name} onChange={(e) => set('name', e.target.value)} autoComplete="name" />
        <Field id="email" type="email" label="Email address" value={f.email} error={errors.email} onChange={(e) => set('email', e.target.value)} autoComplete="email" />
        <Field id="phone" type="tel" label={f.payment_method === 'mpesa' ? 'M-Pesa phone number' : 'Phone number (optional)'} placeholder="0712 345 678" value={f.phone} error={errors.phone} onChange={(e) => set('phone', e.target.value)} autoComplete="tel" />
        <div>
          <label htmlFor="campaign" className="label">Campaign</label>
          <select id="campaign" className="input" value={f.campaign_id} onChange={(e) => set('campaign_id', e.target.value)}>
            <option value="">Where it is needed most</option>
            {campaigns.map((c) => <option key={c.id} value={c.id}>{c.title}</option>)}
          </select>
        </div>
      </div>

      <fieldset>
        <legend className="label">Donation amount (KES)</legend>
        <div className="grid grid-cols-3 gap-3 sm:grid-cols-6">
          {presets.map((a) => (
            <button type="button" key={a} onClick={() => set('amount', a)} aria-pressed={Number(f.amount) === a}
              className={`rounded-xl border-2 py-3 font-bold transition ${Number(f.amount) === a ? 'border-leaf bg-leaf text-white' : 'border-forest/10 text-forest hover:border-leaf'}`}>
              {a.toLocaleString()}
            </button>
          ))}
        </div>
        <input id="amount" type="number" min="50" className="input mt-3" placeholder="Custom amount" aria-label="Custom amount in KES"
          value={f.amount} onChange={(e) => set('amount', e.target.value)} />
        {errors.amount && <p className="err">{errors.amount}</p>}
      </fieldset>

      <div>
        <label htmlFor="message" className="label">Dedication or message (optional)</label>
        <textarea id="message" rows="3" maxLength="300" className="input" value={f.message} onChange={(e) => set('message', e.target.value)} />
      </div>
      <label className="flex items-center gap-3"><input type="checkbox" className="h-5 w-5 accent-[#269B65]" checked={f.anonymous} onChange={(e) => set('anonymous', e.target.checked)} /> Make my donation anonymous</label>

      <PaymentMethods value={f.payment_method} onChange={(v) => set('payment_method', v)} />

      <button className="btn-gold w-full !py-4 text-lg" disabled={busy}>
        {busy ? <><Loader2 className="animate-spin" /> Processing</> : <>Donate KES {Number(f.amount || 0).toLocaleString()}</>}
      </button>
      <p className="flex items-center justify-center gap-2 text-sm text-ink/70"><Lock size={14} /> Payments are processed by M-Pesa, Paystack and PayPal. We never store card details.</p>
    </form>
  );
}
