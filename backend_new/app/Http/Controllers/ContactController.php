<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactEnquiryRequest;
use App\Mail\ContactEnquiryMail;
use App\Models\ContactEnquiry;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    public function store(StoreContactEnquiryRequest $request)
    {
        $data = $request->validated();

        // Saved first so an SMTP failure never loses the enquiry.
        $enquiry = ContactEnquiry::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);

        $to = config('hoperise.contact_email');

        if (! $to) {
            report(new \RuntimeException('CONTACT_EMAIL is not set in .env, so contact enquiries cannot be emailed.'));
            return response()->json([
                'message' => 'The contact inbox is not configured yet. Please try again later.',
            ], 500);
        }

        try {
            Mail::to($to)->send(new ContactEnquiryMail($enquiry));
            $enquiry->markEmailed();
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => "We saved your message but could not email it. Please write to {$to} directly.",
            ], 502);
        }

        return response()->json([
            'data' => ['message' => 'Thank you! Your message has been sent — we will reply by email soon.'],
        ], 201);
    }
}
