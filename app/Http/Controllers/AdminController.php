<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // ─────────────────────── DASHBOARD ───────────────────────────

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'landlords'   => User::where('role', 'landlord')->count(),
            'tenants'     => User::where('role', 'tenant')->count(),
        ];

        $recentLogs = AuditLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentLogs'));
    }

    // ─────────────────────── SETTINGS ────────────────────────────

    public function settingsIndex()
    {
        $settings = SystemSetting::orderBy('id')->get();
        return view('admin.settings.index', compact('settings'));
    }

    public function settingsUpdate(Request $request)
    {
        $data = $request->input('settings', []);

        foreach ($data as $key => $value) {
            SystemSetting::where('key', $key)->update(['value' => $value ?? '']);
        }

        // Handle unchecked booleans (checkboxes not submitted when unchecked)
        SystemSetting::where('type', 'boolean')->each(function ($setting) use ($data) {
            if (! array_key_exists($setting->key, $data)) {
                $setting->update(['value' => '0']);
            }
        });

        AuditLog::log('settings.update', 'System settings updated by admin.');

        return back()->with('success', 'Settings saved successfully.');
    }

    // ─────────────────────── AUDIT LOGS ──────────────────────────

    public function auditLogsIndex(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs    = $query->paginate(30)->withQueryString();
        $actions = AuditLog::distinct()->orderBy('action')->pluck('action');
        $users   = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.audit-logs.index', compact('logs', 'actions', 'users'));
    }

    public function auditLogsClear()
    {
        $count = AuditLog::whereDate('created_at', '<', now()->subDays(90))->count();
        AuditLog::whereDate('created_at', '<', now()->subDays(90))->delete();
        AuditLog::log('audit.clear', "Cleared {$count} audit log entries older than 90 days.");

        return back()->with('success', "{$count} old log entries removed.");
    }

    // ─────────────────────── BACKUPS ─────────────────────────────

    public function backupsIndex()
    {
        $backups = Backup::with('creator')->latest()->paginate(20);
        return view('admin.backups.index', compact('backups'));
    }

    public function backupsCreate()
    {
        $backupDir = storage_path('app/backups');
        @mkdir($backupDir, 0755, true);

        $filename = 'backup-' . now()->format('Y-m-d-His') . '.sql';
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $backup = Backup::create([
            'filename'   => $filename,
            'status'     => 'pending',
            'created_by' => Auth::id(),
        ]);

        $cfg  = config('database.connections.mysql');
        $host = $cfg['host'];
        $port = $cfg['port'] ?? 3306;
        $db   = $cfg['database'];
        $user = $cfg['username'];
        $pass = $cfg['password'];

        $cmd = ['mysqldump', "--host={$host}", "--port={$port}", "--user={$user}"];
        if ($pass) $cmd[] = "--password={$pass}";
        $cmd[] = $db;

        $result = Process::run($cmd);

        if ($result->successful() && strlen($result->output()) > 100) {
            file_put_contents($filePath, $result->output());
            $backup->update([
                'status' => 'completed',
                'size'   => filesize($filePath),
            ]);
            AuditLog::log('backup.create', "Database backup created: {$filename}");
            return back()->with('success', "Backup created: {$filename}");
        }

        $backup->update([
            'status' => 'failed',
            'notes'  => $result->errorOutput() ?: 'mysqldump not available or returned empty output.',
        ]);
        AuditLog::log('backup.failed', "Database backup failed: {$filename}");

        return back()->with('error', 'Backup failed. Check that mysqldump is in your system PATH.');
    }

    public function backupsDownload(Backup $backup)
    {
        $filePath = storage_path('app/backups/' . $backup->filename);

        if (! file_exists($filePath)) {
            return back()->with('error', 'Backup file not found on disk.');
        }

        AuditLog::log('backup.download', "Backup downloaded: {$backup->filename}");

        return response()->download($filePath, $backup->filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function backupsDestroy(Backup $backup)
    {
        $filePath = storage_path('app/backups/' . $backup->filename);
        if (file_exists($filePath)) @unlink($filePath);

        AuditLog::log('backup.delete', "Backup deleted: {$backup->filename}");
        $backup->delete();

        return back()->with('success', 'Backup deleted.');
    }

    // ─────────────────────── USERS ───────────────────────────────

    public function usersIndex(Request $request)
    {
        $query = User::query()->orderBy('name');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->paginate(25)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function usersUpdateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:landlord,tenant,admin']);

        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return back()->with('error', 'You cannot change your own admin role.');
        }

        $old = $user->role;
        $user->update(['role' => $request->role]);

        AuditLog::log('user.role_change', "Changed role of user #{$user->id} ({$user->email}) from {$old} to {$request->role}.", User::class, $user->id);

        return back()->with('success', "Role updated to {$request->role} for {$user->name}.");
    }

    public function usersDestroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        AuditLog::log('user.delete', "Deleted user #{$user->id} ({$user->email}).", User::class, $user->id);
        $user->delete();

        return back()->with('success', 'User account deleted.');
    }
}
