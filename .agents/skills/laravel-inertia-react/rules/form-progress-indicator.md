---
section: forms
priority: high
description: Show upload progress and processing states for better user feedback
keywords: [progress, indicator, upload, processing, feedback, ux]
---

# Form Progress Indicator

Show upload progress for forms with file uploads and processing states for all form submissions.

## Bad Example

```tsx
// Anti-pattern: No feedback during submission
export default function DocumentUpload() {
  const { data, setData, post } = useForm({
    document: null,
  });

  return (
    <form onSubmit={(e) => { e.preventDefault(); post('/documents'); }}>
      <input
        type="file"
        onChange={(e) => setData('document', e.target.files[0])}
      />
      <button type="submit">Upload</button>
      {/* No progress indicator - user has no idea if upload is working */}
    </form>
  );
}

// Anti-pattern: Only using processing state
<button disabled={processing}>
  {processing ? 'Uploading...' : 'Upload'}
</button>
// Missing actual progress percentage for large files
```

## Good Example

```tsx
// resources/js/Components/ProgressBar.tsx
interface ProgressBarProps {
  progress: {
    percentage: number;
    loaded?: number;
    total?: number;
  } | null;
  showBytes?: boolean;
}

function formatBytes(bytes: number): string {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

export default function ProgressBar({ progress, showBytes = false }: ProgressBarProps) {
  if (!progress) return null;

  return (
    <div className="w-full">
      <div className="mb-1 flex justify-between text-sm text-gray-600">
        <span>Uploading...</span>
        <span>
          {progress.percentage}%
          {showBytes && progress.loaded && progress.total && (
            <span className="ml-2 text-gray-400">
              ({formatBytes(progress.loaded)} / {formatBytes(progress.total)})
            </span>
          )}
        </span>
      </div>
      <div className="h-2.5 w-full overflow-hidden rounded-full bg-gray-200">
        <div
          className="h-full rounded-full bg-blue-600 transition-all duration-300 ease-out"
          style={{ width: `${progress.percentage}%` }}
        />
      </div>
    </div>
  );
}

// resources/js/Pages/Documents/Upload.tsx
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import ProgressBar from '@/Components/ProgressBar';

interface UploadForm {
  title: string;
  document: File | null;
}

export default function Upload() {
  const [uploadState, setUploadState] = useState<'idle' | 'uploading' | 'processing' | 'complete'>('idle');

  const { data, setData, post, processing, progress, errors, reset } = useForm<UploadForm>({
    title: '',
    document: null,
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();

    post(route('documents.store'), {
      onStart: () => setUploadState('uploading'),
      onProgress: (progress) => {
        // When upload is complete but server is still processing
        if (progress.percentage === 100) {
          setUploadState('processing');
        }
      },
      onSuccess: () => {
        setUploadState('complete');
        setTimeout(() => {
          setUploadState('idle');
          reset();
        }, 2000);
      },
      onError: () => setUploadState('idle'),
    });
  };

  return (
    <form onSubmit={submit} className="space-y-6">
      <div>
        <label htmlFor="title" className="block text-sm font-medium">
          Document Title
        </label>
        <input
          id="title"
          type="text"
          value={data.title}
          onChange={(e) => setData('title', e.target.value)}
          disabled={processing}
          className="mt-1 block w-full rounded-md border-gray-300 disabled:bg-gray-100"
        />
      </div>

      <div>
        <label htmlFor="document" className="block text-sm font-medium">
          Document File
        </label>
        <input
          id="document"
          type="file"
          onChange={(e) => setData('document', e.target.files?.[0] || null)}
          disabled={processing}
          className="mt-1 block w-full disabled:opacity-50"
        />
        {errors.document && (
          <p className="mt-1 text-sm text-red-600">{errors.document}</p>
        )}
      </div>

      {/* Progress indicator */}
      {uploadState === 'uploading' && progress && (
        <ProgressBar progress={progress} showBytes />
      )}

      {/* Processing state (after upload, server is working) */}
      {uploadState === 'processing' && (
        <div className="flex items-center gap-2 text-sm text-gray-600">
          <svg className="h-5 w-5 animate-spin text-blue-600" viewBox="0 0 24 24">
            <circle
              className="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              strokeWidth="4"
              fill="none"
            />
            <path
              className="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
            />
          </svg>
          <span>Processing document...</span>
        </div>
      )}

      {/* Success state */}
      {uploadState === 'complete' && (
        <div className="flex items-center gap-2 text-sm text-green-600">
          <svg className="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path
              fillRule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
              clipRule="evenodd"
            />
          </svg>
          <span>Upload complete!</span>
        </div>
      )}

      <button
        type="submit"
        disabled={processing || !data.document}
        className="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white disabled:opacity-50"
      >
        {processing ? (
          <>
            <svg className="h-4 w-4 animate-spin" viewBox="0 0 24 24">
              <circle
                className="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                strokeWidth="4"
                fill="none"
              />
              <path
                className="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
              />
            </svg>
            <span>
              {uploadState === 'processing' ? 'Processing...' : 'Uploading...'}
            </span>
          </>
        ) : (
          'Upload Document'
        )}
      </button>
    </form>
  );
}
```

## Why

Progress indicators are essential for good user experience:

1. **User Confidence**: Users know their action is being processed
2. **Large Files**: Essential for uploads that take more than a second
3. **Accurate Feedback**: Percentage shows actual progress, not just "loading"
4. **State Distinction**: Differentiate between uploading, server processing, and completion
5. **Prevent Duplicates**: Disabled buttons prevent accidental resubmission
6. **Accessibility**: Screen readers can announce progress updates
7. **Error Recovery**: Users understand when something goes wrong
