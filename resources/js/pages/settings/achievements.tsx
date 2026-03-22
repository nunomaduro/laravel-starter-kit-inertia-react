import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const achievementsUrl = () => '/settings/achievements';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Level & achievements',
        href: achievementsUrl(),
    },
];

interface AchievementItem {
    id: number;
    name: string;
    description: string | null;
    image: string | null;
    is_secret: boolean;
    progress: number | null;
    unlocked_at: string | null;
}

interface Props {
    level: number;
    points: number;
    next_level_percentage: number;
    achievements: AchievementItem[];
}

export default function Achievements({
    level,
    points,
    next_level_percentage,
    achievements,
}: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Level & achievements" />
            <SettingsLayout>
                <div className="space-y-8">
                    <HeadingSmall
                        title="Level & achievements"
                        description="Your experience points, level, and unlocked achievements"
                    />

                    <div className="rounded-lg border bg-card p-4 text-card-foreground shadow-sm">
                        <div className="flex flex-wrap items-center gap-4">
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Level
                                </p>
                                <p className="text-2xl font-mono font-semibold tabular-nums">
                                    {level}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    XP
                                </p>
                                <p className="text-2xl font-mono font-semibold tabular-nums">
                                    {points}
                                </p>
                            </div>
                            {next_level_percentage < 100 && (
                                <div className="min-w-[120px] flex-1">
                                    <p className="mb-1 text-sm text-muted-foreground">
                                        Progress to next level
                                    </p>
                                    <div className="h-2 w-full overflow-hidden rounded-full bg-muted">
                                        <div
                                            className="h-full rounded-full bg-primary transition-all"
                                            style={{
                                                width: `${next_level_percentage}%`,
                                            }}
                                        />
                                    </div>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        {next_level_percentage}%
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    <div>
                        <h3 className="mb-3 text-sm font-medium">
                            Unlocked achievements
                        </h3>
                        {achievements.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No achievements yet. Complete onboarding and
                                other actions to earn some.
                            </p>
                        ) : (
                            <ul className="space-y-3">
                                {achievements.map((a) => (
                                    <li
                                        key={a.id}
                                        className="flex items-start gap-3 rounded-lg border p-3"
                                    >
                                        {a.image ? (
                                            <img
                                                src={a.image}
                                                alt=""
                                                className="h-10 w-10 rounded object-cover"
                                            />
                                        ) : (
                                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-muted text-lg">
                                                🏆
                                            </div>
                                        )}
                                        <div className="min-w-0 flex-1">
                                            <p className="font-medium">
                                                {a.name}
                                            </p>
                                            {a.description && (
                                                <p className="text-sm text-muted-foreground">
                                                    {a.description}
                                                </p>
                                            )}
                                            {a.unlocked_at && (
                                                <p className="mt-1 text-xs text-muted-foreground">
                                                    Unlocked{' '}
                                                    {new Date(
                                                        a.unlocked_at,
                                                    ).toLocaleDateString()}
                                                </p>
                                            )}
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
