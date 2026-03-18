# BuildLaravelDailyInvoice

## Purpose

Builds a LaravelDaily `Invoice` instance from an app `Invoice` for PDF download or stream. Handles buyer from billing address or organization, line items (or a single total line), and tax.

## Location

`app/Actions/Billing/BuildLaravelDailyInvoice.php`

## Method Signature

```php
public function handle(App\Models\Billing\Invoice $invoice): \LaravelDaily\Invoices\Invoice
```

## Dependencies

None.

## Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$invoice` | `App\Models\Billing\Invoice` | The app invoice to convert |

## Return Value

A LaravelDaily `Invoice` instance ready for `->stream()` or `->download()`.

## Usage Examples

### From Controller

```php
$pdf = app(BuildLaravelDailyInvoice::class)->handle($invoice);
return $pdf->stream();
```

## Related Components

- **Controller**: `InvoiceController` (`download`)
- **Route**: Billing invoice download (tenant-scoped)
- **Model**: `App\Models\Billing\Invoice`

## Notes

- Buyer is built from `invoice.billing_address`, or falls back to organization billing/name/email.
- Line items come from `invoice.line_items`; if empty, a single line with the invoice total is used.
- Tax rate is derived from `invoice.tax` and `invoice.subtotal` when both are present.
- Date is `paid_at`, then `due_date`, then `now()`.
