---
section: forms
priority: high
description: Handle file uploads with progress tracking and preview functionality
keywords: [file, upload, formdata, progress, preview, multipart]
---

# Form File Uploads

Handle file uploads with Inertia using the useForm hook with proper progress tracking and preview functionality.

## Bad Example

```tsx
// Anti-pattern: Manual FormData handling
export default function UploadForm() {
  const [file, setFile] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('avatar', file);

    await fetch('/upload', {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
    });
  };

  return (
    <form onSubmit={handleSubmit}>
      <input type="file" onChange={(e) => setFile(e.target.files[0])} />
      <button type="submit">Upload</button>
    </form>
  );
}

// Anti-pattern: Not handling progress for large files
const { data, setData, post } = useForm({ document: null });

const submit = () => {
  post('/documents'); // No progress indicator for large files
};
```

## Good Example

```tsx
// resources/js/Pages/Profile/Edit.tsx
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useState, useRef } from 'react';
import InputError from '@/Components/InputError';

interface ProfileForm {
  name: string;
  email: string;
  avatar: File | null;
  _method?: string;
}

export default function EditProfile({ user }: { user: User }) {
  const [preview, setPreview] = useState<string | null>(user.avatar_url);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const { data, setData, post, processing, progress, errors, reset } = useForm<ProfileForm>({
    name: user.name,
    email: user.email,
    avatar: null,
  });

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      // Validate file on client side
      if (file.size > 5 * 1024 * 1024) {
        alert('File must be less than 5MB');
        return;
      }

      if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        alert('File must be an image (JPEG, PNG, or WebP)');
        return;
      }

      setData('avatar', file);

      // Create preview
      const reader = new FileReader();
      reader.onloadend = () => setPreview(reader.result as string);
      reader.readAsDataURL(file);
    }
  };

  const removeFile = () => {
    setData('avatar', null);
    setPreview(user.avatar_url);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    // Use post with _method for Laravel's form method spoofing
    post(route('profile.update'), {
      forceFormData: true, // Ensure FormData is used even for small payloads
      onSuccess: () => {
        if (fileInputRef.current) {
          fileInputRef.current.value = '';
        }
      },
    });
  };

  return (
    <form onSubmit={submit} className="space-y-6">
      <div>
        <label className="block text-sm font-medium text-gray-700">
          Profile Photo
        </label>

        <div className="mt-2 flex items-center gap-4">
          {/* Preview */}
          <div className="h-20 w-20 overflow-hidden rounded-full bg-gray-100">
            {preview ? (
              <img
                src={preview}
                alt="Avatar preview"
                className="h-full w-full object-cover"
              />
            ) : (
              <div className="flex h-full w-full items-center justify-center text-gray-400">
                No image
              </div>
            )}
          </div>

          <div className="flex flex-col gap-2">
            <input
              ref={fileInputRef}
              type="file"
              accept="image/jpeg,image/png,image/webp"
              onChange={handleFileChange}
              className="text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
            />
            {data.avatar && (
              <button
                type="button"
                onClick={removeFile}
                className="text-sm text-red-600 hover:text-red-800"
              >
                Remove new photo
              </button>
            )}
          </div>
        </div>

        <InputError message={errors.avatar} className="mt-2" />
      </div>

      {/* Progress bar for file upload */}
      {progress && (
        <div className="w-full">
          <div className="mb-1 flex justify-between text-sm">
            <span>Uploading...</span>
            <span>{progress.percentage}%</span>
          </div>
          <div className="h-2 w-full overflow-hidden rounded-full bg-gray-200">
            <div
              className="h-full bg-indigo-600 transition-all duration-300"
              style={{ width: `${progress.percentage}%` }}
            />
          </div>
        </div>
      )}

      {/* Other form fields */}
      <div>
        <label htmlFor="name">Name</label>
        <input
          id="name"
          value={data.name}
          onChange={(e) => setData('name', e.target.value)}
          className="mt-1 block w-full rounded-md border-gray-300"
        />
        <InputError message={errors.name} className="mt-2" />
      </div>

      <button
        type="submit"
        disabled={processing}
        className="rounded-md bg-indigo-600 px-4 py-2 text-white disabled:opacity-50"
      >
        {processing ? 'Saving...' : 'Save Changes'}
      </button>
    </form>
  );
}

// Multiple file uploads
interface GalleryForm {
  title: string;
  images: File[];
}

function GalleryUpload() {
  const { data, setData, post, progress, errors } = useForm<GalleryForm>({
    title: '',
    images: [],
  });

  const handleMultipleFiles = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    setData('images', [...data.images, ...files]);
  };

  const removeImage = (index: number) => {
    setData('images', data.images.filter((_, i) => i !== index));
  };

  return (
    <form onSubmit={(e) => { e.preventDefault(); post('/gallery'); }}>
      <input
        type="file"
        multiple
        accept="image/*"
        onChange={handleMultipleFiles}
      />
      <div className="grid grid-cols-4 gap-2">
        {data.images.map((file, index) => (
          <div key={index} className="relative">
            <img
              src={URL.createObjectURL(file)}
              alt={`Preview ${index}`}
              className="h-24 w-24 object-cover"
            />
            <button
              type="button"
              onClick={() => removeImage(index)}
              className="absolute -right-2 -top-2 rounded-full bg-red-500 p-1 text-white"
            >
              X
            </button>
          </div>
        ))}
      </div>
      {errors.images && <InputError message={errors.images} />}
    </form>
  );
}
```

## Why

Using Inertia's built-in file upload handling provides:

1. **Automatic FormData**: Inertia converts data to FormData when files are present
2. **Progress Tracking**: Real-time upload progress for better UX
3. **CSRF Handling**: Tokens are automatically included
4. **Validation Integration**: Server-side errors display like regular field errors
5. **Simplified API**: No manual FormData construction or fetch calls
6. **Preview Support**: Client-side previews improve user experience
7. **Chunked Uploads**: For very large files, consider pairing with libraries like Filepond
