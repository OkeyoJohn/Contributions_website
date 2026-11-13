{{-- Email-safe markup: tables and inline styles only, no external CSS. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Website enquiry</title>
</head>
<body style="margin:0;padding:24px;background:#f4f6f5;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#12271e;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;">
        <tr>
            <td style="background:#0f3d2e;padding:20px 28px;color:#ffffff;font-size:18px;font-weight:700;">
                New enquiry from the HopeRise website
            </td>
        </tr>
        <tr>
            <td style="padding:28px;">
                <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#3d4f47;">
                    Someone submitted the contact form. Reply to this email to answer them directly.
                </p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;line-height:1.6;">
                    <tr>
                        <td style="padding:8px 0;color:#6b7d75;width:110px;vertical-align:top;">Name</td>
                        <td style="padding:8px 0;font-weight:600;">{{ $enquiry->name }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0;color:#6b7d75;vertical-align:top;">Email</td>
                        <td style="padding:8px 0;"><a href="mailto:{{ $enquiry->email }}" style="color:#1f7a53;">{{ $enquiry->email }}</a></td>
                    </tr>
                    @if ($enquiry->phone)
                    <tr>
                        <td style="padding:8px 0;color:#6b7d75;vertical-align:top;">Phone</td>
                        <td style="padding:8px 0;">{{ $enquiry->phone }}</td>
                    </tr>
                    @endif
                    @if ($enquiry->subject)
                    <tr>
                        <td style="padding:8px 0;color:#6b7d75;vertical-align:top;">Subject</td>
                        <td style="padding:8px 0;">{{ $enquiry->subject }}</td>
                    </tr>
                    @endif
                </table>

                <div style="margin-top:20px;padding:18px 20px;background:#f4f6f5;border-left:4px solid #269b65;border-radius:8px;">
                    <p style="margin:0 0 6px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#6b7d75;">Message</p>
                    <p style="margin:0;font-size:15px;line-height:1.7;white-space:pre-line;">{{ $enquiry->message }}</p>
                </div>

                <p style="margin:24px 0 0;font-size:13px;color:#8b9a93;">
                    Sent {{ $enquiry->created_at->format('D, d M Y \a\t H:i') }} ({{ config('app.timezone') }})
                    @if ($enquiry->ip) &middot; IP {{ $enquiry->ip }} @endif
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
