<?php

namespace App\Services\Admin;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Starts the GitHub Actions deploy (workflow_dispatch of config('services.github_deploy.workflow'))
 * and reads its latest runs. The server itself never pulls code: the workflow does git pull, the
 * image, migrations and caches, as on a merge to main.
 */
final class DeployTrigger
{
    private const API = 'https://api.github.com';

    private const RUNS_CACHE = 'admin.deploy.runs';

    public static function ready(): bool
    {
        return filled(config('services.github_deploy.token')) && filled(config('services.github_deploy.repo'));
    }

    /** @throws RuntimeException with a message for the admin */
    public static function dispatch(): void
    {
        if (!self::ready()) {
            throw new RuntimeException('GITHUB_DEPLOY_TOKEN .env da yozilmagan.');
        }

        if (self::running()) {
            throw new RuntimeException('Deploy hozir bajarilmoqda — tugashini kuting.');
        }

        try {
            $response = self::client()->post(self::workflowUrl() . '/dispatches', ['ref' => config('services.github_deploy.ref', 'main')]);
        } catch (ConnectionException) {
            throw new RuntimeException('GitHub bilan bog\'lanib bo\'lmadi.');
        }

        Cache::forget(self::RUNS_CACHE);

        if ($response->status() !== 204) {
            Log::error('Deploy dispatch refused', ['status' => $response->status(), 'message' => $response->json('message')]);

            throw new RuntimeException(match ($response->status()) {
                401     => 'GitHub tokeni noto\'g\'ri yoki muddati o\'tgan.',
                403, 404 => 'Tokenda bu repo uchun "Actions: read and write" ruxsati yo\'q (yoki workflow topilmadi).',
                422     => 'Workflow qo\'lda ishga tushirishni qabul qilmadi (workflow_dispatch yo\'q?).',
                default => 'GitHub javobi: ' . $response->status(),
            });
        }
    }

    /**
     * Latest runs of the deploy workflow, newest first (cached 15 s; empty when GitHub is unreachable).
     *
     * @return list<array{status: string, conclusion: ?string, event: string, sha: string, url: string, created_at: string}>
     */
    public static function runs(int $limit = 5): array
    {
        if (!self::ready()) {
            return [];
        }

        return Cache::remember(self::RUNS_CACHE, 15, function () use ($limit) {
            try {
                $response = self::client()->get(self::workflowUrl() . '/runs', ['per_page' => $limit]);
            } catch (ConnectionException) {
                return [];
            }

            return collect($response->successful() ? $response->json('workflow_runs', []) : [])
                ->map(fn (array $run) => [
                    'status'     => (string) ($run['status'] ?? ''),
                    'conclusion' => $run['conclusion'] ?? null,
                    'event'      => (string) ($run['event'] ?? ''),
                    'sha'        => substr((string) ($run['head_sha'] ?? ''), 0, 7),
                    'url'        => (string) ($run['html_url'] ?? ''),
                    'created_at' => (string) ($run['created_at'] ?? ''),
                ])->values()->all();
        });
    }

    /** A deploy is queued or running on GitHub */
    public static function running(): bool
    {
        return collect(self::runs())->contains(fn (array $run) => in_array($run['status'], ['queued', 'in_progress', 'waiting', 'pending', 'requested'], true));
    }

    private static function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withToken((string) config('services.github_deploy.token'))
            ->withHeaders(['Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28'])
            ->timeout(15);
    }

    private static function workflowUrl(): string
    {
        return self::API . '/repos/' . config('services.github_deploy.repo') . '/actions/workflows/' . rawurlencode((string) config('services.github_deploy.workflow', 'deploy.yml'));
    }
}
