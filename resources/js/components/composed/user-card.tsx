import * as React from 'react';
import { MailIcon, MessageSquareIcon, UserPlusIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

export interface UserCardUser {
    id: string;
    name: string;
    email?: string;
    role?: string;
    avatar?: string;
    initials?: string;
    bio?: string;
    status?: 'online' | 'offline' | 'away' | 'busy';
    badges?: string[];
    stats?: { label: string; value: string | number }[];
}

export interface UserCardProps {
    user: UserCardUser;
    variant?: 'compact' | 'default' | 'detailed';
    onMessage?: (user: UserCardUser) => void;
    onFollow?: (user: UserCardUser) => void;
    onEmail?: (user: UserCardUser) => void;
    isFollowing?: boolean;
    className?: string;
    children?: React.ReactNode;
}

const statusColors: Record<NonNullable<UserCardUser['status']>, string> = {
    online: 'bg-green-500',
    offline: 'bg-gray-400',
    away: 'bg-amber-400',
    busy: 'bg-red-500',
};

function getInitials(name: string): string {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
}

function UserCard({
    user,
    variant = 'default',
    onMessage,
    onFollow,
    onEmail,
    isFollowing = false,
    className,
    children,
}: UserCardProps) {
    if (variant === 'compact') {
        return (
            <div
                data-slot="user-card"
                className={cn('flex items-center gap-3 rounded-lg p-2 hover:bg-muted/50', className)}
            >
                <div className="relative shrink-0">
                    <Avatar className="size-8">
                        <AvatarImage src={user.avatar} alt={user.name} />
                        <AvatarFallback className="text-xs">
                            {user.initials ?? getInitials(user.name)}
                        </AvatarFallback>
                    </Avatar>
                    {user.status && (
                        <span
                            className={cn(
                                'absolute bottom-0 right-0 size-2.5 rounded-full border-2 border-background',
                                statusColors[user.status],
                            )}
                        />
                    )}
                </div>
                <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-medium">{user.name}</p>
                    {(user.role || user.email) && (
                        <p className="truncate text-xs text-muted-foreground">
                            {user.role ?? user.email}
                        </p>
                    )}
                </div>
                {children}
            </div>
        );
    }

    return (
        <Card data-slot="user-card" className={cn('overflow-hidden', className)}>
            {variant === 'detailed' && (
                <div className="h-20 bg-gradient-to-r from-primary/20 to-primary/5" />
            )}
            <CardContent className={cn('p-4', variant === 'detailed' && '-mt-10')}>
                <div className={cn('flex items-start gap-3', variant === 'detailed' && 'items-end')}>
                    <div className="relative shrink-0">
                        <Avatar
                            className={cn(
                                'border-2 border-background',
                                variant === 'detailed' ? 'size-16' : 'size-10',
                            )}
                        >
                            <AvatarImage src={user.avatar} alt={user.name} />
                            <AvatarFallback
                                className={variant === 'detailed' ? 'text-lg' : 'text-sm'}
                            >
                                {user.initials ?? getInitials(user.name)}
                            </AvatarFallback>
                        </Avatar>
                        {user.status && (
                            <span
                                className={cn(
                                    'absolute bottom-0.5 right-0.5 rounded-full border-2 border-background',
                                    variant === 'detailed' ? 'size-4' : 'size-3',
                                    statusColors[user.status],
                                )}
                            />
                        )}
                    </div>
                    <div className="min-w-0 flex-1">
                        <div className="flex flex-wrap items-center gap-1.5">
                            <h3
                                className={cn(
                                    'font-semibold leading-tight',
                                    variant === 'detailed' ? 'text-base' : 'text-sm',
                                )}
                            >
                                {user.name}
                            </h3>
                            {user.badges?.map((badge) => (
                                <Badge key={badge} variant="secondary" className="px-1.5 py-0 text-[10px]">
                                    {badge}
                                </Badge>
                            ))}
                        </div>
                        {user.role && (
                            <p className="text-xs text-muted-foreground">{user.role}</p>
                        )}
                        {user.email && variant === 'detailed' && (
                            <p className="text-xs text-muted-foreground">{user.email}</p>
                        )}
                    </div>
                </div>

                {user.bio && variant === 'detailed' && (
                    <p className="mt-3 text-sm text-muted-foreground">{user.bio}</p>
                )}

                {user.stats && user.stats.length > 0 && (
                    <div className="mt-3 flex divide-x divide-border">
                        {user.stats.map((stat) => (
                            <div key={stat.label} className="flex-1 px-3 first:pl-0 last:pr-0 text-center">
                                <p className="text-sm font-semibold">{stat.value}</p>
                                <p className="text-[10px] text-muted-foreground">{stat.label}</p>
                            </div>
                        ))}
                    </div>
                )}

                {(onMessage || onFollow || onEmail || children) && (
                    <div className="mt-3 flex flex-wrap items-center gap-2">
                        {onFollow && (
                            <Button
                                size="sm"
                                variant={isFollowing ? 'outline' : 'default'}
                                className="h-7 flex-1 text-xs"
                                onClick={() => onFollow(user)}
                            >
                                <UserPlusIcon className="mr-1 size-3.5" />
                                {isFollowing ? 'Following' : 'Follow'}
                            </Button>
                        )}
                        {onMessage && (
                            <Button
                                size="sm"
                                variant="outline"
                                className="h-7 flex-1 text-xs"
                                onClick={() => onMessage(user)}
                            >
                                <MessageSquareIcon className="mr-1 size-3.5" />
                                Message
                            </Button>
                        )}
                        {onEmail && (
                            <Button
                                size="sm"
                                variant="outline"
                                className="h-7 px-2"
                                onClick={() => onEmail(user)}
                                aria-label="Send email"
                            >
                                <MailIcon className="size-3.5" />
                            </Button>
                        )}
                        {children}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

export { UserCard };
