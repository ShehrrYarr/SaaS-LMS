<?php

namespace App\Jobs;

use App\Models\Patient;
use App\Models\Tenant;
use App\Models\TestOrder;
use App\Services\TenantMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReportReadyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private TestOrder $order,
        private Tenant $tenant
    ) {}

    public function handle(): void
    {
        $patient = $this->order->patient;

        if (!$patient || !$patient->email) {
            return;
        }

        $mailer = app(TenantMailer::class);
        $mailer->send($this->tenant, function ($mail) use ($patient) {
            $mail->to($patient->email, $patient->name)
                 ->subject("Your Test Report is Ready — {$this->tenant->name}")
                 ->html($this->buildHtml($patient));
        });
    }

    private function buildHtml(Patient $patient): string
    {
        $loginUrl = url("/{$this->tenant->slug}/portal/login");
        $labName  = e($this->tenant->name);
        $name     = e($patient->name);
        $orderId  = str_pad($this->order->id, 6, '0', STR_PAD_LEFT);

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <body style="font-family:sans-serif;background:#f5f5f5;padding:40px 0;">
        <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);">
            <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:32px;text-align:center;">
                <h2 style="color:#fff;margin:0;">{$labName}</h2>
                <p style="color:rgba(255,255,255,.8);margin:8px 0 0;font-size:14px;">Report Ready Notification</p>
            </div>
            <div style="padding:32px;">
                <p style="color:#374151;font-size:15px;margin-top:0;">Hello <strong>{$name}</strong>,</p>
                <p style="color:#6B7280;font-size:14px;">Your test results for Order <strong>#{$orderId}</strong> are now available. You can view and download your report from the patient portal.</p>
                <a href="{$loginUrl}" style="display:inline-block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;font-weight:600;margin-top:8px;">View My Report</a>
                <p style="color:#9CA3AF;font-size:12px;margin-top:24px;">If you have any questions about your results, please contact {$labName} directly.</p>
            </div>
        </div>
        </body>
        </html>
        HTML;
    }
}
