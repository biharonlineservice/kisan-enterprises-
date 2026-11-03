<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMTP Mailer Test - Kisan Enterprises</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #15803d 0%, #166534 100%); padding: 28px 32px; text-align: left;">
                            <table role="presentation" width="100%">
                                <tr>
                                    <td>
                                        <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.02em;">
                                            🌾 Kisan Enterprises
                                        </h1>
                                        <p style="margin: 6px 0 0 0; color: #bbf7d0; font-size: 13px; font-weight: 500;">
                                            Agricultural Crop Trading &amp; Grain Procurement ERP
                                        </p>
                                    </td>
                                    <td align="right">
                                        <span style="background-color: rgba(255, 255, 255, 0.2); color: #ffffff; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                                            SMTP Verified
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px;">
                            <!-- Success Banner -->
                            <div style="background-color: #ecfdf5; border-left: 4px solid #10b981; padding: 14px 18px; border-radius: 6px; margin-bottom: 24px;">
                                <table role="presentation" width="100%">
                                    <tr>
                                        <td width="30" valign="top" style="font-size: 20px; line-height: 1;">
                                            ✅
                                        </td>
                                        <td style="font-size: 14px; color: #065f46; font-weight: 600; line-height: 1.5;">
                                            SMTP Configuration Test Successful!
                                            <div style="font-weight: 400; font-size: 13px; color: #047857; margin-top: 2px;">
                                                Your outbound mail transport is configured properly and able to deliver emails.
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 20px 0;">
                                Hello, this is an automated verification email dispatched from the <strong>Kisan Enterprises ERP</strong> Business Settings panel to confirm that your SMTP mail settings are functional.
                            </p>

                            <!-- Configuration Details Table -->
                            <table role="presentation" width="100%" style="border-collapse: collapse; margin-bottom: 24px; font-size: 13px;">
                                <thead>
                                    <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                        <th align="left" style="padding: 10px 14px; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em;">Parameter</th>
                                        <th align="left" style="padding: 10px 14px; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em;">Configured Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 10px 14px; color: #64748b; font-weight: 500;">Mail Driver</td>
                                        <td style="padding: 10px 14px; color: #0f172a; font-family: monospace; font-weight: 600;">
                                            {{ strtoupper($details['mailer'] ?? 'SMTP') }}
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 10px 14px; color: #64748b; font-weight: 500;">SMTP Host</td>
                                        <td style="padding: 10px 14px; color: #0f172a; font-family: monospace;">
                                            {{ $details['host'] ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 10px 14px; color: #64748b; font-weight: 500;">SMTP Port &amp; Encryption</td>
                                        <td style="padding: 10px 14px; color: #0f172a; font-family: monospace;">
                                            {{ $details['port'] ?? 465 }} ({{ strtoupper($details['encryption'] ?? 'SSL') }})
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 10px 14px; color: #64748b; font-weight: 500;">From Address</td>
                                        <td style="padding: 10px 14px; color: #0f172a; font-family: monospace;">
                                            {{ $details['from_name'] ?? 'Kisan Enterprises' }} &lt;{{ $details['from_address'] ?? 'noreply' }}&gt;
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 10px 14px; color: #64748b; font-weight: 500;">Dispatched At</td>
                                        <td style="padding: 10px 14px; color: #0f172a;">
                                            {{ $details['sent_at'] ?? now()->toDayDateTimeString() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px 14px; color: #64748b; font-weight: 500;">Dispatched By</td>
                                        <td style="padding: 10px 14px; color: #0f172a;">
                                            {{ $details['sent_by'] ?? 'System Administrator' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div style="background-color: #f8fafc; border-radius: 8px; padding: 14px; border: 1px solid #e2e8f0; font-size: 12px; color: #64748b; line-height: 1.5;">
                                💡 <strong>Tip for Hostinger / Custom Domains:</strong> Ensure your SPF (TXT) and DKIM DNS records are properly configured in your domain's DNS zone to prevent messages from landing in the spam folder.
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; text-align: center; font-size: 12px; color: #94a3b8;">
                            &copy; {{ date('Y') }} Kisan Enterprises. All rights reserved. Automated system notification.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

