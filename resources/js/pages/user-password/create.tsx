import UserPasswordController from '@/actions/App/Http/Controllers/UserPasswordController';
import { Form, Head } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import AuthLayout from '@/layouts/auth-layout';

interface ResetPasswordProps {
    token: string;
    email: string;
}

export default function ResetPassword({ token, email }: ResetPasswordProps) {
    return (
        <AuthLayout
            title="Reset password"
            description="Please enter your new password below"
        >
            <Head title="Reset password" />

            <Form
                {...UserPasswordController.store.form()}
                transform={(data) => ({ ...data, token, email })}
                resetOnSuccess={['password', 'password_confirmation']}
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <FormField
                            label="Email"
                            htmlFor="email"
                            error={errors.email}
                        >
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                autoComplete="email"
                                value={email}
                                className="block w-full"
                                readOnly
                            />
                        </FormField>

                        <FormField
                            label="Password"
                            htmlFor="password"
                            error={errors.password}
                        >
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                autoComplete="new-password"
                                className="block w-full"
                                autoFocus
                                placeholder="Password"
                            />
                        </FormField>

                        <FormField
                            label="Confirm password"
                            htmlFor="password_confirmation"
                            error={errors.password_confirmation}
                        >
                            <Input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                autoComplete="new-password"
                                className="block w-full"
                                placeholder="Confirm password"
                            />
                        </FormField>

                        <Button
                            type="submit"
                            className="mt-4 w-full"
                            disabled={processing}
                            data-test="reset-password-button"
                        >
                            {processing && (
                                <LoaderCircle className="h-4 w-4 animate-spin" />
                            )}
                            Reset password
                        </Button>
                    </div>
                )}
            </Form>
        </AuthLayout>
    );
}
