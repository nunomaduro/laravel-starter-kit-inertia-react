---
title: Validation After Hooks
impact: MEDIUM
impactDescription: Complex cross-field validation
tags: validation, hooks, custom-validation
---

## Validation After Hooks

**Impact: MEDIUM (Complex cross-field validation)**

Use after validation hooks for complex validation that depends on multiple fields or external data.

## Bad Example

```php
// Complex validation logic mixed in controller
class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
        ]);

        // Post-validation checks scattered in controller
        $room = Room::find($validated['room_id']);

        if ($validated['guests'] > $room->max_guests) {
            return back()->withErrors([
                'guests' => 'This room has a maximum capacity of ' . $room->max_guests,
            ]);
        }

        $existingBooking = Booking::where('room_id', $validated['room_id'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('check_in', [$validated['check_in'], $validated['check_out']])
                    ->orWhereBetween('check_out', [$validated['check_in'], $validated['check_out']]);
            })
            ->exists();

        if ($existingBooking) {
            return back()->withErrors([
                'room_id' => 'This room is not available for the selected dates.',
            ]);
        }

        // ... create booking
    }
}
```

## Good Example

```php
// Form Request with after validation hook
namespace App\Http\Requests;

use App\Models\Room;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'check_in' => ['required', 'date', 'after:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['required', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Only run if basic validation passed
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->validateRoomCapacity($validator);
            $this->validateRoomAvailability($validator);
            $this->validateMinimumStay($validator);
        });
    }

    private function validateRoomCapacity(Validator $validator): void
    {
        $room = Room::find($this->room_id);

        if ($room && $this->guests > $room->max_guests) {
            $validator->errors()->add(
                'guests',
                "This room has a maximum capacity of {$room->max_guests} guests."
            );
        }
    }

    private function validateRoomAvailability(Validator $validator): void
    {
        $hasConflict = Booking::where('room_id', $this->room_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('check_in', '<=', $this->check_in)
                      ->where('check_out', '>', $this->check_in);
                })->orWhere(function ($q) {
                    $q->where('check_in', '<', $this->check_out)
                      ->where('check_out', '>=', $this->check_out);
                })->orWhere(function ($q) {
                    $q->where('check_in', '>=', $this->check_in)
                      ->where('check_out', '<=', $this->check_out);
                });
            })
            ->exists();

        if ($hasConflict) {
            $validator->errors()->add(
                'room_id',
                'This room is not available for the selected dates.'
            );
        }
    }

    private function validateMinimumStay(Validator $validator): void
    {
        $checkIn = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);
        $nights = $checkIn->diffInDays($checkOut);

        $room = Room::find($this->room_id);

        if ($room && $nights < $room->minimum_nights) {
            $validator->errors()->add(
                'check_out',
                "This room requires a minimum stay of {$room->minimum_nights} nights."
            );
        }
    }
}
```

```php
// After hook for cross-field validation
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class UpdatePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!Hash::check($this->current_password, $this->user()->password)) {
                $validator->errors()->add(
                    'current_password',
                    'The current password is incorrect.'
                );
            }

            if ($this->current_password === $this->password) {
                $validator->errors()->add(
                    'password',
                    'New password must be different from current password.'
                );
            }
        });
    }
}
```

```php
// After hook with external API validation
class VerifyAddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'street' => ['required', 'string'],
            'city' => ['required', 'string'],
            'state' => ['required', 'string', 'size:2'],
            'zip' => ['required', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $addressService = app(AddressVerificationService::class);
            $result = $addressService->verify([
                'street' => $this->street,
                'city' => $this->city,
                'state' => $this->state,
                'zip' => $this->zip,
            ]);

            if (!$result->isValid()) {
                $validator->errors()->add('street', 'Unable to verify address.');

                if ($result->hasSuggestion()) {
                    $validator->errors()->add(
                        'suggestion',
                        'Did you mean: ' . $result->getSuggestion()
                    );
                }
            }
        });
    }
}
```

## Why

- **Complex validation**: Handle multi-field dependencies
- **External checks**: Validate against APIs or services
- **Database queries**: Check uniqueness across related records
- **Clean separation**: Keep complex logic out of controllers
- **Conditional execution**: Only run when basic validation passes
- **Rich error messages**: Add context-specific error details
