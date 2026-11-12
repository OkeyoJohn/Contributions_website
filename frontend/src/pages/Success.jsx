import { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { CheckCircle2, XCircle, Loader2 } from 'lucide-react';
import { getDonation } from '../services/donationService.js';

// Never trusts the URL: the status comes only from Laravel (which verifies with the provider).
export default function Success() {
  const [params] = useSearchParams();
  const ref = params.get('reference') || params.get('trxref');
  const [donation, setDonation] = useState(null);
  const [error, setError] = useState(false);

  useEffect(() => {
    if (!ref) return setError(true);
    let tries = 0, timer;
    const poll = async () => {
      try {
        const d = await getDonation(ref);
        setDonation(d);
        if (d.status === 'pending' && ++tries < 24) timer = setTimeout(poll, 5000);
      } catch { setError(true); }
    };
    poll();
    return () => clearTimeout(timer);
  }, [ref]);

  let body;
  if (error) body = <Box icon={<XCircle size={56} className="text-red-700" />} title="We could not find your donation" text="Check your reference or contact us if money left your account." />;
  else if (!donation) body = <Box icon={<Loader2 size={56} className="animate-spin text-leaf" />} title="Checking your payment" text="Please wait a moment." />;
  else if (donation.status === 'paid') body = <Box icon={<CheckCircle2 size={56} className="text-leaf" />} title="Thank you for your donation" text={`Your payment was confirmed. Reference: ${donation.reference}.`} />;
  else if (donation.status === 'failed') body = <Box icon={<XCircle size={56} className="text-red-700" />} title="Payment not completed" text="The payment was not confirmed. You have not been charged for this attempt." retry />;
  else body = <Box icon={<Loader2 size={56} className="animate-spin text-gold" />} title="Waiting for confirmation" text="Complete the prompt on your phone. This page updates automatically." />;
  return body;
}

export function Failed() {
  return <Box icon={<XCircle size={56} className="text-red-700" />} title="Payment not completed" text="Your payment was cancelled or failed. Please try again or choose another payment method." retry />;
}

function Box({ icon, title, text, retry }) {
  return (
    <div className="container-x max-w-xl py-24 text-center">
      <div className="card p-10">
        <div className="mb-4 flex justify-center">{icon}</div>
        <h1 className="text-3xl font-extrabold text-forest">{title}</h1>
        <p className="mt-3">{text}</p>
        <div className="mt-6 flex justify-center gap-3">
          {retry && <Link to="/donate" className="btn-primary">Try again</Link>}
          <Link to="/" className="btn-outline">Back to home</Link>
        </div>
      </div>
    </div>
  );
}
