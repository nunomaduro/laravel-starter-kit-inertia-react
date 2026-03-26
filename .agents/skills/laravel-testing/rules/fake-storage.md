---
title: Faking Storage with Storage::fake()
impact: HIGH
impactDescription: Prevents real file writes during tests and enables assertion on file upload behaviour
tags: faking, storage, Storage::fake, assertExists, assertMissing, file upload, pest
---

## Faking Storage with Storage::fake()

**Impact: HIGH (Prevents real file writes during tests and enables assertion on file upload behaviour)**

Call `Storage::fake('disk')` before any code that reads or writes files. This swaps the real filesystem with an in-memory fake. Use `UploadedFile::fake()` to generate fake files for upload tests. Assert presence or absence of files with `Storage::disk()->assertExists()`.

## Bad Example

```php
<?php

// Writes real files to disk — pollutes storage, fails in CI environments
test('user can upload avatar', function () {
    $user = User::factory()->create();
    $file = new \Illuminate\Http\UploadedFile(
        storage_path('test-image.jpg'), // relies on a file existing locally
        'avatar.jpg',
        'image/jpeg',
        null,
        true
    );

    $this->actingAs($user)
        ->postJson('/api/avatar', ['file' => $file])
        ->assertOk();
    // Real file written to storage/app/public — not cleaned up after test
});
```

## Good Example

```php
<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Basic upload test
test('user can upload an avatar', function () {
    Storage::fake('avatars');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    $this->actingAs($user)
        ->postJson('/api/avatar', ['avatar' => $file])
        ->assertOk();

    Storage::disk('avatars')->assertExists("users/{$user->id}/avatar.jpg");
});

// Assert file is not present when upload is rejected
test('oversized image is rejected and not stored', function () {
    Storage::fake('avatars');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('big.jpg')->size(5001); // 5001 KB > limit

    $this->actingAs($user)
        ->postJson('/api/avatar', ['avatar' => $file])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['avatar']);

    Storage::disk('avatars')->assertMissing("users/{$user->id}/big.jpg");
});

// Assert file is removed on delete
test('deleting avatar removes file from storage', function () {
    Storage::fake('avatars');

    $user = User::factory()->create([
        'avatar_path' => "users/{$user->id}/avatar.jpg",
    ]);
    Storage::disk('avatars')->put("users/{$user->id}/avatar.jpg", 'fake-content');

    $this->actingAs($user)
        ->deleteJson('/api/avatar')
        ->assertNoContent();

    Storage::disk('avatars')->assertMissing("users/{$user->id}/avatar.jpg");
});

// Fake multiple disks
test('document is moved from temp to permanent disk', function () {
    Storage::fake('temp');
    Storage::fake('documents');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('report.pdf', 512, 'application/pdf');

    $this->actingAs($user)
        ->postJson('/api/documents', ['file' => $file])
        ->assertStatus(201);

    Storage::disk('documents')->assertExists("reports/{$user->id}/report.pdf");
    Storage::disk('temp')->assertMissing("reports/{$user->id}/report.pdf");
});
```

## UploadedFile::fake() Helpers

```php
UploadedFile::fake()->image('photo.jpg')           // JPEG image
UploadedFile::fake()->image('photo.png', 400, 300) // image with dimensions
UploadedFile::fake()->create('doc.pdf', 1024)      // file with size in KB
UploadedFile::fake()->create('doc.pdf', 1024, 'application/pdf') // with MIME
```

## Key Assertions

| Method | Description |
|--------|-------------|
| `Storage::disk('s3')->assertExists($path)` | File exists at path |
| `Storage::disk('s3')->assertMissing($path)` | File does not exist at path |
| `Storage::disk('s3')->assertDirectoryEmpty($dir)` | Directory has no files |

## Why It Matters

- **No real I/O**: No files written to disk or S3 — tests are fast, clean, and portable
- **CI-safe**: No dependency on local filesystem paths or cloud credentials
- **Assertions**: Explicitly verify that files are created at the expected paths

Reference: [Laravel Mocking — Storage Fake](https://laravel.com/docs/13.x/mocking#storage-fake)
