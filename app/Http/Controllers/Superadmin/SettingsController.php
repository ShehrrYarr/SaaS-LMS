<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Process\Process;

class SettingsController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('superadmin')->user();
        return view('superadmin.settings', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('superadmin')->user();

        $data = $request->validate([
            'name'  => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:superadmins,email,' . $admin->id,
        ]);

        $admin->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        Auth::guard('superadmin')->user()->update([
            'password'             => Hash::make($request->password),
            'recoverable_password' => $request->password,
        ]);

        return back()->with('success', 'Your password has been changed.');
    }

    public function runMigrate()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());
        } catch (\Throwable $e) {
            return back()->with('deploy_output', $e->getMessage())
                         ->with('error', 'Migration failed.');
        }

        return back()->with('deploy_output', $output ?: 'Nothing to migrate.')
                     ->with('success', 'Migrations completed.');
    }

    public function runGitPull()
    {
        $process = Process::fromShellCommandline('git pull 2>&1', base_path(), null, null, 120);
        $process->run();

        $output = trim($process->getOutput()) ?: '(no output)';

        if (! $process->isSuccessful()) {
            return back()->with('deploy_output', $output)
                         ->with('error', 'Git pull failed.');
        }

        return back()->with('deploy_output', $output)
                     ->with('success', 'Git pull completed.');
    }

    public function runOptimize()
    {
        // Must run in a separate process: config:cache inside this request
        // would swap the app container and break the session flash.
        $cmd = 'php artisan optimize:clear --no-ansi 2>&1'
             . ' && php artisan config:cache --no-ansi 2>&1'
             . ' && php artisan route:cache --no-ansi 2>&1'
             . ' && php artisan view:cache --no-ansi 2>&1';

        $process = Process::fromShellCommandline($cmd, base_path(), null, null, 300);
        $process->run();

        $output = trim($process->getOutput()) ?: '(no output)';

        if (! $process->isSuccessful()) {
            return back()->with('deploy_output', $output)
                         ->with('error', 'Optimize failed.');
        }

        return back()->with('deploy_output', $output)
                     ->with('success', 'Caches cleared and rebuilt.');
    }
}
