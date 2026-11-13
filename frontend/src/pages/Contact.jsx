import { useState } from 'react';
import { Loader2, MailCheck } from 'lucide-react';
import { sendEnquiry } from '../services/contactService.js';
import { errorMessage, validationErrors } from '../services/api.js';
import { useToast } from '../components/Toast.jsx';

const init = { name: '', email: '', phone: '', subject: '', message: '' };

function validate(f) {
  const e = {};
  if (f.name.trim().length < 2) e.name = 'Enter your name.';
  if (!/^\S+@\S+\.\S+$/.test(f.email)) e.email = 'Enter a valid email address.';
  if (f.message.trim().length < 10) e.message = 'Write at least 10 characters.';
  return e;
}

export default function Contact() {
  const toast = useToast();
  const [f, setF] = useState(init);
  const [err, setErr] = useState({});
  const [busy, setBusy] = useState(false);
  const [sent, setSent] = useState(false);

  const set = (k) => (e) => setF({ ...f, [k]: e.target.value });

  async function submit(e) {
    e.preventDefault();
    const v = validate(f);
    setErr(v);
    if (Object.keys(v).length) {
      toast('Please fix the highlighted fields.', 'error');
      return;
    }
    setBusy(true);
    try {
      await sendEnquiry(f);
      setSent(true);
      setF(init);
      toast('Your message has been sent. We will reply by email.');
    } catch (e2) {
      setErr(validationErrors(e2));
      toast(errorMessage(e2), 'error');
    } finally {
      setBusy(false);
    }
  }

  if (sent) {
    return (
      <div className="container-x max-w-2xl py-16">
        <div className="card p-10 text-center">
          <MailCheck className="mx-auto text-leaf" size={44} />
          <h1 className="mt-4 text-3xl font-extrabold text-forest">Message sent</h1>
          <p className="mt-3 text-ink/70">Thank you for reaching out. We have received your enquiry and will reply by email soon.</p>
          <button type="button" className="btn-primary mt-8" onClick={() => setSent(false)}>
            Send another message
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="container-x max-w-2xl py-16">
      <h1 className="text-5xl font-extrabold text-forest">Contact us</h1>
      <p className="mt-3 text-ink/70">Questions about a campaign, a donation or partnering with us? Send us a message.</p>
      <form onSubmit={submit} noValidate className="card mt-8 space-y-5 p-8">
        <div className="grid gap-5 sm:grid-cols-2">
          <div>
            <label htmlFor="name" className="label">Your name</label>
            <input id="name" className="input" value={f.name} onChange={set('name')} autoComplete="name" aria-invalid={!!err.name} />
            {err.name && <p className="err">{err.name}</p>}
          </div>
          <div>
            <label htmlFor="email" className="label">Email address</label>
            <input id="email" type="email" className="input" value={f.email} onChange={set('email')} autoComplete="email" aria-invalid={!!err.email} />
            {err.email && <p className="err">{err.email}</p>}
          </div>
          <div>
            <label htmlFor="phone" className="label">Phone number (optional)</label>
            <input id="phone" type="tel" className="input" placeholder="0712 345 678" value={f.phone} onChange={set('phone')} autoComplete="tel" />
            {err.phone && <p className="err">{err.phone}</p>}
          </div>
          <div>
            <label htmlFor="subject" className="label">Subject (optional)</label>
            <input id="subject" className="input" maxLength="150" value={f.subject} onChange={set('subject')} aria-invalid={!!err.subject} />
            {err.subject && <p className="err">{err.subject}</p>}
          </div>
        </div>
        <div>
          <label htmlFor="message" className="label">Message</label>
          <textarea id="message" rows="5" maxLength="5000" className="input" value={f.message} onChange={set('message')} aria-invalid={!!err.message} />
          {err.message && <p className="err">{err.message}</p>}
        </div>
        <button className="btn-primary" disabled={busy}>
          {busy ? <><Loader2 className="animate-spin" size={18} /> Sending</> : 'Send message'}
        </button>
      </form>
    </div>
  );
}
