import UserProfileController from '@/actions/App/Http/Controllers/UserProfileController';
import { useInitials } from '@/hooks/use-initials';
import { send } from '@/routes/verification';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Camera, X } from 'lucide-react';
import { useRef, useState } from 'react';

import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import userProfile from '@/routes/user-profile';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: userProfile.edit().url,
    },
];

export default function Edit({ status }: { status?: string }) {
    const { auth } = usePage<SharedData>().props;
    const getInitials = useInitials();
    const avatarUrl = auth.user.avatar_profile ?? auth.user.avatar ?? undefined;
    const [preview, setPreview] = useState<string | null>(null);
    const [fileName, setFileName] = useState<string | null>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    function handleFileChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];
        if (!file) return;
        setFileName(file.name);
        const url = URL.createObjectURL(file);
        setPreview(url);
    }

    function clearPreview() {
        setPreview(null);
        setFileName(null);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Profile information"
                        description="Update your name and email address"
                    />

                    <Form
                        {...UserProfileController.update.form()}
                        encType="multipart/form-data"
                        method="post"
                        options={{
                            preserveScroll: true,
                        }}
                        className="space-y-6"
                    >
                        {({ processing, recentlySuccessful, errors }) => (
                            <>
                                <input
                                    name="_method"
                                    type="hidden"
                                    value="patch"
                                />
                                <div className="grid gap-2">
                                    <Label>Photo</Label>
                                    <div className="flex items-center gap-5">
                                        {/* Clickable avatar with camera overlay */}
                                        <button
                                            type="button"
                                            onClick={() =>
                                                fileInputRef.current?.click()
                                            }
                                            className="group relative size-20 shrink-0 cursor-pointer rounded-full focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                                            aria-label="Change profile photo"
                                        >
                                            <Avatar className="size-20 overflow-hidden rounded-full transition-opacity duration-200 group-hover:opacity-75">
                                                <AvatarImage
                                                    alt={auth.user.name}
                                                    src={preview ?? avatarUrl}
                                                />
                                                <AvatarFallback className="rounded-full bg-neutral-200 text-2xl text-black dark:bg-neutral-700 dark:text-white">
                                                    {getInitials(
                                                        auth.user.name,
                                                    )}
                                                </AvatarFallback>
                                            </Avatar>
                                            {/* Camera overlay */}
                                            <span className="absolute inset-0 flex items-center justify-center rounded-full bg-black/0 transition-all duration-200 group-hover:bg-black/40">
                                                <Camera className="size-6 text-white opacity-0 drop-shadow transition-opacity duration-200 group-hover:opacity-100" />
                                            </span>
                                        </button>

                                        {/* File info + actions */}
                                        <div className="min-w-0 flex-1">
                                            {fileName ? (
                                                <div className="flex items-center gap-2 rounded-md border border-border bg-muted/50 px-3 py-2 text-sm">
                                                    <span className="min-w-0 flex-1 truncate font-medium text-foreground">
                                                        {fileName}
                                                    </span>
                                                    <button
                                                        type="button"
                                                        onClick={clearPreview}
                                                        className="shrink-0 rounded text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                                                        aria-label="Remove selected photo"
                                                    >
                                                        <X className="size-4" />
                                                    </button>
                                                </div>
                                            ) : (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        fileInputRef.current?.click()
                                                    }
                                                    className="inline-flex cursor-pointer items-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                                >
                                                    <Camera className="size-4 text-muted-foreground" />
                                                    Change photo
                                                </button>
                                            )}
                                            <p className="mt-1.5 text-xs text-muted-foreground">
                                                JPG, PNG, WebP or GIF · Max 2 MB
                                            </p>
                                            <InputError
                                                className="mt-1"
                                                message={errors.avatar}
                                            />
                                        </div>
                                    </div>

                                    {/* Hidden file input */}
                                    <input
                                        ref={fileInputRef}
                                        accept="image/jpeg,image/png,image/webp,image/gif"
                                        id="avatar"
                                        name="avatar"
                                        type="file"
                                        className="sr-only"
                                        onChange={handleFileChange}
                                    />
                                </div>

                                <FormField
                                    label="Name"
                                    htmlFor="name"
                                    error={errors.name}
                                >
                                    <Input
                                        id="name"
                                        className="block w-full"
                                        defaultValue={auth.user.name}
                                        name="name"
                                        required
                                        autoComplete="name"
                                        placeholder="Full name"
                                    />
                                </FormField>

                                <FormField
                                    label="Email address"
                                    htmlFor="email"
                                    description="We'll send a verification link if you change this."
                                    error={errors.email}
                                >
                                    <Input
                                        id="email"
                                        type="email"
                                        className="block w-full"
                                        defaultValue={auth.user.email}
                                        name="email"
                                        required
                                        autoComplete="username"
                                        placeholder="Email address"
                                    />
                                </FormField>

                                <FormField
                                    label="Phone"
                                    htmlFor="phone"
                                    error={errors.phone}
                                >
                                    <Input
                                        id="phone"
                                        type="tel"
                                        className="block w-full"
                                        defaultValue={auth.user.phone ?? ''}
                                        name="phone"
                                        autoComplete="tel"
                                        placeholder="+1 234 567 8900"
                                    />
                                </FormField>

                                {auth.user.email_verified_at === null && (
                                    <div>
                                        <p className="-mt-4 text-sm text-muted-foreground">
                                            Your email address is unverified.{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                            >
                                                Click here to resend the
                                                verification email.
                                            </Link>
                                        </p>

                                        {status ===
                                            'verification-link-sent' && (
                                            <div className="mt-2 text-sm font-medium text-green-600">
                                                A new verification link has been
                                                sent to your email address.
                                            </div>
                                        )}
                                    </div>
                                )}

                                <div className="flex items-center gap-4">
                                    <Button
                                        disabled={processing}
                                        data-test="update-profile-button"
                                    >
                                        Save
                                    </Button>

                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600">
                                            Saved
                                        </p>
                                    </Transition>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
