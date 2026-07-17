<?php

namespace App\Jobs;

use App\Models\Patient;
use App\Models\Tenant;
use App\Services\TenantMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPatientCredentialsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Patient $patient,
        private Tenant $tenant,
        private string $plainPassword
    ) {}

    public function handle(): void
    {
        $mailer = app(TenantMailer::class);
        $mailer->send($this->tenant, function ($mail) {
            $mail->to($this->patient->email, $this->patient->name)
                 ->subject("Welcome to {$this->tenant->name} — Your Patient Portal Access")
                 ->html($this->buildHtml());
        });
    }

    private function buildHtml(): string
    {
        $loginUrl  = url("/{$this->tenant->slug}/portal/login");
        $labName   = e($this->tenant->name);
        $name      = e($this->patient->name);
        $email     = e($this->patient->email);
        $password  = e($this->plainPassword);
        $code      = e($this->patient->patient_code);

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <body style="font-family:sans-serif;background:#f5f5f5;padding:40px 0;">
        <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);">
            <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:32px;text-align:center;">
                <h2 style="color:#fff;margin:0;font-size:22px;">{$labName}</h2>
                <p style="color:rgba(255,255,255,.8);margin:8px 0 0;font-size:14px;">Patient Portal Access</p>
            </div>
            <div style="padding:32px;">
                <p style="color:#374151;font-size:15px;margin-top:0;">Hello <strong>{$name}</strong>,</p>
                <p style="color:#6B7280;font-size:14px;">Your patient account has been created at <strong>{$labName}</strong>. Use the credentials below to access your test reports and invoices online.</p>
                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin:24px 0;">
                    <table style="width:100%;font-size:14px;color:#374151;">
                        <tr><td style="padding:4px 0;color:#9CA3AF;width:120px;">Patient ID</td><td><strong>{$code}</strong></td></tr>
                        <tr><td style="padding:4px 0;color:#9CA3AF;">Email</td><td><strong>{$email}</strong></td></tr>
                        <tr><td style="padding:4px 0;color:#9CA3AF;">Password</td><td><strong>{$password}</strong></td></tr>
                    </table>
                </div>
                <a href="{$loginUrl}" style="display:inline-block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;font-weight:600;">Login to Patient Portal</a>
                <p style="color:#9CA3AF;font-size:12px;margin-top:24px;">Please change your password after your first login. If you did not expect this email, please contact us immediately.</p>
            </div>
        </div>
        </body>
        </html>
        HTML;
    }
}
