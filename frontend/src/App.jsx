import { Routes, Route, useLocation } from 'react-router-dom';
import { useEffect } from 'react';
import Navbar from './components/Navbar.jsx';
import Footer from './components/Footer.jsx';
import Home from './pages/Home.jsx';
import About from './pages/About.jsx';
import Campaigns from './pages/Campaigns.jsx';
import CampaignDetails from './pages/CampaignDetails.jsx';
import Donate from './pages/Donate.jsx';
import Success, { Failed } from './pages/Success.jsx';
import Contact from './pages/Contact.jsx';
import Privacy from './pages/Privacy.jsx';

export default function App() {
  const { pathname, hash } = useLocation();
  useEffect(() => {
    if (hash) {
      const target = document.getElementById(hash.slice(1));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
        return;
      }
    }
    window.scrollTo({ top: 0 });
  }, [pathname, hash]);
  return (
    <>
      <a href="#main" className="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-3">Skip to content</a>
      <Navbar />
      <main id="main">
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/about" element={<About />} />
          <Route path="/campaigns" element={<Campaigns />} />
          <Route path="/campaigns/:slug" element={<CampaignDetails />} />
          <Route path="/donate" element={<Donate />} />
          <Route path="/donation/success" element={<Success />} />
          <Route path="/donation/failed" element={<Failed />} />
          <Route path="/contact" element={<Contact />} />
          <Route path="/privacy" element={<Privacy />} />
          <Route path="*" element={<div className="container-x py-32 text-center"><h1 className="text-3xl font-extrabold">Page not found</h1></div>} />
        </Routes>
      </main>
      <Footer />
    </>
  );
}
