import {
    FileIcon,
    FolderIcon,
    FolderOpenIcon,
    GridIcon,
    ListIcon,
    MoreHorizontalIcon,
    Trash2Icon,
    UploadIcon,
} from 'lucide-react';
import * as React from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

export interface FileItem {
    id: string;
    name: string;
    type: 'file' | 'folder';
    size?: number;
    mimeType?: string;
    updatedAt?: Date | string;
    url?: string;
}

export interface FileManagerProps {
    items: FileItem[];
    path?: string[];
    onNavigate?: (folder: FileItem) => void;
    onNavigatePath?: (index: number) => void;
    onUpload?: () => void;
    onDelete?: (item: FileItem) => void;
    onRename?: (item: FileItem, newName: string) => void;
    onCreateFolder?: (name: string) => void;
    onSelect?: (item: FileItem) => void;
    selectedIds?: string[];
    isLoading?: boolean;
    className?: string;
}

function formatBytes(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}

function formatDate(d: Date | string | undefined): string {
    if (!d) return '';
    const date = d instanceof Date ? d : new Date(d);
    return date.toLocaleDateString();
}

function FileManager({
    items,
    path = [],
    onNavigate,
    onNavigatePath,
    onUpload,
    onDelete,
    onRename,
    onCreateFolder,
    onSelect,
    selectedIds = [],
    isLoading = false,
    className,
}: FileManagerProps) {
    const [mode, setMode] = React.useState<'grid' | 'list'>('list');
    const [search, setSearch] = React.useState('');
    const [renamingId, setRenamingId] = React.useState<string | null>(null);
    const [renameValue, setRenameValue] = React.useState('');
    const [showNewFolder, setShowNewFolder] = React.useState(false);
    const [newFolderName, setNewFolderName] = React.useState('');

    const filtered = items.filter((item) =>
        item.name.toLowerCase().includes(search.toLowerCase()),
    );

    const handleRenameSubmit = (item: FileItem) => {
        if (renameValue.trim() && renameValue !== item.name) {
            onRename?.(item, renameValue.trim());
        }
        setRenamingId(null);
        setRenameValue('');
    };

    const handleCreateFolder = () => {
        if (newFolderName.trim()) {
            onCreateFolder?.(newFolderName.trim());
            setNewFolderName('');
            setShowNewFolder(false);
        }
    };

    return (
        <div
            data-slot="file-manager"
            className={cn('flex flex-col gap-3', className)}
        >
            {/* Toolbar */}
            <div className="flex flex-wrap items-center gap-2">
                <nav
                    className="flex items-center gap-1 text-sm"
                    aria-label="breadcrumb"
                >
                    <button
                        type="button"
                        onClick={() => onNavigatePath?.(-1)}
                        className="text-muted-foreground hover:text-foreground"
                    >
                        Home
                    </button>
                    {path.map((segment, i) => (
                        <React.Fragment key={i}>
                            <span className="text-muted-foreground">/</span>
                            <button
                                type="button"
                                onClick={() => onNavigatePath?.(i)}
                                className={cn(
                                    'hover:text-foreground',
                                    i === path.length - 1
                                        ? 'font-medium text-foreground'
                                        : 'text-muted-foreground',
                                )}
                            >
                                {segment}
                            </button>
                        </React.Fragment>
                    ))}
                </nav>

                <div className="ml-auto flex items-center gap-2">
                    <Input
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search files..."
                        className="h-8 w-40 text-sm"
                    />
                    {onCreateFolder && (
                        <Button
                            variant="outline"
                            size="sm"
                            className="h-8"
                            onClick={() => setShowNewFolder(true)}
                        >
                            <FolderIcon className="mr-1.5 size-4" />
                            New folder
                        </Button>
                    )}
                    {onUpload && (
                        <Button size="sm" className="h-8" onClick={onUpload}>
                            <UploadIcon className="mr-1.5 size-4" />
                            Upload
                        </Button>
                    )}
                    <div className="flex items-center gap-0.5 rounded-md border p-0.5">
                        <Button
                            variant={mode === 'list' ? 'secondary' : 'ghost'}
                            size="sm"
                            className="h-6 w-6 p-0"
                            onClick={() => setMode('list')}
                            aria-label="List view"
                        >
                            <ListIcon className="size-3.5" />
                        </Button>
                        <Button
                            variant={mode === 'grid' ? 'secondary' : 'ghost'}
                            size="sm"
                            className="h-6 w-6 p-0"
                            onClick={() => setMode('grid')}
                            aria-label="Grid view"
                        >
                            <GridIcon className="size-3.5" />
                        </Button>
                    </div>
                </div>
            </div>

            {showNewFolder && (
                <div className="flex items-center gap-2 rounded-md border bg-muted/30 px-3 py-2">
                    <FolderIcon className="size-4 text-muted-foreground" />
                    <Input
                        autoFocus
                        value={newFolderName}
                        onChange={(e) => setNewFolderName(e.target.value)}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter') handleCreateFolder();
                            if (e.key === 'Escape') {
                                setShowNewFolder(false);
                                setNewFolderName('');
                            }
                        }}
                        placeholder="Folder name"
                        className="h-7 text-sm"
                    />
                    <Button
                        size="sm"
                        className="h-7"
                        onClick={handleCreateFolder}
                    >
                        Create
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-7"
                        onClick={() => {
                            setShowNewFolder(false);
                            setNewFolderName('');
                        }}
                    >
                        Cancel
                    </Button>
                </div>
            )}

            {isLoading ? (
                <div className="flex h-40 items-center justify-center text-sm text-muted-foreground">
                    Loading...
                </div>
            ) : filtered.length === 0 ? (
                <div className="flex h-40 items-center justify-center text-sm text-muted-foreground">
                    No files found.
                </div>
            ) : mode === 'grid' ? (
                <div className="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                    {filtered.map((item) => (
                        <FileGridItem
                            key={item.id}
                            item={item}
                            selected={selectedIds.includes(item.id)}
                            renamingId={renamingId}
                            renameValue={renameValue}
                            onSelect={onSelect}
                            onNavigate={onNavigate}
                            onDelete={onDelete}
                            onRename={onRename}
                            setRenamingId={setRenamingId}
                            setRenameValue={setRenameValue}
                            onRenameSubmit={handleRenameSubmit}
                        />
                    ))}
                </div>
            ) : (
                <div className="overflow-x-auto rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50">
                            <tr>
                                <th className="px-3 py-2 text-left text-xs font-medium text-muted-foreground">
                                    Name
                                </th>
                                <th className="px-3 py-2 text-left text-xs font-medium text-muted-foreground">
                                    Size
                                </th>
                                <th className="px-3 py-2 text-left text-xs font-medium text-muted-foreground">
                                    Modified
                                </th>
                                <th className="w-10 px-3 py-2" />
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                            {filtered.map((item) => (
                                <FileListRow
                                    key={item.id}
                                    item={item}
                                    selected={selectedIds.includes(item.id)}
                                    renamingId={renamingId}
                                    renameValue={renameValue}
                                    onSelect={onSelect}
                                    onNavigate={onNavigate}
                                    onDelete={onDelete}
                                    onRename={onRename}
                                    setRenamingId={setRenamingId}
                                    setRenameValue={setRenameValue}
                                    onRenameSubmit={handleRenameSubmit}
                                />
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}

interface FileItemComponentProps {
    item: FileItem;
    selected: boolean;
    renamingId: string | null;
    renameValue: string;
    onSelect?: (item: FileItem) => void;
    onNavigate?: (folder: FileItem) => void;
    onDelete?: (item: FileItem) => void;
    onRename?: (item: FileItem, newName: string) => void;
    setRenamingId: (id: string | null) => void;
    setRenameValue: (v: string) => void;
    onRenameSubmit: (item: FileItem) => void;
}

function FileActionsMenu({
    item,
    onDelete,
    onRename,
    setRenamingId,
    setRenameValue,
}: {
    item: FileItem;
    onDelete?: (item: FileItem) => void;
    onRename?: (item: FileItem, newName: string) => void;
    setRenamingId: (id: string | null) => void;
    setRenameValue: (v: string) => void;
}) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className="size-7 opacity-0 group-hover:opacity-100"
                    aria-label="File actions"
                >
                    <MoreHorizontalIcon className="size-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {onRename && (
                    <DropdownMenuItem
                        onClick={() => {
                            setRenamingId(item.id);
                            setRenameValue(item.name);
                        }}
                    >
                        Rename
                    </DropdownMenuItem>
                )}
                {item.url && (
                    <DropdownMenuItem asChild>
                        <a href={item.url} download={item.name}>
                            Download
                        </a>
                    </DropdownMenuItem>
                )}
                {onDelete && (
                    <>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            onClick={() => onDelete(item)}
                            className="text-destructive focus:text-destructive"
                        >
                            <Trash2Icon className="mr-2 size-4" />
                            Delete
                        </DropdownMenuItem>
                    </>
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

function FileGridItem({
    item,
    selected,
    renamingId,
    renameValue,
    onSelect,
    onNavigate,
    onDelete,
    onRename,
    setRenamingId,
    setRenameValue,
    onRenameSubmit,
}: FileItemComponentProps) {
    const isRenaming = renamingId === item.id;

    return (
        <div
            data-slot="file-grid-item"
            className={cn(
                'group relative flex flex-col items-center gap-1.5 rounded-lg border p-3 transition-colors hover:bg-muted/50',
                selected && 'border-primary bg-primary/5',
            )}
            onClick={() =>
                item.type === 'folder' ? onNavigate?.(item) : onSelect?.(item)
            }
            role="button"
            tabIndex={0}
            onKeyDown={(e) => {
                if (e.key === 'Enter') {
                    (item.type === 'folder' ? onNavigate : onSelect)?.(item);
                }
            }}
        >
            <div className="absolute top-1 right-1">
                <FileActionsMenu
                    item={item}
                    onDelete={onDelete}
                    onRename={onRename}
                    setRenamingId={setRenamingId}
                    setRenameValue={setRenameValue}
                />
            </div>
            {item.type === 'folder' ? (
                <FolderOpenIcon className="size-10 text-primary/70" />
            ) : (
                <FileIcon className="size-10 text-muted-foreground" />
            )}
            {isRenaming ? (
                <Input
                    autoFocus
                    value={renameValue}
                    onChange={(e) => setRenameValue(e.target.value)}
                    onBlur={() => onRenameSubmit(item)}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter') onRenameSubmit(item);
                        if (e.key === 'Escape') setRenamingId(null);
                        e.stopPropagation();
                    }}
                    onClick={(e) => e.stopPropagation()}
                    className="h-6 w-full px-1 text-center text-xs"
                />
            ) : (
                <span className="w-full truncate text-center text-xs">
                    {item.name}
                </span>
            )}
            {item.size !== undefined && (
                <Badge variant="secondary" className="px-1 py-0 text-[10px]">
                    {formatBytes(item.size)}
                </Badge>
            )}
        </div>
    );
}

function FileListRow({
    item,
    selected,
    renamingId,
    renameValue,
    onSelect,
    onNavigate,
    onDelete,
    onRename,
    setRenamingId,
    setRenameValue,
    onRenameSubmit,
}: FileItemComponentProps) {
    const isRenaming = renamingId === item.id;

    return (
        <tr
            className={cn(
                'group cursor-pointer transition-colors hover:bg-muted/30',
                selected && 'bg-primary/5',
            )}
            onClick={() =>
                item.type === 'folder' ? onNavigate?.(item) : onSelect?.(item)
            }
        >
            <td className="px-3 py-2">
                <div className="flex items-center gap-2">
                    {item.type === 'folder' ? (
                        <FolderIcon className="size-4 text-primary/70" />
                    ) : (
                        <FileIcon className="size-4 text-muted-foreground" />
                    )}
                    {isRenaming ? (
                        <Input
                            autoFocus
                            value={renameValue}
                            onChange={(e) => setRenameValue(e.target.value)}
                            onBlur={() => onRenameSubmit(item)}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter') onRenameSubmit(item);
                                if (e.key === 'Escape') setRenamingId(null);
                                e.stopPropagation();
                            }}
                            onClick={(e) => e.stopPropagation()}
                            className="h-6 text-sm"
                        />
                    ) : (
                        <span className="text-sm">{item.name}</span>
                    )}
                </div>
            </td>
            <td className="px-3 py-2 text-sm text-muted-foreground">
                {item.size !== undefined ? formatBytes(item.size) : '—'}
            </td>
            <td className="px-3 py-2 text-sm text-muted-foreground">
                {formatDate(item.updatedAt)}
            </td>
            <td
                className="px-3 py-2 text-right"
                onClick={(e) => e.stopPropagation()}
            >
                <FileActionsMenu
                    item={item}
                    onDelete={onDelete}
                    onRename={onRename}
                    setRenamingId={setRenamingId}
                    setRenameValue={setRenameValue}
                />
            </td>
        </tr>
    );
}

export { FileManager };
