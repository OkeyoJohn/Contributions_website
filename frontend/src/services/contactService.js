import api, { unwrap } from './api.js';

/**
 * POST /contact
 * payload: { name, email, phone?, subject?, message }
 *
 * Laravel saves the enquiry and emails it to CONTACT_EMAIL.
 * Responds 201 { message } on success, 422 { errors } when validation fails.
 */
export const sendEnquiry = async (payload) => unwrap(await api.post('/contact', payload));
