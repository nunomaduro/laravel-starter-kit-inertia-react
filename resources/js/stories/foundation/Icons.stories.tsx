import type { Meta, StoryObj } from '@storybook/react';
import {
    ActivityIcon,
    AlertCircleIcon,
    AlertTriangleIcon,
    ArrowRightIcon,
    BellIcon,
    BookOpenIcon,
    CheckCircleIcon,
    ChevronDownIcon,
    ChevronRightIcon,
    CircleIcon,
    ClockIcon,
    CloudIcon,
    CopyIcon,
    CreditCardIcon,
    DatabaseIcon,
    EditIcon,
    ExternalLinkIcon,
    EyeIcon,
    FileIcon,
    FilterIcon,
    FolderIcon,
    GlobeIcon,
    GridIcon,
    HeartIcon,
    HomeIcon,
    InfoIcon,
    KeyIcon,
    LayoutIcon,
    LinkIcon,
    LoaderIcon,
    LockIcon,
    LogOutIcon,
    MailIcon,
    MenuIcon,
    MessageSquareIcon,
    MoonIcon,
    MoreHorizontalIcon,
    PlusIcon,
    RefreshCwIcon,
    SearchIcon,
    SendIcon,
    SettingsIcon,
    ShareIcon,
    ShieldIcon,
    StarIcon,
    SunIcon,
    TrashIcon,
    TrendingUpIcon,
    UploadIcon,
    UserIcon,
    UsersIcon,
    XIcon,
    ZapIcon,
} from 'lucide-react';
import React from 'react';

const ICONS = [
    { name: 'Activity', Icon: ActivityIcon },
    { name: 'AlertCircle', Icon: AlertCircleIcon },
    { name: 'AlertTriangle', Icon: AlertTriangleIcon },
    { name: 'ArrowRight', Icon: ArrowRightIcon },
    { name: 'Bell', Icon: BellIcon },
    { name: 'BookOpen', Icon: BookOpenIcon },
    { name: 'CheckCircle', Icon: CheckCircleIcon },
    { name: 'ChevronDown', Icon: ChevronDownIcon },
    { name: 'ChevronRight', Icon: ChevronRightIcon },
    { name: 'Circle', Icon: CircleIcon },
    { name: 'Clock', Icon: ClockIcon },
    { name: 'Cloud', Icon: CloudIcon },
    { name: 'Copy', Icon: CopyIcon },
    { name: 'CreditCard', Icon: CreditCardIcon },
    { name: 'Database', Icon: DatabaseIcon },
    { name: 'Edit', Icon: EditIcon },
    { name: 'ExternalLink', Icon: ExternalLinkIcon },
    { name: 'Eye', Icon: EyeIcon },
    { name: 'File', Icon: FileIcon },
    { name: 'Filter', Icon: FilterIcon },
    { name: 'Folder', Icon: FolderIcon },
    { name: 'Globe', Icon: GlobeIcon },
    { name: 'Grid', Icon: GridIcon },
    { name: 'Heart', Icon: HeartIcon },
    { name: 'Home', Icon: HomeIcon },
    { name: 'Info', Icon: InfoIcon },
    { name: 'Key', Icon: KeyIcon },
    { name: 'Layout', Icon: LayoutIcon },
    { name: 'Link', Icon: LinkIcon },
    { name: 'Loader', Icon: LoaderIcon },
    { name: 'Lock', Icon: LockIcon },
    { name: 'LogOut', Icon: LogOutIcon },
    { name: 'Mail', Icon: MailIcon },
    { name: 'Menu', Icon: MenuIcon },
    { name: 'MessageSquare', Icon: MessageSquareIcon },
    { name: 'Moon', Icon: MoonIcon },
    { name: 'MoreHorizontal', Icon: MoreHorizontalIcon },
    { name: 'Plus', Icon: PlusIcon },
    { name: 'RefreshCw', Icon: RefreshCwIcon },
    { name: 'Search', Icon: SearchIcon },
    { name: 'Send', Icon: SendIcon },
    { name: 'Settings', Icon: SettingsIcon },
    { name: 'Share', Icon: ShareIcon },
    { name: 'Shield', Icon: ShieldIcon },
    { name: 'Star', Icon: StarIcon },
    { name: 'Sun', Icon: SunIcon },
    { name: 'Trash', Icon: TrashIcon },
    { name: 'TrendingUp', Icon: TrendingUpIcon },
    { name: 'Upload', Icon: UploadIcon },
    { name: 'User', Icon: UserIcon },
    { name: 'Users', Icon: UsersIcon },
    { name: 'X', Icon: XIcon },
    { name: 'Zap', Icon: ZapIcon },
];

function IconsDemo() {
    return (
        <div className="p-6 bg-background text-foreground">
            <h2 className="text-lg font-semibold mb-1">Lucide Icons</h2>
            <p className="text-sm text-muted-foreground mb-6">
                This application uses <strong>lucide-react</strong> for all icons.
            </p>
            <div className="grid grid-cols-4 gap-3 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10">
                {ICONS.map(({ name, Icon }) => (
                    <div
                        key={name}
                        className="flex flex-col items-center gap-1.5 rounded-md border border-border p-3 hover:bg-muted transition-colors"
                        title={name}
                    >
                        <Icon className="size-5 text-foreground" />
                        <span className="text-[10px] text-muted-foreground text-center leading-tight">{name}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}

const meta: Meta = {
    title: 'Foundation/Icons',
    component: IconsDemo,
    tags: ['autodocs'],
    parameters: { layout: 'fullscreen' },
};

export default meta;

export const AllIcons: StoryObj = {
    render: () => <IconsDemo />,
};
