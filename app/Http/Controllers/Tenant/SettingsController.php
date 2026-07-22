<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\TenantContext;
use App\Services\TenantMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function __construct(
        private TenantContext $context,
        private TenantMailer $mailer
    ) {}

    public function index(string $lab_slug)
    {
        $tenant = $this->context->get();
        return view('tenant.settings.index', compact('tenant'));
    }

    public function customDomain(string $lab_slug)
    {
        $tenant = $this->context->get();
        return view('tenant.settings.domain', compact('tenant'));
    }

    /** Default theme values matching the stock design. */
    public const THEME_DEFAULTS = [
        'sidebar_type'      => 'solid',
        'sidebar_color1'    => '#ffffff',
        'sidebar_color2'    => '#eef2ff',
        'sidebar_direction' => 'to bottom',
        'sidebar_glass'     => false,
        'topbar_type'       => 'solid',
        'topbar_color1'     => '#ffffff',
        'topbar_color2'     => '#eef2ff',
        'topbar_direction'  => 'to right',
        'topbar_glass'      => false,
        'bg_type'           => 'solid',
        'bg_color1'         => '#eef1f7',
        'bg_color2'         => '#e0e7ff',
        'bg_direction'      => 'to bottom right',
        'sidebar_text'      => '#475569',
        'topbar_text'       => '#1e293b',
        'heading_text'      => '#1e293b',
        'body_text'         => '#64748b',
    ];

    public function appearance(string $lab_slug)
    {
        $tenant = $this->context->get();
        $theme  = array_merge(
            self::THEME_DEFAULTS,
            json_decode($tenant->getSetting('theme') ?? '[]', true) ?: []
        );
        return view('tenant.settings.appearance', compact('tenant', 'theme'));
    }

    public function updateAppearance(Request $request, string $lab_slug)
    {
        $hex = ['required', 'regex:/^#[0-9a-fA-F]{6}$/'];

        $data = $request->validate([
            'sidebar_type'      => 'required|in:solid,gradient',
            'sidebar_color1'    => $hex,
            'sidebar_color2'    => $hex,
            'sidebar_direction' => 'required|in:to bottom,to right,to bottom right',
            'topbar_type'       => 'required|in:solid,gradient',
            'topbar_color1'     => $hex,
            'topbar_color2'     => $hex,
            'topbar_direction'  => 'required|in:to bottom,to right,to bottom right',
            'bg_type'           => 'required|in:solid,gradient',
            'bg_color1'         => $hex,
            'bg_color2'         => $hex,
            'bg_direction'      => 'required|in:to bottom,to right,to bottom right',
            'sidebar_text'      => $hex,
            'topbar_text'       => $hex,
            'heading_text'      => $hex,
            'body_text'         => $hex,
        ]);

        $data['sidebar_glass'] = $request->boolean('sidebar_glass');
        $data['topbar_glass']  = $request->boolean('topbar_glass');

        $this->context->get()->setSetting('theme', json_encode($data));

        return back()->with('success', 'Appearance saved. The new colors apply to your staff panel, patient portal, and branch portal.');
    }

    public function resetAppearance(string $lab_slug)
    {
        $this->context->get()->settings()->where('key', 'theme')->delete();
        return back()->with('success', 'Appearance reset to the default look.');
    }

    public function updatePassword(Request $request, string $lab_slug)
    {
        $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password'         => 'required|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password'             => Hash::make($request->password),
            'recoverable_password' => $request->password,
        ]);

        return back()->with('success', 'Your password has been changed.');
    }

    public function updateSmtp(Request $request, string $lab_slug)
    {
        $request->validate([
            'smtp_host'         => 'required|string|max:191',
            'smtp_port'         => 'required|integer',
            'smtp_username'     => 'required|string|max:191',
            'smtp_password'     => 'required|string|max:191',
            'smtp_encryption'   => 'required|in:tls,ssl,none',
            'mail_from_address' => 'required|email|max:191',
            'mail_from_name'    => 'required|string|max:191',
        ]);

        $tenant = $this->context->get();

        foreach ($request->only(['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_encryption', 'mail_from_address', 'mail_from_name']) as $key => $value) {
            $tenant->setSetting($key, $value);
        }

        return back()->with('success', 'SMTP settings saved.');
    }

    public function testSmtp(Request $request, string $lab_slug)
    {
        $tenant = $this->context->get();

        try {
            $works = $this->mailer->testConnection($tenant);
            if ($works) {
                return back()->with('success', 'SMTP connection test successful!');
            }
            return back()->with('error', 'SMTP connection failed. Check your credentials.');
        } catch (\Throwable $e) {
            return back()->with('error', 'SMTP test failed: ' . $e->getMessage());
        }
    }

    public function templateBuilder(string $lab_slug)
    {
        $tenant = $this->context->get();

        $logoUrl = null;
        $signatureUrl = null;

        if ($p = $tenant->getSetting('report_logo')) {
            $abs = storage_path('app/public/' . $p);
            if (file_exists($abs)) $logoUrl = Storage::url($p);
        }
        if ($p = $tenant->getSetting('report_signature')) {
            $abs = storage_path('app/public/' . $p);
            if (file_exists($abs)) $signatureUrl = Storage::url($p);
        }

        $reportTemplate  = $tenant->getSetting('report_template');
        $invoiceTemplate = $tenant->getSetting('invoice_template');

        return view('tenant.settings.template-builder',
            compact('tenant', 'logoUrl', 'signatureUrl', 'reportTemplate', 'invoiceTemplate'));
    }

    public function saveTemplate(Request $request, string $lab_slug, string $type)
    {
        abort_unless(in_array($type, ['report', 'invoice']), 404);
        $request->validate(['template' => 'required|string|max:100000']);
        abort_if(json_decode($request->template) === null, 422);

        $this->context->get()->setSetting("{$type}_template", $request->template);

        return response()->json(['success' => true]);
    }

    public function updateBranding(Request $request, string $lab_slug)
    {
        $request->validate([
            'report_logo'        => 'nullable|image|max:2048',
            'report_signature'   => 'nullable|image|max:2048',
            'report_header_html' => 'nullable|string|max:5000',
            'report_footer_html' => 'nullable|string|max:5000',
        ]);

        $tenant = $this->context->get();

        if ($request->hasFile('report_logo')) {
            $path = $request->file('report_logo')->store("tenants/{$tenant->id}/branding", 'public');
            $tenant->setSetting('report_logo', $path);
        }

        if ($request->hasFile('report_signature')) {
            $path = $request->file('report_signature')->store("tenants/{$tenant->id}/branding", 'public');
            $tenant->setSetting('report_signature', $path);
        }

        if ($request->filled('report_header_html')) {
            $tenant->setSetting('report_header_html', $request->report_header_html);
        }

        if ($request->filled('report_footer_html')) {
            $tenant->setSetting('report_footer_html', $request->report_footer_html);
        }

        return back()->with('success', 'Branding settings saved.');
    }
}
