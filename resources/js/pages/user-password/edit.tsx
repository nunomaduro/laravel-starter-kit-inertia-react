import UserPasswordController from '@/actions/App/Http/Controllers/UserPasswordController';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head } from '@inertiajs/react';
import { useRef } from 'react';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { edit } from '@/routes/password';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: edit().url,
    },
];

export default function Password() {
    const passwordInputRef = useRef<HTMLInputElement>(null);
    const currentPasswordInputRef = useRef<HTMLInputElement>(null);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Password settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Update password"
                        description="Ensure your account is using a long, random password to stay secure"
                    />

                    <Form
                        {...UserPasswordController.update.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        resetOnError={[
                            'password',
                            'password_confirmation',
                            'current_password',
                        ]}
                        resetOnSuccess
                        onError={(errors) => {
                            if (errors.password) {
                                passwordInputRef.current?.focus();
                            }

                            if (errors.current_password) {
                                currentPasswordInputRef.current?.focus();
                            }
                        }}
                        className="space-y-6"
                    >
                        {({ errors, processing, recentlySuccessful }) => (
                            <>
                                <FormField
                                    label="Current password"
                                    htmlFor="current_password"
                                    error={errors.current_password}
                                >
                                    <Input
                                        id="current_password"
                                        ref={currentPasswordInputRef}
                                        name="current_password"
                                        type="password"
                                        className="block w-full"
                                        autoComplete="current-password"
                                        placeholder="Current password"
                                    />
                                </FormField>

                                <FormField
                                    label="New password"
                                    htmlFor="password"
                                    error={errors.password}
                                >
                                    <Input
                                        id="password"
                                        ref={passwordInputRef}
                                        name="password"
                                        type="password"
                                        className="block w-full"
                                        autoComplete="new-password"
                                        placeholder="New password"
                                    />
                                </FormField>

                                <FormField
                                    label="Confirm password"
                                    htmlFor="password_confirmation"
                                    error={errors.password_confirmation}
                                >
                                    <Input
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        className="block w-full"
                                        autoComplete="new-password"
                                        placeholder="Confirm password"
                                    />
                                </FormField>

                                <div className="flex items-center gap-4">
                                    <Button
                                        disabled={processing}
                                        data-test="update-password-button"
                                    >
                                        Save password
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
            </SettingsLayout>
        </AppLayout>
    );
}
