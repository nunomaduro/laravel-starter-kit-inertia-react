import { Link, usePage } from '@inertiajs/react';
import { Menu, Search } from 'lucide-react';
import { useState } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { useInitials } from '@/hooks/use-initials';
import type { User } from '@/types';

export default function PublicHeader() {
    const { auth } = usePage<{ auth: { user: User | null } }>().props;
    const user = auth?.user;
    const getInitials = useInitials();
    const [mobileOpen, setMobileOpen] = useState(false);

    return (
        <header className="sticky top-0 z-50 w-full border-b bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60 dark:bg-neutral-950/95 dark:supports-[backdrop-filter]:bg-neutral-950/60">
            <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" className="flex items-center gap-2">
                    <div className="flex size-8 items-center justify-center rounded-md bg-primary text-primary-foreground">
                        <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
                    </div>
                    <span className="text-lg font-semibold">StayBooker</span>
                </Link>

                <nav className="hidden items-center gap-6 md:flex">
                    <Link
                        href="/search"
                        className="flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <Search className="size-4" />
                        Search
                    </Link>
                </nav>

                <div className="hidden items-center gap-3 md:flex">
                    {user ? (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" className="relative size-9 rounded-full">
                                    <Avatar className="size-9">
                                        <AvatarImage src={user.avatar} alt={user.name} />
                                        <AvatarFallback className="bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {getInitials(user.name)}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" className="w-56">
                                <div className="flex items-center gap-2 px-2 py-1.5">
                                    <div className="grid flex-1 text-left text-sm leading-tight">
                                        <span className="truncate font-medium">{user.name}</span>
                                        <span className="truncate text-xs text-muted-foreground">{user.email}</span>
                                    </div>
                                </div>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem asChild>
                                    <Link href="/dashboard" className="w-full cursor-pointer">
                                        Dashboard
                                    </Link>
                                </DropdownMenuItem>
                                {user.role === 'host' && (
                                    <DropdownMenuItem asChild>
                                        <Link href="/host/dashboard" className="w-full cursor-pointer">
                                            Host Dashboard
                                        </Link>
                                    </DropdownMenuItem>
                                )}
                                <DropdownMenuSeparator />
                                <DropdownMenuItem asChild>
                                    <Link href="/logout" as="button" className="w-full cursor-pointer">
                                        Log out
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    ) : (
                        <>
                            <Button variant="ghost" asChild>
                                <Link href="/login">Log in</Link>
                            </Button>
                            <Button asChild>
                                <Link href="/register">Sign up</Link>
                            </Button>
                        </>
                    )}
                </div>

                <Button
                    variant="ghost"
                    size="icon"
                    className="md:hidden"
                    onClick={() => setMobileOpen(true)}
                >
                    <Menu className="size-5" />
                    <span className="sr-only">Open menu</span>
                </Button>

                <Sheet open={mobileOpen} onOpenChange={setMobileOpen}>
                    <SheetContent side="right">
                        <SheetHeader>
                            <SheetTitle>Menu</SheetTitle>
                        </SheetHeader>
                        <nav className="flex flex-col gap-4 px-4 pt-4">
                            <Link
                                href="/search"
                                className="flex items-center gap-2 text-sm font-medium"
                                onClick={() => setMobileOpen(false)}
                            >
                                <Search className="size-4" />
                                Search
                            </Link>
                            {user ? (
                                <>
                                    <Link
                                        href="/dashboard"
                                        className="text-sm font-medium"
                                        onClick={() => setMobileOpen(false)}
                                    >
                                        Dashboard
                                    </Link>
                                    {user.role === 'host' && (
                                        <Link
                                            href="/host/dashboard"
                                            className="text-sm font-medium"
                                            onClick={() => setMobileOpen(false)}
                                        >
                                            Host Dashboard
                                        </Link>
                                    )}
                                    <Link
                                        href="/logout"
                                        as="button"
                                        className="text-left text-sm font-medium text-destructive"
                                        onClick={() => setMobileOpen(false)}
                                    >
                                        Log out
                                    </Link>
                                </>
                            ) : (
                                <>
                                    <Link
                                        href="/login"
                                        className="text-sm font-medium"
                                        onClick={() => setMobileOpen(false)}
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        href="/register"
                                        className="text-sm font-medium"
                                        onClick={() => setMobileOpen(false)}
                                    >
                                        Sign up
                                    </Link>
                                </>
                            )}
                        </nav>
                    </SheetContent>
                </Sheet>
            </div>
        </header>
    );
}
