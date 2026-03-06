<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Process;

final readonly class DocumentationChangeDetector
{
    /**
     * Detect changes in staged files that require documentation updates.
     *
     * @return array<string, array<string, mixed>>
     */
    public function detectStagedChanges(): array
    {
        $changes = [
            'actions' => [],
            'controllers' => [],
            'pages' => [],
            'routes' => false,
        ];

        // Get staged files
        $stagedFiles = $this->getStagedFiles();

        foreach ($stagedFiles as $file) {
            // Check Actions
            if (str_contains($file, 'app/Actions/') && str_ends_with($file, '.php')) {
                $actionName = basename($file, '.php');
                $changes['actions'][$actionName] = $this->analyzeActionChanges($file);
            }

            // Check Controllers
            if (str_contains($file, 'app/Http/Controllers/') && str_ends_with($file, '.php')) {
                $controllerName = basename($file, '.php');
                $changes['controllers'][$controllerName] = $this->analyzeControllerChanges($file);
            }

            // Check Pages
            if (str_contains($file, 'resources/js/pages/') && str_ends_with($file, '.tsx')) {
                $pagePath = str_replace(['resources/js/pages/', '.tsx'], '', $file);
                $changes['pages'][$pagePath] = $this->analyzePageChanges($file);
            }

            // Check routes
            if (str_contains($file, 'routes/web.php')) {
                $changes['routes'] = true;
            }
        }

        return $changes;
    }

    /**
     * Check if documentation needs update based on changes.
     *
     * @param  array<string, mixed>  $changes
     */
    public function needsDocumentationUpdate(array $changes): bool
    {
        // Check Actions
        foreach ($changes['actions'] as $actionChanges) {
            if ($actionChanges['methodSignatureChanged'] ||
                $actionChanges['parametersChanged'] ||
                $actionChanges['returnTypeChanged'] ||
                $actionChanges['dependenciesChanged']) {
                return true;
            }
        }

        // Check Controllers
        foreach ($changes['controllers'] as $controllerChanges) {
            if ($controllerChanges['methodsChanged'] ||
                $controllerChanges['actionsChanged'] ||
                $controllerChanges['formRequestsChanged']) {
                return true;
            }
        }

        // Check Pages
        foreach ($changes['pages'] as $pageChanges) {
            if ($pageChanges['propsChanged'] || $pageChanges['componentChanged']) {
                return true;
            }
        }

        // Check routes
        return (bool) $changes['routes'];
    }

    /**
     * Get human-readable summary of changes.
     *
     * @param  array<string, mixed>  $changes
     */
    public function getChangeSummary(array $changes): string
    {
        $summary = [];

        foreach ($changes['actions'] as $actionName => $actionChanges) {
            $actionSummary = [];
            if ($actionChanges['methodSignatureChanged']) {
                $actionSummary[] = 'method signature';
            }

            if ($actionChanges['parametersChanged']) {
                $actionSummary[] = 'parameters';
            }

            if ($actionChanges['returnTypeChanged']) {
                $actionSummary[] = 'return type';
            }

            if ($actionChanges['dependenciesChanged']) {
                $actionSummary[] = 'dependencies';
            }

            if ($actionSummary !== []) {
                $summary[] = sprintf('Action %s: ', $actionName).implode(', ', $actionSummary).' changed';
            }
        }

        foreach ($changes['controllers'] as $controllerName => $controllerChanges) {
            $controllerSummary = [];
            if ($controllerChanges['methodsChanged']) {
                $controllerSummary[] = 'methods';
            }

            if ($controllerChanges['actionsChanged']) {
                $controllerSummary[] = 'actions used';
            }

            if ($controllerChanges['formRequestsChanged']) {
                $controllerSummary[] = 'form requests';
            }

            if ($controllerSummary !== []) {
                $summary[] = sprintf('Controller %s: ', $controllerName).implode(', ', $controllerSummary).' changed';
            }
        }

        foreach ($changes['pages'] as $pagePath => $pageChanges) {
            $pageSummary = [];
            if ($pageChanges['propsChanged']) {
                $pageSummary[] = 'props';
            }

            if ($pageChanges['componentChanged']) {
                $pageSummary[] = 'component';
            }

            if ($pageSummary !== []) {
                $summary[] = sprintf('Page %s: ', $pagePath).implode(', ', $pageSummary).' changed';
            }
        }

        if ($changes['routes']) {
            $summary[] = 'Routes changed';
        }

        return implode("\n", $summary);
    }

    /**
     * Analyze changes in an Action file.
     *
     * @return array<string, mixed>
     */
    private function analyzeActionChanges(string $filePath): array
    {
        $changes = [
            'methodSignatureChanged' => false,
            'parametersChanged' => false,
            'returnTypeChanged' => false,
            'dependenciesChanged' => false,
        ];

        // Get diff for this file
        $diff = $this->getFileDiff($filePath);

        // Check for method signature changes
        if (preg_match('/[-+].*function\s+handle\(/', $diff)) {
            $changes['methodSignatureChanged'] = true;
        }

        // Check for parameter changes
        if (preg_match('/[-+].*@param/', $diff) || preg_match('/[-+].*function\s+handle\([^)]*\)/', $diff)) {
            $changes['parametersChanged'] = true;
        }

        // Check for return type changes
        if (preg_match('/[-+].*:\s*\w+/', $diff)) {
            $changes['returnTypeChanged'] = true;
        }

        // Check for constructor changes (dependencies)
        if (preg_match('/[-+].*__construct\(/', $diff)) {
            $changes['dependenciesChanged'] = true;
        }

        return $changes;
    }

    /**
     * Analyze changes in a Controller file.
     *
     * @return array<string, mixed>
     */
    private function analyzeControllerChanges(string $filePath): array
    {
        $changes = [
            'methodsChanged' => false,
            'actionsChanged' => false,
            'formRequestsChanged' => false,
        ];

        $diff = $this->getFileDiff($filePath);

        // Check for method changes
        if (preg_match('/[-+].*public\s+function\s+\w+\(/', $diff)) {
            $changes['methodsChanged'] = true;
        }

        // Check for Action usage changes
        if (preg_match('/[-+].*Actions\\\\/', $diff)) {
            $changes['actionsChanged'] = true;
        }

        // Check for Form Request changes
        if (preg_match('/[-+].*Requests\\\\/', $diff)) {
            $changes['formRequestsChanged'] = true;
        }

        return $changes;
    }

    /**
     * Analyze changes in a Page file.
     *
     * @return array<string, mixed>
     */
    private function analyzePageChanges(string $filePath): array
    {
        $changes = [
            'propsChanged' => false,
            'componentChanged' => false,
        ];

        $diff = $this->getFileDiff($filePath);

        // Check for prop changes
        if (preg_match('/[-+].*(interface|type).*Props/', $diff) || preg_match('/[-+].*:\s*\w+[?]?;/', $diff)) {
            $changes['propsChanged'] = true;
        }

        // Check for component changes
        if (preg_match('/[-+].*export\s+default/', $diff)) {
            $changes['componentChanged'] = true;
        }

        return $changes;
    }

    /**
     * Get list of staged files from git.
     *
     * @return array<string>
     */
    private function getStagedFiles(): array
    {
        $result = Process::run(['git', 'diff', '--cached', '--name-only', '--diff-filter=ACMR']);

        if (! $result->successful()) {
            return [];
        }

        $lines = array_map(trim(...), explode("\n", $result->output()));

        return array_values(array_filter($lines));
    }

    /**
     * Get git diff for a specific file.
     */
    private function getFileDiff(string $filePath): string
    {
        $result = Process::run(['git', 'diff', '--cached', '--', $filePath]);

        if (! $result->successful()) {
            return '';
        }

        return $result->output();
    }
}
