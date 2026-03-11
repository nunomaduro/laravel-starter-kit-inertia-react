# Model HashId (deligoez/laravel-model-hashid)

[deligoez/laravel-model-hashid](https://github.com/deligoez/laravel-model-hashid) provides **HashIds** for Eloquent models so that URLs use short, non-sequential identifiers (e.g. `user_kqYZeLgo`) instead of numeric IDs, avoiding leakage of row counts and enabling nicer shareable links.

## Models using HashId

- **User** — `users/{user}` routes (show, duplicate); DataTable and show page use `hash_id` for links.
- **Invoice** — `billing/invoices/{invoice}` (download); list payload includes `hash_id` for future download links.

Organization uses `slug` for routing (`getRouteKeyName()` = slug); HashId is not used there.

## Usage

1. **Traits:** Add `HasHashId` and `HasHashIdRouting` to the model. `HasHashId` adds `hashId` / `hashIdRaw` and `keyFromHashId()`; `HasHashIdRouting` makes route model binding resolve by hashid and `getRouteKey()` return the hashid.
2. **Config:** `config/model-hashid.php` — salt (`HASHID_SALT`), length, alphabet, model prefix. Use a strong salt in production.
3. **Appends (optional):** Add `hash_id` to `$appends` if the model is serialized to JSON for the frontend (e.g. so Inertia receives `hash_id` for building URLs).

## Route binding and links

- **Backend:** Routes like `users/{user}` and `billing/invoices/{invoice}` resolve the model by hashid automatically when the request URL contains the hashid (e.g. `/users/user_kqYZeLgo`).
- **Frontend:** Use the `hash_id` (or `hashId`) value from the API/Inertia props when building user or invoice URLs (e.g. `/users/${user.hash_id}`, `/billing/invoices/${invoice.hash_id}`). Do not use numeric `id` in path segments for these resources.
- **Wayfinder:** When generating URLs with a model instance (e.g. `route('users.show', $user)`), Laravel uses `getRouteKey()`, so the hashid is used automatically.

## Validation

Use the package’s validation rules (e.g. `HashIdExists::for(User::class)`) in form requests when the input is a hashid for one of these models.
