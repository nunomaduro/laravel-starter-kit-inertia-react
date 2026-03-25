<?php

declare(strict_types=1);

use App\Actions\GeneratePdf;
use App\Jobs\GeneratePdfJob;
use App\Models\User;
use App\Notifications\PdfFailedNotification;
use App\Notifications\PdfReadyNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function (): void {
    // Ensure the pdf storage directory is clean for tests
    $pdfDir = storage_path('app/pdf');
    if (is_dir($pdfDir)) {
        array_map('unlink', glob($pdfDir.'/*.pdf') ?: []);
    }
});

it('generates a pdf from a blade view and returns the path', function (): void {
    Pdf::fake();

    $action = new GeneratePdf;
    $filename = 'test-'.uniqid().'.pdf';

    $path = $action->handle(
        view: 'pdf.test-document',
        data: ['title' => 'Test Document', 'body' => 'Hello world'],
        filename: $filename,
    );

    expect($path)->toBe(storage_path('app/pdf/'.$filename));
    Pdf::assertViewIs('pdf.test-document');
});

it('dispatches the job to the queue', function (): void {
    Queue::fake();

    $user = User::factory()->create();

    GeneratePdfJob::dispatch(
        view: 'pdf.test-document',
        data: ['title' => 'Queued PDF'],
        filename: 'queued.pdf',
        userId: $user->id,
    );

    Queue::assertPushed(GeneratePdfJob::class, function (GeneratePdfJob $job): bool {
        return true;
    });
});

it('job calls action and notifies user on success', function (): void {
    Notification::fake();
    Pdf::fake();

    $user = User::factory()->create();
    $filename = 'success-'.uniqid().'.pdf';

    $job = new GeneratePdfJob(
        view: 'pdf.test-document',
        data: ['title' => 'Success PDF'],
        filename: $filename,
        userId: $user->id,
    );

    $job->handle(new GeneratePdf);

    Notification::assertSentTo($user, PdfReadyNotification::class, function (PdfReadyNotification $notification) use ($user): bool {
        $data = $notification->toDatabase($user);

        return isset($data['filename']) && isset($data['path']);
    });
});

it('job notifies user of failure via failed method', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $filename = 'failed.pdf';

    $job = new GeneratePdfJob(
        view: 'pdf.test-document',
        data: [],
        filename: $filename,
        userId: $user->id,
    );

    $job->failed(new RuntimeException('PDF generation failed'));

    Notification::assertSentTo($user, PdfFailedNotification::class, function (PdfFailedNotification $notification) use ($user, $filename): bool {
        $data = $notification->toDatabase($user);

        return $data['filename'] === $filename && $data['error'] === 'PDF generation failed';
    });
});

it('pdf ready notification has correct data shape', function (): void {
    $notification = new PdfReadyNotification(
        filename: 'report.pdf',
        path: '/storage/app/pdf/report.pdf',
    );

    $notifiable = new stdClass;

    expect($notification->via($notifiable))->toBe(['database']);

    $data = $notification->toDatabase($notifiable);

    expect($data)->toHaveKeys(['filename', 'path'])
        ->and($data['filename'])->toBe('report.pdf')
        ->and($data['path'])->toBe('/storage/app/pdf/report.pdf');
});

it('pdf failed notification has correct data shape', function (): void {
    $notification = new PdfFailedNotification(
        filename: 'report.pdf',
        errorMessage: 'Something went wrong',
    );

    $notifiable = new stdClass;

    expect($notification->via($notifiable))->toBe(['database']);

    $data = $notification->toDatabase($notifiable);

    expect($data)->toHaveKeys(['filename', 'error'])
        ->and($data['filename'])->toBe('report.pdf')
        ->and($data['error'])->toBe('Something went wrong');
});
