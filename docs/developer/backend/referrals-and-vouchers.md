# Referrals and Vouchers

## Referrals (jijunair/laravel-referral)

- **Package**: [jijunair/laravel-referral](https://github.com/jijunair/laravel-referral) — referral codes and tracking.
- **Model**: `User` uses the `Referrable` trait; referral links and codes are generated and stored by the package.
- **Routes**: `GET save20/{referralCode}` (`referralLink`) — package `ReferralController`; `GET generate-ref-accounts` (`generateReferralCodes`) for generating referral codes for existing users.
- **Filament**: Affiliates resource under Billing (`filament.admin.resources.billing.affiliates.*`, `filament.system.resources.billing.affiliates.*`). `Affiliate` model tracks referrals and conversions; `CreditTransactionType::Referral` for credit attribution.
- **Billing**: Referral credits and conversion tracking tie into the credits system; extend as needed for your commission or reward rules.

## Vouchers (beyondcode/laravel-vouchers)

- **Package**: [beyondcode/laravel-vouchers](https://github.com/beyondcode/laravel-vouchers) — discount codes and redemption.
- **Model**: `User` uses `CanRedeemVouchers`. Vouchers are bound to a scope (e.g. global) via the polymorphic `model_type` / `model_id` on the package’s `Voucher` model.
- **Scope**: `App\Models\VoucherScope` is the scope/owner for global vouchers; create one “Global” scope and attach vouchers to it. Seeder: `Database\Seeders\Development\VoucherScopeSeeder`.
- **Filament**: `App\Filament\Resources\Vouchers\VoucherResource` — list, create, edit vouchers; uses package `Vouchers::generate()` and the package’s `Voucher` model. Export action on the table.
- **Routes**: Filament handles admin routes; redemption is typically done in the app (e.g. checkout or a “Redeem code” flow) by calling the package’s redemption API with the authenticated user.

## Summary

| Package | Purpose | Key usage |
|--------|---------|-----------|
| jijunair/laravel-referral | Referral codes, links, tracking | User `Referrable`, Affiliates resource, referralLink route |
| beyondcode/laravel-vouchers | Discount codes, redemption | User `CanRedeemVouchers`, VoucherScope, VoucherResource |
