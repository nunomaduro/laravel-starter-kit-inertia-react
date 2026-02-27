<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Notifications\SlackCriticalAlertNotification;
use App\Support\SlackWebhookRecipient;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendSlackAlertOnJobFailed
{
    public function handle(JobFailed $event): void
    {
        if (empty(config('services.slack.webhook_url'))) {
            return;
        }

        try {
            $recipient = new SlackWebhookRecipient;
            $jobName = $event->job->resolveName();
            $exception = $event->exception;
            $body = $exception->getMessage();
            if (mb_strlen($body) > 2000) {
                $body = mb_substr($body, 0, 1997).'...';
            }
            $recipient->notify(new SlackCriticalAlertNotification(
                title: 'Queue job failed: '.$jobName,
                body: $body,
                level: 'error',
            ));
        } catch (Throwable $e) {
            Log::warning('Failed to send Slack alert for failed job', [
                'job' => $event->job->resolveName(),
                'slack_error' => $e->getMessage(),
            ]);
        }
    }
}
