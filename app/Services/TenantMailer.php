<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class TenantMailer
{
    /**
     * Send mail using the tenant's configured SMTP credentials.
     *
     * Usage:
     *   $mailer->send($tenant, function($mail) {
     *       $mail->to('a@b.com')->subject('Hi')->html('<p>Hello</p>');
     *   });
     */
    public function send(Tenant $tenant, \Closure $callback): void
    {
        $this->configureTenantMailer($tenant);
        $mailer = Mail::mailer('tenant_smtp');
        $callback($mailer);
    }

    public function testConnection(Tenant $tenant): bool
    {
        $this->configureTenantMailer($tenant);

        try {
            $transport = Mail::mailer('tenant_smtp')->getSymfonyTransport();
            $transport->start();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function configureTenantMailer(Tenant $tenant): void
    {
        Config::set('mail.mailers.tenant_smtp', [
            'transport'  => 'smtp',
            'host'       => $tenant->getSetting('smtp_host', config('mail.host')),
            'port'       => (int) $tenant->getSetting('smtp_port', config('mail.port')),
            'encryption' => $tenant->getSetting('smtp_encryption', 'tls'),
            'username'   => $tenant->getSetting('smtp_username'),
            'password'   => $tenant->getSetting('smtp_password'),
        ]);

        Config::set('mail.from', [
            'address' => $tenant->getSetting('mail_from_address', $tenant->email ?? 'noreply@lab.com'),
            'name'    => $tenant->getSetting('mail_from_name', $tenant->name),
        ]);
    }
}
