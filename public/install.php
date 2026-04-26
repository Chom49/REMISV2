<?php
/**
 * REMIS V2 — Browser Setup Wizard
 * Access via: http://localhost/RemisV2/install.php
 */

define('ROOT', dirname(__DIR__));
define('STEP', $_POST['step'] ?? $_GET['step'] ?? 'check');

// ── Helpers ──────────────────────────────────────────────────────────────────

function phpBin(): string {
    return PHP_BINARY;
}

function composerCmd(): string {
    $phar = ROOT . '/composer.phar';
    if (file_exists($phar)) {
        return escapeshellarg(phpBin()) . ' ' . escapeshellarg($phar);
    }
    return (PHP_OS_FAMILY === 'Windows') ? 'composer' : 'composer';
}

function run(string $cmd): array {
    chdir(ROOT);
    exec($cmd . ' 2>&1', $output, $code);
    return ['output' => implode("\n", $output), 'code' => $code];
}

function artisan(string $args): array {
    return run(escapeshellarg(phpBin()) . ' artisan ' . $args);
}

function envValue(string $key, string $default = ''): string {
    $envFile = ROOT . '/.env';
    if (!file_exists($envFile)) return $default;
    foreach (file($envFile) as $line) {
        if (str_starts_with(trim($line), $key . '=')) {
            return trim(explode('=', $line, 2)[1] ?? $default);
        }
    }
    return $default;
}

function isInstalled(): bool {
    return file_exists(ROOT . '/.env')
        && envValue('APP_KEY') !== ''
        && file_exists(ROOT . '/vendor/autoload.php');
}

// ── Actions ──────────────────────────────────────────────────────────────────

$result   = null;
$error    = null;
$redirect = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (STEP === 'env') {
        // Build .env from form input
        $dbName = trim($_POST['db_name'] ?? 'remis_db');
        $dbUser = trim($_POST['db_user'] ?? 'root');
        $dbPass = trim($_POST['db_pass'] ?? '');
        $appUrl = rtrim(trim($_POST['app_url'] ?? 'http://localhost/RemisV2'), '/');

        $env = file_get_contents(ROOT . '/.env.example');
        $env = preg_replace('/^DB_DATABASE=.*/m',  "DB_DATABASE={$dbName}",  $env);
        $env = preg_replace('/^DB_USERNAME=.*/m',  "DB_USERNAME={$dbUser}",  $env);
        $env = preg_replace('/^DB_PASSWORD=.*/m',  "DB_PASSWORD={$dbPass}",  $env);
        $env = preg_replace('/^APP_URL=.*/m',       "APP_URL={$appUrl}",      $env);

        if (!file_put_contents(ROOT . '/.env', $env)) {
            $error = 'Cannot write .env file. Check folder permissions.';
        } else {
            $redirect = '?step=dependencies';
        }
    }

    if (STEP === 'dependencies') {
        if (!file_exists(ROOT . '/vendor/autoload.php')) {
            $r = run(composerCmd() . ' install --no-interaction --optimize-autoloader 2>&1');
            if ($r['code'] !== 0) {
                $error = "Composer install failed:\n" . $r['output'];
            } else {
                $redirect = '?step=database';
            }
        } else {
            $redirect = '?step=database';
        }
    }

    if (STEP === 'database') {
        $key  = artisan('key:generate --force');
        $mig  = artisan('migrate --force');
        $link = artisan('storage:link --force');
        if ($mig['code'] !== 0) {
            $error = "Migration failed:\n" . $mig['output'];
        } else {
            $redirect = '?step=done';
        }
    }
}

if ($redirect) {
    header("Location: install.php{$redirect}");
    exit;
}

$step = $_GET['step'] ?? (STEP === 'check' ? 'check' : STEP);

