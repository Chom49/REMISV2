<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->clearStaleViteHotFile();
    }

    private function clearStaleViteHotFile(): void
    {
        $hot = public_path('hot');
        if (! file_exists($hot)) {
            return;
        }

        // Vite hot file exists — check if the dev server is actually running.
        // If not (e.g. after a reboot), delete the stale file so Laravel falls
        // back to the compiled build assets instead of trying to reach a dead server.
        $url  = trim(file_get_contents($hot));
        $host = parse_url($url, PHP_URL_HOST) ?? 'localhost';
        $port = parse_url($url, PHP_URL_PORT) ?? 5173;

        $connection = @fsockopen($host, $port, $errno, $errstr, 1);
        if ($connection) {
            fclose($connection);
        } else {
            unlink($hot);
        }
    }
}
