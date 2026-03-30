import { Button } from '@/components/ui/button';
import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import type { BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';

interface Role {
    id: number;
    name: string;
}

interface UserData {
    id: number;
    hash_id: string;
    name: string;
    email: string;
    phone: string | null;
    role_ids: number[];
    tag_names: string[];
}

interface Props {
    user: UserData;
    roles: Role[];
    tagSuggestions: string[];
}

export default function UsersEdit({ user, roles, tagSuggestions = [] }: Props) {
    const [tags, setTags] = useState<string[]>(user.tag_names ?? []);
    const [tagInput, setTagInput] = useState('');

    const addTag = (tag: string) => {
        const trimmed = tag.trim();
        if (trimmed && !tags.includes(trimmed)) {
            setTags([...tags, trimmed]);
        }
        setTagInput('');
    };

    const removeTag = (tag: string) => {
        setTags(tags.filter((t) => t !== tag));
    };

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Users', href: '/users' },
        { title: user.name, href: `/users/${user.hash_id}` },
        { title: 'Edit', href: `/users/${user.hash_id}/edit` },
    ];

    return (
        <AppSidebarLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${user.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <h2 className="font-mono text-lg font-semibold tracking-tight">Edit user</h2>

                <Form
                    action={`/users/${user.hash_id}`}
                    method="put"
                    disableWhileProcessing
                    className="max-w-lg space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <FormField label="Name" htmlFor="name" error={errors.name} required>
                                <Input id="name" name="name" type="text" defaultValue={user.name} required autoFocus />
                            </FormField>

                            <FormField label="Email" htmlFor="email" error={errors.email} required>
                                <Input id="email" name="email" type="email" defaultValue={user.email} required />
                            </FormField>

                            <FormField label="Phone" htmlFor="phone" error={errors.phone}>
                                <Input id="phone" name="phone" type="tel" defaultValue={user.phone ?? ''} />
                            </FormField>

                            <FormField label="Password" htmlFor="password" error={errors.password}>
                                <Input id="password" name="password" type="password" placeholder="Leave blank to keep current" />
                            </FormField>

                            <FormField label="Roles" htmlFor="roles" error={errors.roles}>
                                <select
                                    id="roles"
                                    name="roles[]"
                                    multiple
                                    defaultValue={user.role_ids.map(String)}
                                    className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                >
                                    {roles.map((role) => (
                                        <option key={role.id} value={role.id}>
                                            {role.name}
                                        </option>
                                    ))}
                                </select>
                            </FormField>

                            <FormField label="Tags" htmlFor="tag_input" error={errors.tag_names}>
                                <div className="space-y-2">
                                    {tags.length > 0 && (
                                        <div className="flex flex-wrap gap-1.5">
                                            {tags.map((tag) => (
                                                <span
                                                    key={tag}
                                                    className="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5 text-xs font-medium"
                                                >
                                                    {tag}
                                                    <button
                                                        type="button"
                                                        onClick={() => removeTag(tag)}
                                                        className="text-muted-foreground hover:text-foreground"
                                                    >
                                                        &times;
                                                    </button>
                                                </span>
                                            ))}
                                        </div>
                                    )}
                                    <Input
                                        id="tag_input"
                                        type="text"
                                        value={tagInput}
                                        onChange={(e) => setTagInput(e.target.value)}
                                        onKeyDown={(e) => {
                                            if (e.key === 'Enter' || e.key === ',') {
                                                e.preventDefault();
                                                addTag(tagInput);
                                            }
                                        }}
                                        placeholder="Type and press Enter"
                                        list="tag-suggestions"
                                    />
                                    {tagSuggestions.length > 0 && (
                                        <datalist id="tag-suggestions">
                                            {tagSuggestions.map((s) => (
                                                <option key={s} value={s} />
                                            ))}
                                        </datalist>
                                    )}
                                    {tags.map((tag) => (
                                        <input key={tag} type="hidden" name="tag_names[]" value={tag} />
                                    ))}
                                </div>
                            </FormField>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Saving...' : 'Save changes'}
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href={`/users/${user.hash_id}`}>Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppSidebarLayout>
    );
}
