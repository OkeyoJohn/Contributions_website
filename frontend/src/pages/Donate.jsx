import DonationForm from '../components/DonationForm.jsx';

export default function Donate() {
  return (
    <div className="container-x max-w-3xl py-16">
      <h1 className="text-5xl font-extrabold text-forest">Make a donation</h1>
      <p className="mb-8 mt-3">Your gift goes directly to the campaign you choose.</p>
      <DonationForm />
    </div>
  );
}
