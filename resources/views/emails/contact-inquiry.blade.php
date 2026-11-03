<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Website Inquiry</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #15803d 0%, #166534 100%); padding: 24px 30px; text-align: left;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 20px; font-weight: 700;">
                                🌾 Kisan Enterprises ERP
                            </h1>
                            <p style="margin: 4px 0 0 0; color: #bbf7d0; font-size: 13px;">
                                New Lead &bull; Website Inquiry Notification
                            </p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <div style="margin-bottom: 20px;">
                                <span style="background-color: #dcfce7; color: #166534; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 12px; text-transform: uppercase;">
                                    {{ strtoupper(str_replace('_', ' ', $inquiry->inquiry_type)) }}
                                </span>
                            </div>

                            <h2 style="margin: 0 0 16px 0; font-size: 18px; color: #0f172a; font-weight: 700;">
                                {{ $inquiry->subject ?: 'New Message from Public Website' }}
                            </h2>

                            <!-- Contact Summary -->
                            <table role="presentation" width="100%" style="border-collapse: collapse; margin-bottom: 24px; background-color: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px;">
                                <tr>
                                    <td style="padding: 10px 16px; color: #64748b; font-weight: 600; width: 120px;">Name</td>
                                    <td style="padding: 10px 16px; color: #0f172a; font-weight: 600;">{{ $inquiry->name }}</td>
                                </tr>
                                <tr style="border-top: 1px solid #e2e8f0;">
                                    <td style="padding: 10px 16px; color: #64748b; font-weight: 600;">Phone / Mobile</td>
                                    <td style="padding: 10px 16px; color: #0f172a;">
                                        <a href="tel:{{ $inquiry->phone }}" style="color: #15803d; text-decoration: none; font-weight: 600;">{{ $inquiry->phone }}</a>
                                    </td>
                                </tr>
                                <tr style="border-top: 1px solid #e2e8f0;">
                                    <td style="padding: 10px 16px; color: #64748b; font-weight: 600;">Email Address</td>
                                    <td style="padding: 10px 16px; color: #0f172a;">
                                        @if($inquiry->email)
                                            <a href="mailto:{{ $inquiry->email }}" style="color: #15803d; text-decoration: none;">{{ $inquiry->email }}</a>
                                        @else
                                            <span style="color: #94a3b8; font-style: italic;">Not provided</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr style="border-top: 1px solid #e2e8f0;">
                                    <td style="padding: 10px 16px; color: #64748b; font-weight: 600;">Submitted At</td>
                                    <td style="padding: 10px 16px; color: #0f172a;">{{ $inquiry->created_at?->format('d M Y, h:i A') ?? now()->format('d M Y, h:i A') }}</td>
                                </tr>
                            </table>

                            <!-- Message Content -->
                            <div style="margin-bottom: 24px;">
                                <div style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px;">
                                    Message / Requirement:
                                </div>
                                <div style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px; font-size: 14px; line-height: 1.6; color: #334155; white-space: pre-wrap;">{{ $inquiry->message }}</div>
                            </div>

                            <p style="font-size: 12px; color: #64748b; margin: 0;">
                                You can manage and mark this inquiry as resolved from the ERP Admin Dashboard &rarr; Website CMS &rarr; Inquiries.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 30px; text-align: center; font-size: 12px; color: #94a3b8;">
                            &copy; {{ date('Y') }} Kisan Enterprises &bull; Automated notification from website inquiry portal
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

