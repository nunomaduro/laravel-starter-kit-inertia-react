---
title: Files and Vector Stores for RAG
impact: MEDIUM
impactDescription: Store files with providers and build searchable knowledge bases
tags: files, vector-stores, rag, document, storage
---

## Files and Vector Stores for RAG

**Impact: MEDIUM (Store files with providers and build searchable knowledge bases)**

Store files with AI providers for repeated use in conversations. Create vector stores to build searchable document collections for retrieval-augmented generation (RAG).

## Bad Example

```php
// Re-uploading the same file on every prompt
$response = (new DocumentAnalyzer)->prompt(
    'Summarize this document.',
    attachments: [
        // This uploads the file every single time
        Files\Document::fromStorage('report.pdf'),
    ],
);
```

## Good Example

```php
// Store a file once with the provider
use Laravel\Ai\Files\Document;
use Laravel\Ai\Files\Image;

$stored = Document::fromPath('/path/to/report.pdf')->put();
$stored = Document::fromStorage('report.pdf', disk: 'local')->put();
$stored = Document::fromUrl('https://example.com/doc.pdf')->put();
$stored = Document::fromUpload($request->file('document'))->put();
$stored = Image::fromPath('/path/to/photo.jpg')->put();

$fileId = $stored->id;
```

```php
// Reference stored file in conversations — no re-upload
$response = (new DocumentAnalyzer)->prompt(
    'Summarize this document.',
    attachments: [
        Document::fromId($fileId),
    ],
);
```

```php
// Delete a stored file
Document::fromId('file-id')->delete();
```

```php
// Create a vector store for RAG
use Laravel\Ai\Stores;

$store = Stores::create(
    name: 'Knowledge Base',
    description: 'Documentation and reference materials.',
    expiresWhenIdleFor: days(30),
);
```

```php
// Add files to a vector store — auto-indexed for searching
$store = Stores::get('store_id');

$document = $store->add(Document::fromPath('/path/to/doc.pdf'));
$document = $store->add(Document::fromStorage('manual.pdf'));
$document = $store->add($request->file('document'));

// Add with metadata for filtering
$store->add(Document::fromPath('/path/to/doc.pdf'), metadata: [
    'author' => 'Taylor Otwell',
    'department' => 'Engineering',
    'year' => 2026,
]);
```

```php
// Remove file from store
$store->remove('file_id');

// Remove and delete permanently
$store->remove('file_id', deleteFile: true);
```

```php
// Use vector store with FileSearch provider tool
use Laravel\Ai\Providers\Tools\FileSearch;

public function tools(): iterable
{
    return [
        new FileSearch(stores: ['store_id']),
    ];
}
```

## Why

- **Efficiency**: Store once, reference by ID — no repeated uploads
- **RAG**: Vector stores + FileSearch enable knowledge-base-powered agents
- **Metadata filtering**: Filter search results by author, date, department, etc.
- **Provider support**: OpenAI, Anthropic, Gemini for file storage

Reference: [Laravel AI SDK — Files](https://laravel.com/docs/13.x/ai-sdk#files)
