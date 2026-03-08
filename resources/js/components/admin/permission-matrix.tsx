import * as React from 'react';

import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { cn } from '@/lib/utils';

export interface PermissionRole {
    id: string;
    name: string;
    description?: string;
    color?: string;
}

export interface Permission {
    id: string;
    name: string;
    resource: string;
    description?: string;
}

export type PermissionGrant = Record<string, Record<string, boolean>>;

interface PermissionMatrixProps {
    roles: PermissionRole[];
    permissions: Permission[];
    grants: PermissionGrant;
    onChange?: (roleId: string, permissionId: string, value: boolean) => void;
    readonly?: boolean;
    className?: string;
}

function groupPermissions(
    permissions: Permission[],
): Record<string, Permission[]> {
    return permissions.reduce<Record<string, Permission[]>>((groups, perm) => {
        const key = perm.resource;
        if (!groups[key]) {
            groups[key] = [];
        }
        groups[key].push(perm);
        return groups;
    }, {});
}

function PermissionMatrix({
    roles,
    permissions,
    grants,
    onChange,
    readonly = false,
    className,
}: PermissionMatrixProps) {
    const grouped = groupPermissions(permissions);
    const resources = Object.keys(grouped);

    const isGranted = (roleId: string, permissionId: string): boolean => {
        return grants[roleId]?.[permissionId] ?? false;
    };

    const handleChange = (
        roleId: string,
        permissionId: string,
        checked: boolean,
    ) => {
        if (!readonly && onChange) {
            onChange(roleId, permissionId, checked);
        }
    };

    return (
        <Card className={cn('overflow-hidden', className)}>
            <CardHeader>
                <CardTitle className="text-base">Permission Matrix</CardTitle>
                <CardDescription>
                    {readonly
                        ? 'View role permissions across all resources.'
                        : 'Configure which roles have access to each permission.'}
                </CardDescription>
            </CardHeader>
            <CardContent className="p-0">
                <div className="w-full overflow-auto">
                    <div className="min-w-max">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b bg-muted/50">
                                    <th className="sticky left-0 z-10 bg-muted/50 px-4 py-3 text-left font-medium text-muted-foreground">
                                        Permission
                                    </th>
                                    {roles.map((role) => (
                                        <th
                                            key={role.id}
                                            className="min-w-28 px-4 py-3 text-center font-medium"
                                        >
                                            <div className="flex flex-col items-center gap-1">
                                                <Badge
                                                    variant="secondary"
                                                    className="text-xs"
                                                    style={
                                                        role.color
                                                            ? {
                                                                  backgroundColor:
                                                                      role.color +
                                                                      '20',
                                                                  color: role.color,
                                                              }
                                                            : undefined
                                                    }
                                                >
                                                    {role.name}
                                                </Badge>
                                                {role.description && (
                                                    <span className="text-xs font-normal text-muted-foreground">
                                                        {role.description}
                                                    </span>
                                                )}
                                            </div>
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {resources.map((resource) => (
                                    <React.Fragment key={resource}>
                                        <tr className="border-b bg-muted/20">
                                            <td
                                                colSpan={roles.length + 1}
                                                className="sticky left-0 px-4 py-2"
                                            >
                                                <span className="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                                    {resource}
                                                </span>
                                            </td>
                                        </tr>
                                        {grouped[resource]?.map(
                                            (permission) => (
                                                <tr
                                                    key={permission.id}
                                                    className="border-b transition-colors hover:bg-muted/30"
                                                >
                                                    <td className="sticky left-0 bg-background px-4 py-3">
                                                        <div>
                                                            <span className="font-medium">
                                                                {
                                                                    permission.name
                                                                }
                                                            </span>
                                                            {permission.description && (
                                                                <p className="text-xs text-muted-foreground">
                                                                    {
                                                                        permission.description
                                                                    }
                                                                </p>
                                                            )}
                                                        </div>
                                                    </td>
                                                    {roles.map((role) => (
                                                        <td
                                                            key={role.id}
                                                            className="px-4 py-3 text-center"
                                                        >
                                                            <Checkbox
                                                                checked={isGranted(
                                                                    role.id,
                                                                    permission.id,
                                                                )}
                                                                onCheckedChange={(
                                                                    checked,
                                                                ) =>
                                                                    handleChange(
                                                                        role.id,
                                                                        permission.id,
                                                                        Boolean(
                                                                            checked,
                                                                        ),
                                                                    )
                                                                }
                                                                disabled={
                                                                    readonly
                                                                }
                                                                className={cn(
                                                                    readonly &&
                                                                        'pointer-events-none',
                                                                )}
                                                                aria-label={`${role.name} - ${permission.name}`}
                                                            />
                                                        </td>
                                                    ))}
                                                </tr>
                                            ),
                                        )}
                                    </React.Fragment>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

export { PermissionMatrix };
