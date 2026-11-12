import { Smartphone, CreditCard, Wallet } from 'lucide-react';

export const methods = [
  { id: 'mpesa', name: 'M-Pesa', icon: Smartphone, hint: 'Enter your Safaricom number. You will receive an STK Push prompt on your phone to enter your M-Pesa PIN.' },
  { id: 'paystack', name: 'Visa / Mastercard', icon: CreditCard, hint: 'You will be redirected to Paystack\u2019s secure checkout to enter your card details. HopeRise never sees your card number.' },
  { id: 'paypal', name: 'PayPal', icon: Wallet, hint: 'You will be sent to PayPal to approve the payment securely.' },
];

export default function PaymentMethods({ value, onChange }) {
  const active = methods.find((m) => m.id === value);
  return (
    <fieldset>
      <legend className="label">Payment method</legend>
      <div className="grid gap-3 sm:grid-cols-3">
        {methods.map(({ id, name, icon: Icon }) => (
          <label key={id} className={`flex cursor-pointer items-center gap-3 rounded-2xl border-2 p-4 transition ${value === id ? 'border-leaf bg-leaf/5' : 'border-forest/10 hover:border-forest/30'}`}>
            <input type="radio" name="payment_method" value={id} checked={value === id} onChange={() => onChange(id)} className="sr-only" />
            <Icon className="text-leaf" /><span className="font-bold text-forest">{name}</span>
          </label>
        ))}
      </div>
      {active && <p className="mt-3 rounded-xl bg-cream p-4 text-sm">{active.hint}</p>}
    </fieldset>
  );
}