// ── Checks ───────────────────────────────────────────────────────────────────
$checks = [
    'PHP ≥ 8.2'           => version_compare(PHP_VERSION, '8.2.0', '>='),
    'PDO extension'        => extension_loaded('pdo'),
    'pdo_mysql extension'  => extension_loaded('pdo_mysql'),
    'mbstring extension'   => extension_loaded('mbstring'),
    'openssl extension'    => extension_loaded('openssl'),
    'tokenizer extension'  => extension_loaded('tokenizer'),
    'fileinfo extension'   => extension_loaded('fileinfo'),
    'storage/ writable'    => is_writable(ROOT . '/storage'),
    'bootstrap/ writable'  => is_writable(ROOT . '/bootstrap/cache'),
];
$allPass = !in_array(false, $checks, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>REMIS — Setup Wizard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-lg">

  <!-- Header -->
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4">
      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">REMIS V2 Setup</h1>
    <p class="text-gray-500 text-sm mt-1">Rental Management Information System</p>
  </div>

  <!-- Steps indicator -->
  <?php
  $steps   = ['check' => 'Requirements', 'env' => 'Database', 'dependencies' => 'Install', 'database' => 'Migrate', 'done' => 'Done'];
  $stepKeys = array_keys($steps);
  $current  = array_search($step, $stepKeys);
  ?>
  <div class="flex items-center justify-between mb-6">
    <?php foreach ($steps as $k => $label): ?>
      <?php $idx = array_search($k, $stepKeys); ?>
      <div class="flex flex-col items-center flex-1">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
          <?= $idx < $current ? 'bg-blue-600 text-white' : ($idx === $current ? 'bg-blue-600 text-white ring-4 ring-blue-100' : 'bg-gray-200 text-gray-500') ?>">
          <?= $idx < $current ? '✓' : ($idx + 1) ?>
        </div>
        <span class="text-xs mt-1 <?= $idx === $current ? 'text-blue-600 font-medium' : 'text-gray-400' ?>"><?= $label ?></span>
      </div>
      <?php if ($idx < count($steps) - 1): ?>
        <div class="flex-1 h-px <?= $idx < $current ? 'bg-blue-600' : 'bg-gray-200' ?> mb-4 mx-1"></div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

  <?php if ($error): ?>
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
      <strong>Error:</strong><br>
      <pre class="mt-1 text-xs overflow-auto whitespace-pre-wrap"><?= htmlspecialchars($error) ?></pre>
    </div>
  <?php endif; ?>

  <!-- ── STEP: check ── -->
  <?php if ($step === 'check'): ?>
    <h2 class="text-lg font-semibold text-gray-900 mb-4">System Requirements</h2>
    <div class="space-y-2 mb-6">
      <?php foreach ($checks as $label => $pass): ?>
        <div class="flex items-center justify-between py-2 border-b border-gray-50">
          <span class="text-sm text-gray-700"><?= $label ?></span>
          <?php if ($pass): ?>
            <span class="text-green-600 text-sm font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              Pass
            </span>
          <?php else: ?>
            <span class="text-red-600 text-sm font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
              Fail
            </span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($allPass): ?>
      <a href="?step=env"
        class="w-full block text-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition">
        Continue →
      </a>
    <?php else: ?>
      <div class="text-sm text-red-600 bg-red-50 rounded-lg p-3">
        Some requirements are not met. Please fix the issues above and
        <a href="install.php" class="underline">try again</a>.
      </div>
    <?php endif; ?>

  <!-- ── STEP: env ── -->
  <?php elseif ($step === 'env'): ?>
    <h2 class="text-lg font-semibold text-gray-900 mb-1">Database Configuration</h2>
    <p class="text-sm text-gray-500 mb-4">Enter your XAMPP MySQL details. Leave password blank if you haven't set one.</p>
    <form method="POST" action="install.php?step=env">
      <input type="hidden" name="step" value="env">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Application URL</label>
          <input type="text" name="app_url"
            value="<?= htmlspecialchars('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/RemisV2') ?>"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
          <input type="text" name="db_name" value="remis_db" required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-xs text-gray-400 mt-1">Create this database in phpMyAdmin first if it doesn't exist.</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Database Username</label>
          <input type="text" name="db_user" value="root"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Database Password</label>
          <input type="text" name="db_pass" value="" placeholder="Leave blank for no password"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
      </div>
      <button type="submit"
        class="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition">
        Save & Continue →
      </button>
    </form>

  <!-- ── STEP: dependencies ── -->
  <?php elseif ($step === 'dependencies'): ?>
    <h2 class="text-lg font-semibold text-gray-900 mb-1">Installing Dependencies</h2>
    <p class="text-sm text-gray-500 mb-4">
      <?php if (file_exists(ROOT . '/vendor/autoload.php')): ?>
        Dependencies are already installed.
      <?php else: ?>
        Running <code class="bg-gray-100 px-1 rounded">composer install</code>. This may take a minute…
      <?php endif; ?>
    </p>
    <form method="POST" action="install.php?step=dependencies" id="depForm">
      <input type="hidden" name="step" value="dependencies">
      <?php if (!file_exists(ROOT . '/vendor/autoload.php')): ?>
        <div class="bg-gray-900 text-green-400 rounded-lg p-3 text-xs font-mono mb-4 h-20 flex items-center justify-center" id="spinner">
          <span class="animate-pulse">⏳ Installing packages…</span>
        </div>
      <?php endif; ?>
      <button type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition">
        <?= file_exists(ROOT . '/vendor/autoload.php') ? 'Continue →' : 'Run Install →' ?>
      </button>
    </form>

  <!-- ── STEP: database ── -->
  <?php elseif ($step === 'database'): ?>
    <h2 class="text-lg font-semibold text-gray-900 mb-1">Database Setup</h2>
    <p class="text-sm text-gray-500 mb-4">
      This will create all the required tables in your database. Make sure the database
      <strong><?= htmlspecialchars(envValue('DB_DATABASE', 'remis_db')) ?></strong> exists in phpMyAdmin.
    </p>
    <form method="POST" action="install.php?step=database">
      <input type="hidden" name="step" value="database">
      <button type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition">
        Run Migrations →
      </button>
    </form>

  <!-- ── STEP: done ── -->
  <?php elseif ($step === 'done'): ?>
    <div class="text-center py-4">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
      </div>
      <h2 class="text-xl font-bold text-gray-900 mb-2">Setup Complete!</h2>
      <p class="text-gray-500 text-sm mb-6">REMIS V2 is ready to use.</p>
      <a href="../"
        class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium px-8 py-2.5 rounded-lg transition">
        Open REMIS →
      </a>
    </div>
  <?php endif; ?>

  </div>

  <p class="text-center text-xs text-gray-400 mt-4">
    REMIS V2 · Rental Management Information System
  </p>
</div>
</body>
</html>
