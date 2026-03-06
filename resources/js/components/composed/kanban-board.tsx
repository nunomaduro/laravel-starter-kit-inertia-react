import * as React from 'react';
import { PlusIcon } from 'lucide-react';

import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Kanban, type KanbanCard, type KanbanColumn } from '@/components/ui/kanban';

export interface KanbanBoardProps {
    columns: KanbanColumn[];
    onChange?: (columns: KanbanColumn[]) => void;
    onAddCard?: (columnId: string, card: Omit<KanbanCard, 'id'>) => void;
    onAddColumn?: (title: string) => void;
    renderCard?: (card: KanbanCard, column: KanbanColumn) => React.ReactNode;
    title?: string;
    className?: string;
    allowAddColumn?: boolean;
    allowAddCard?: boolean;
}

function KanbanBoard({
    columns,
    onChange,
    onAddCard,
    onAddColumn,
    renderCard,
    title,
    className,
    allowAddColumn = false,
    allowAddCard = false,
}: KanbanBoardProps) {
    const [addingCard, setAddingCard] = React.useState<string | null>(null);
    const [newCardTitle, setNewCardTitle] = React.useState('');
    const [addingColumn, setAddingColumn] = React.useState(false);
    const [newColumnTitle, setNewColumnTitle] = React.useState('');

    const handleAddCard = (columnId: string) => {
        if (!newCardTitle.trim()) {
            setAddingCard(null);
            return;
        }
        onAddCard?.(columnId, { title: newCardTitle.trim() });
        setNewCardTitle('');
        setAddingCard(null);
    };

    const handleAddColumn = () => {
        if (!newColumnTitle.trim()) {
            setAddingColumn(false);
            return;
        }
        onAddColumn?.(newColumnTitle.trim());
        setNewColumnTitle('');
        setAddingColumn(false);
    };

    return (
        <div data-slot="kanban-board" className={cn('flex flex-col gap-3', className)}>
            {title && (
                <div className="flex items-center justify-between">
                    <h2 className="text-lg font-semibold">{title}</h2>
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        {columns.reduce((acc, col) => acc + col.cards.length, 0)} cards across{' '}
                        {columns.length} columns
                    </div>
                </div>
            )}

            <div className="flex gap-4 overflow-x-auto pb-2">
                <Kanban columns={columns} onChange={onChange} renderCard={renderCard} />

                {allowAddCard && (
                    <div className="flex shrink-0 flex-col gap-2">
                        {columns.map((col) => (
                            <div key={col.id}>
                                {addingCard === col.id ? (
                                    <div className="w-72 rounded-lg border bg-card p-3 shadow-sm">
                                        <Input
                                            autoFocus
                                            value={newCardTitle}
                                            onChange={(e) => setNewCardTitle(e.target.value)}
                                            placeholder="Card title..."
                                            className="mb-2 h-7 text-sm"
                                            onKeyDown={(e) => {
                                                if (e.key === 'Enter') handleAddCard(col.id);
                                                if (e.key === 'Escape') {
                                                    setAddingCard(null);
                                                    setNewCardTitle('');
                                                }
                                            }}
                                        />
                                        <div className="flex gap-1">
                                            <Button
                                                size="sm"
                                                className="h-6 text-xs"
                                                onClick={() => handleAddCard(col.id)}
                                            >
                                                Add
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                className="h-6 text-xs"
                                                onClick={() => {
                                                    setAddingCard(null);
                                                    setNewCardTitle('');
                                                }}
                                            >
                                                Cancel
                                            </Button>
                                        </div>
                                    </div>
                                ) : null}
                            </div>
                        ))}
                    </div>
                )}

                {allowAddColumn && (
                    <>
                        {addingColumn ? (
                            <div className="flex w-72 shrink-0 flex-col gap-2 rounded-xl bg-muted/50 p-3">
                                <Input
                                    autoFocus
                                    value={newColumnTitle}
                                    onChange={(e) => setNewColumnTitle(e.target.value)}
                                    placeholder="Column title..."
                                    className="h-7 text-sm"
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') handleAddColumn();
                                        if (e.key === 'Escape') {
                                            setAddingColumn(false);
                                            setNewColumnTitle('');
                                        }
                                    }}
                                />
                                <div className="flex gap-1">
                                    <Button
                                        size="sm"
                                        className="h-6 text-xs"
                                        onClick={handleAddColumn}
                                    >
                                        Add column
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        className="h-6 text-xs"
                                        onClick={() => {
                                            setAddingColumn(false);
                                            setNewColumnTitle('');
                                        }}
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </div>
                        ) : (
                            <Button
                                variant="outline"
                                className="h-auto w-72 shrink-0 justify-start rounded-xl border-dashed py-3 text-muted-foreground"
                                onClick={() => setAddingColumn(true)}
                            >
                                <PlusIcon className="mr-2 size-4" />
                                Add column
                            </Button>
                        )}
                    </>
                )}
            </div>

            {allowAddCard && (
                <div className="flex gap-4 overflow-x-auto">
                    {columns.map((col) => (
                        <div key={col.id} className="w-72 shrink-0">
                            {addingCard !== col.id && (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="w-full justify-start text-xs text-muted-foreground"
                                    onClick={() => {
                                        setAddingCard(col.id);
                                        setNewCardTitle('');
                                    }}
                                >
                                    <PlusIcon className="mr-1.5 size-3.5" />
                                    Add card to {col.title}
                                </Button>
                            )}
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

export { KanbanBoard };
