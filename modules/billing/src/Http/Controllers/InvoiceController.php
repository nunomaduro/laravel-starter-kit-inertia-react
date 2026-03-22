<?php

declare(strict_types=1);

namespace Modules\Billing\Http\Controllers;

use App\Services\TenantContext;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Billing\Actions\BuildLaravelDailyInvoice;
use Modules\Billing\Models\Invoice;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final readonly class InvoiceController
{
    public function __construct(
        private BuildLaravelDailyInvoice $buildLaravelDailyInvoice
    ) {}

    public function index(): Response
    {
        $organization = TenantContext::get();
        abort_unless($organization, 403, 'No organization selected.');
        $invoices = $organization->invoices()->paginate(15);

        return Inertia::render('billing/invoices', [
            'invoices' => $invoices,
        ]);
    }

    public function download(Invoice $invoice): HttpResponse
    {
        Gate::authorize('download', $invoice);

        $laravelInvoice = $this->buildLaravelDailyInvoice->handle($invoice);

        return $laravelInvoice->filename("invoice-{$invoice->number}")->download();
    }
}
