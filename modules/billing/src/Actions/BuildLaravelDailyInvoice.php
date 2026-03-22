<?php

declare(strict_types=1);

namespace Modules\Billing\Actions;

use Carbon\Carbon;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Facades\Invoice;
use Modules\Billing\Models\Invoice as AppInvoice;

final readonly class BuildLaravelDailyInvoice
{
    /**
     * Build a LaravelDaily Invoice instance from an App Invoice for PDF download/stream.
     */
    public function handle(AppInvoice $invoice): \LaravelDaily\Invoices\Invoice
    {
        $invoice->loadMissing('organization');

        $buyer = $this->buildBuyer($invoice);
        $laravelInvoice = Invoice::make()
            ->buyer($buyer)
            ->series($invoice->number)
            ->currencyCode(mb_strtoupper($invoice->currency))
            ->date($this->invoiceDate($invoice));

        foreach ($this->buildItems($invoice) as $item) {
            $laravelInvoice->addItem($item);
        }

        if ($invoice->tax > 0 && $invoice->subtotal > 0) {
            $rate = round($invoice->tax / $invoice->subtotal * 100, 2);
            $laravelInvoice->taxRate((float) $rate);
        }

        return $laravelInvoice;
    }

    private function buildBuyer(AppInvoice $invoice): Buyer
    {
        $attrs = $invoice->billing_address ?? [];
        if ($attrs === [] && $invoice->relationLoaded('organization') && $invoice->organization) {
            $org = $invoice->organization;
            $attrs = $org->billing_address ?? [
                'name' => $org->name,
                'address' => null,
                'custom_fields' => array_filter([
                    'email' => $org->billing_email ?? $org->owner?->email,
                ]),
            ];
        }
        if ($attrs === []) {
            $attrs = ['name' => 'Customer', 'address' => null];
        }
        if (! isset($attrs['custom_fields'])) {
            $attrs['custom_fields'] = [];
        }

        return new Buyer($attrs);
    }

    /**
     * @return list<InvoiceItem>
     */
    private function buildItems(AppInvoice $invoice): array
    {
        $items = [];
        $lineItems = $invoice->line_items;

        if (! empty($lineItems) && is_array($lineItems)) {
            foreach ($lineItems as $row) {
                $name = $row['name'] ?? $row['description'] ?? 'Item';
                $priceCents = isset($row['price']) ? (int) $row['price'] : (int) ($row['total'] ?? 0);
                $quantity = isset($row['quantity']) ? (float) $row['quantity'] : 1.0;
                if (isset($row['total']) && $quantity > 0) {
                    $priceCents = (int) round((float) $row['total'] / $quantity);
                }
                $priceInUnits = $priceCents / 100;
                $item = InvoiceItem::make($name)
                    ->pricePerUnit($priceInUnits)
                    ->quantity($quantity);
                $items[] = $item;
            }
        }

        if ($items === []) {
            $totalInUnits = $invoice->total / 100;
            $items[] = InvoiceItem::make('Invoice #'.$invoice->number)
                ->pricePerUnit($totalInUnits)
                ->quantity(1);
        }

        return $items;
    }

    private function invoiceDate(AppInvoice $invoice): Carbon
    {
        if ($invoice->paid_at) {
            return Carbon::instance($invoice->paid_at);
        }
        if ($invoice->due_date) {
            return Carbon::instance($invoice->due_date);
        }

        return now();
    }
}
