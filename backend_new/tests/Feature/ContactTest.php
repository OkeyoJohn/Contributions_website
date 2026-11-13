<?php

use App\Mail\ContactEnquiryMail;
use App\Models\ContactEnquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['hoperise.contact_email' => 'inbox@example.com']);
});

function validEnquiry(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jane Wanjiku',
        'email' => 'jane@example.com',
        'phone' => '0712 345 678',
        'subject' => 'Partnership',
        'message' => 'I would like to partner with HopeRise on the education campaign.',
    ], $overrides);
}

it('saves an enquiry and emails it to the contact inbox', function () {
    Mail::fake();

    $this->postJson('/api/contact', validEnquiry())
        ->assertCreated()
        ->assertJsonPath('data.message', fn ($m) => str_contains($m, 'Thank you'));

    $enquiry = ContactEnquiry::sole();
    expect($enquiry->email)->toBe('jane@example.com')
        ->and($enquiry->emailed_at)->not->toBeNull();

    Mail::assertSent(ContactEnquiryMail::class, function (ContactEnquiryMail $mail) use ($enquiry) {
        return $mail->hasTo('inbox@example.com')
            && $mail->hasReplyTo('jane@example.com')
            && $mail->enquiry->is($enquiry);
    });
});

it('renders the email body without errors', function () {
    $enquiry = ContactEnquiry::create([
        'name' => 'Jane Wanjiku',
        'email' => 'jane@example.com',
        'subject' => 'Partnership',
        'message' => "Line one\nLine two",
    ]);

    expect((new ContactEnquiryMail($enquiry))->render())
        ->toContain('Jane Wanjiku')
        ->toContain('Partnership')
        ->toContain('jane@example.com');
});

it('rejects an invalid submission without storing or emailing', function () {
    Mail::fake();

    $this->postJson('/api/contact', ['name' => 'J', 'email' => 'not-an-email', 'message' => 'short'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'message']);

    expect(ContactEnquiry::count())->toBe(0);
    Mail::assertNothingSent();
});

it('keeps the enquiry when email delivery fails', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    $this->postJson('/api/contact', validEnquiry())
        ->assertStatus(502);

    $enquiry = ContactEnquiry::sole();
    expect($enquiry->emailed_at)->toBeNull()
        ->and($enquiry->message)->toContain('partner');
});

it('fails clearly when no contact inbox is configured', function () {
    Mail::fake();
    config(['hoperise.contact_email' => null]);

    $this->postJson('/api/contact', validEnquiry())->assertStatus(500);

    Mail::assertNothingSent();
});
