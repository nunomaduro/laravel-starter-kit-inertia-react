<?php

declare(strict_types=1);

/**
 * Checks whether inline package overrides in composer.json can be removed
 * because the original package now supports the target Laravel version.
 *
 * Usage: php scripts/check-inline-overrides.php
 *        composer check:overrides
 */
$targetFramework = '13.0.0';
$composerJson = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);

// Collect all inline package overrides (type: package)
$overrides = [];
foreach ($composerJson['repositories'] ?? [] as $repo) {
    if (($repo['type'] ?? '') === 'package' && isset($repo['package']['name'])) {
        $overrides[] = [
            'name' => $repo['package']['name'],
            'version' => $repo['package']['version'],
        ];
    }
}

if (empty($overrides)) {
    echo "No inline package overrides found.\n";
    exit(0);
}

echo 'Checking '.count($overrides)." inline override(s) against Packagist...\n\n";

$canRemove = [];
$stillNeeded = [];
$errors = [];

foreach ($overrides as $pkg) {
    $name = $pkg['name'];
    $url = "https://repo.packagist.org/p2/{$name}.json";

    $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
    $json = @file_get_contents($url, false, $ctx);

    if ($json === false) {
        $errors[] = $name;

        continue;
    }

    $data = json_decode($json, true);
    $versions = $data['packages'][$name] ?? [];

    $latestSupporting = null;

    foreach ($versions as $v) {
        $versionStr = $v['version'] ?? '';

        // Skip dev/alpha/beta versions
        if (preg_match('/(dev|alpha|beta|rc)/i', $versionStr)) {
            continue;
        }

        $requires = $v['require'] ?? [];

        // Check if any illuminate/* or laravel/framework constraint covers ^13
        $supportsL13 = false;
        foreach ($requires as $dep => $constraint) {
            if (! str_starts_with($dep, 'illuminate/') && $dep !== 'laravel/framework') {
                continue;
            }
            // Check for ^13, ~13, 13.*, >=13, or a range including 13
            if (
                preg_match('/\^13/', $constraint) ||
                preg_match('/~13/', $constraint) ||
                preg_match('/13\.\*/', $constraint) ||
                preg_match('/>=\s*13/', $constraint) ||
                preg_match('/\|\|\s*\^13/', $constraint) ||
                preg_match('/\^13\s*\|/', $constraint)
            ) {
                $supportsL13 = true;
                break;
            }
        }

        if ($supportsL13) {
            $latestSupporting = $versionStr;
            break; // packagist returns newest first
        }
    }

    if ($latestSupporting !== null) {
        $canRemove[] = ['name' => $name, 'version' => $latestSupporting, 'pinned' => $pkg['version']];
    } else {
        $stillNeeded[] = ['name' => $name, 'pinned' => $pkg['version']];
    }
}

// Output results
if (! empty($canRemove)) {
    echo "\033[32m✓ Can remove override (upstream now supports Laravel 13):\033[0m\n";
    foreach ($canRemove as $pkg) {
        echo "  - {$pkg['name']} (pinned: {$pkg['pinned']} → latest L13: {$pkg['version']})\n";
    }
    echo "\n";
}

if (! empty($stillNeeded)) {
    echo "\033[33m⏳ Still needed (upstream does not yet support Laravel 13):\033[0m\n";
    foreach ($stillNeeded as $pkg) {
        echo "  - {$pkg['name']} (pinned: {$pkg['pinned']})\n";
    }
    echo "\n";
}

if (! empty($errors)) {
    echo "\033[31m✗ Could not check (network error):\033[0m\n";
    foreach ($errors as $name) {
        echo "  - {$name}\n";
    }
    echo "\n";
}

if (! empty($canRemove)) {
    echo "To remove an override: delete its \"type\": \"package\" block from the\n";
    echo "repositories section in composer.json, then run:\n";
    echo "  composer update <package-name>\n\n";
}

exit(empty($canRemove) ? 0 : 1);
