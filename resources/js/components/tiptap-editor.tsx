import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import {
    Bold,
    Italic,
    List,
    ListOrdered,
    Heading2,
    Heading3,
    Link as LinkIcon,
    Undo,
    Redo,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { useEffect } from 'react';

interface TiptapEditorProps {
    content: string;
    onChange: (html: string) => void;
    placeholder?: string;
    className?: string;
    variables?: Record<string, Record<string, string>>;
}

export default function TiptapEditor({
    content,
    onChange,
    placeholder = 'Start typing...',
    className,
    variables,
}: TiptapEditorProps) {
    const editor = useEditor({
        extensions: [
            StarterKit,
            Link.configure({ openOnClick: false }),
            Placeholder.configure({ placeholder }),
        ],
        content,
        onUpdate: ({ editor }) => {
            onChange(editor.getHTML());
        },
        editorProps: {
            attributes: {
                class: 'prose prose-sm dark:prose-invert max-w-none focus:outline-none min-h-[200px] px-3 py-2',
            },
        },
    });

    useEffect(() => {
        if (editor && content !== editor.getHTML()) {
            editor.commands.setContent(content);
        }
    }, [content, editor]);

    if (!editor) return null;

    const insertVariable = (variable: string) => {
        editor.chain().focus().insertContent(`{{ ${variable} }}`).run();
    };

    return (
        <div className={cn('rounded-md border', className)}>
            {/* Toolbar */}
            <div className="flex flex-wrap items-center gap-0.5 border-b px-2 py-1">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-xs"
                    onClick={() => editor.chain().focus().toggleBold().run()}
                    data-active={editor.isActive('bold') || undefined}
                >
                    <Bold className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-xs"
                    onClick={() => editor.chain().focus().toggleItalic().run()}
                    data-active={editor.isActive('italic') || undefined}
                >
                    <Italic className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-xs"
                    onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()}
                    data-active={editor.isActive('heading', { level: 2 }) || undefined}
                >
                    <Heading2 className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-xs"
                    onClick={() => editor.chain().focus().toggleHeading({ level: 3 }).run()}
                    data-active={editor.isActive('heading', { level: 3 }) || undefined}
                >
                    <Heading3 className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-xs"
                    onClick={() => editor.chain().focus().toggleBulletList().run()}
                    data-active={editor.isActive('bulletList') || undefined}
                >
                    <List className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-xs"
                    onClick={() => editor.chain().focus().toggleOrderedList().run()}
                    data-active={editor.isActive('orderedList') || undefined}
                >
                    <ListOrdered className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-xs"
                    onClick={() => {
                        const url = window.prompt('Enter URL');
                        if (url) editor.chain().focus().setLink({ href: url }).run();
                    }}
                    data-active={editor.isActive('link') || undefined}
                >
                    <LinkIcon className="size-3.5" />
                </Button>
                <div className="bg-border mx-1 h-4 w-px" />
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-xs"
                    onClick={() => editor.chain().focus().undo().run()}
                    disabled={!editor.can().undo()}
                >
                    <Undo className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-xs"
                    onClick={() => editor.chain().focus().redo().run()}
                    disabled={!editor.can().redo()}
                >
                    <Redo className="size-3.5" />
                </Button>
            </div>

            {/* Variable insertion toolbar */}
            {variables && Object.keys(variables).length > 0 && (
                <div className="flex flex-wrap items-center gap-1 border-b px-2 py-1.5">
                    <span className="text-muted-foreground mr-1 text-xs">Variables:</span>
                    {Object.entries(variables).map(([group, vars]) => (
                        <div key={group} className="flex items-center gap-1">
                            {Object.entries(vars).map(([variable, label]) => (
                                <Button
                                    key={variable}
                                    type="button"
                                    variant="outline"
                                    size="xs"
                                    className="font-mono"
                                    onClick={() => insertVariable(variable)}
                                    title={label}
                                >
                                    {variable}
                                </Button>
                            ))}
                        </div>
                    ))}
                </div>
            )}

            {/* Editor content */}
            <EditorContent editor={editor} />
        </div>
    );
}
