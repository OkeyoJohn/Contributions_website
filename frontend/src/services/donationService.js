import api, { unwrap } from './api.js';

/**
 * POST /donations
 * payload: { name, email, phone, amount, currency, campaign_id, anonymous, message, payment_method }
 * payment_method: 'mpesa' | 'paystack' | 'paypal'
 *
 * Expected Laravel response:
 *  mpesa    -> { reference }                 (backend triggers the STK Push)
 *  paystack -> { reference, redirect_url }   (Paystack-hosted checkout)
 *  paypal   -> { reference, redirect_url }   (PayPal approval URL)
 */
export const createDonation = async (payload) => unwrap(await api.post('/donations', payload));

// Status is set by Laravel after it verifies with the provider: pending | paid | failed
export const getDonation = async (reference) => unwrap(await api.get(`/donations/${reference}`));
