import { useState } from 'react';
import { useToast } from '../components/Toast.jsx';

export default function Contact() {
  const toast = useToast();
  const [f, setF] = useState({ name: '', email: '', message: '' });
  const [err, setErr] = useState({});
  const submit = (e) => {
    e.preventDefault();
    const v = {};
    if (!f.name.trim()) v.name = 'Enter your name.';
    if (!/^\S+@\S+\.\S+$/.test(f.email)) v.email = 'Enter a valid email address.';
    if (f.message.trim().length < 10) v.message = 'Write at least 10 characters.';
    setErr(v);
    // TODO: POST /contact once the Laravel endpoint exists
    if (!Object.keys(v).length) toast('Form is valid. Connect the /contact endpoint to send it.');
  };
  const set = (k) => (e) => setF({ ...f, [k]: e.target.value });
  return (
    <div className="container-x max-w-2xl py-16">
      <h1 className="text-5xl font-extrabold text-forest">Contact us</h1>
      <form onSubmit={submit} noValidate className="card mt-8 space-y-5 p-8">
        {[['name', 'Your name'], ['email', 'Email address']].map(([k, l]) => (
          <div key={k}><label htmlFor={k} className="label">{l}</label><input id={k} className="input" value={f[k]} onChange={set(k)} />{err[k] && <p className="err">{err[k]}</p>}</div>
        ))}
        <div><label htmlFor="message" className="label">Message</label><textarea id="message" rows="5" className="input" value={f.message} onChange={set('message')} />{err.message && <p className="err">{err.message}</p>}</div>
        <button className="btn-primary">Send message</button>
      </form>
    </div>
  );
}
