<?php

declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

$output = [];
$exitCode = 0;
exec('git ls-files', $output, $exitCode);
if ($exitCode !== 0) {
    fwrite(STDERR, "Unable to enumerate tracked files.\n");
    exit(1);
}

$required = [
    'README.md',
    'SECURITY.md',
    'CHANGELOG.md',
    '.env.example',
    '.github/workflows/ci.yml',
    'docs/ARCHITECTURE.md',
    'docs/SECURITY_MODEL.md',
    'docs/OPERATIONS.md',
    'docs/ENGINEERING_DECISIONS.md',
    'docs/EVIDENCE_CHECKLIST.md',
    'assets/system-architecture.svg',
    'voice-engine/.env.example',
    'telephony/.env.example',
    'realtime-server/.env.example',
];

$errors = [];
$tracked = array_fill_keys(array_map(static fn (string $path): string => str_replace('\\', '/', trim($path)), $output), true);

foreach ($required as $path) {
    if (! isset($tracked[$path])) {
        $errors[] = "Required public artifact is not tracked: {$path}";
    }
}

$forbiddenNames = [
    '.env',
    'database.sqlite',
    'id_rsa',
    'id_ed25519',
];
$forbiddenExtensions = ['.pem', '.p12', '.pfx', '.key', '.sqlite', '.db', '.wav', '.mp3', '.m4a', '.ogg', '.log'];
$forbiddenFragments = ['/storage/logs/', '/recordings/', '/transcripts/', '/backups/'];

foreach (array_keys($tracked) as $path) {
    $normalized = '/'.strtolower($path);
    $basename = strtolower(basename($path));

    if (in_array($basename, $forbiddenNames, true)) {
        $errors[] = "Forbidden tracked file: {$path}";
    }
    foreach ($forbiddenExtensions as $extension) {
        if (str_ends_with($normalized, $extension)) {
            $errors[] = "Forbidden tracked artifact: {$path}";
        }
    }
    foreach ($forbiddenFragments as $fragment) {
        if ($basename !== '.gitignore' && str_contains($normalized, $fragment)) {
            $errors[] = "Private runtime path is tracked: {$path}";
        }
    }
}

$secretPatterns = [
    '/\bsk-[A-Za-z0-9_-]{20,}\b/',
    '/\bgh[opusr]_[A-Za-z0-9]{20,}\b/',
    '/-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----/',
    '/\bAIza[0-9A-Za-z_-]{30,}\b/',
];
$textExtensions = ['php', 'ts', 'js', 'json', 'md', 'yml', 'yaml', 'xml', 'env', 'example', 'txt', 'css', 'scss', 'blade.php'];

foreach (array_keys($tracked) as $path) {
    $eligible = false;
    foreach ($textExtensions as $extension) {
        if (str_ends_with(strtolower($path), '.'.$extension)) {
            $eligible = true;
            break;
        }
    }
    if (! $eligible || ! is_file($path) || filesize($path) > 2_000_000) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        $errors[] = "Unable to inspect tracked file: {$path}";
        continue;
    }
    foreach ($secretPatterns as $pattern) {
        if (preg_match($pattern, $content) === 1) {
            $errors[] = "Possible live secret in {$path}";
        }
    }
}

foreach (['.env.example', 'voice-engine/.env.example', 'telephony/.env.example', 'realtime-server/.env.example'] as $example) {
    $content = file_get_contents($example);
    if ($content === false) {
        continue;
    }
    foreach (preg_split('/\R/', $content) ?: [] as $line) {
        if ($line === '' || str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
        if (preg_match('/(?:KEY|SECRET|TOKEN|PASSWORD|PASSKEY)$/i', trim($name)) === 1
            && trim($value, " \t\n\r\0\x0B\"'") !== ''
            && ! in_array(trim($value, " \t\n\r\0\x0B\"'"), ['local-key'], true)) {
            $errors[] = "Sensitive example variable must be blank: {$example}:{$name}";
        }
    }
}

if ($errors !== []) {
    foreach (array_unique($errors) as $error) {
        fwrite(STDERR, "ERROR: {$error}\n");
    }
    exit(1);
}

fwrite(STDOUT, "Public repository validation passed.\n");
