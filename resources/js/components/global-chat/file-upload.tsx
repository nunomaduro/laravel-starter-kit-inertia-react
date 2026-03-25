import { Paperclip, X } from 'lucide-react';
import { useCallback, useRef, useState } from 'react';
import { toast } from 'sonner';

export interface FileAttachment {
    file: File;
    preview?: string;
}

interface FileUploadProps {
    onChange: (files: FileAttachment[]) => void;
    maxFiles?: number;
    maxSizeMb?: number;
}

const ALLOWED_TYPES = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf',
    'text/plain',
    'text/csv',
    'application/json',
];

export function FileUpload({ onChange, maxFiles = 5, maxSizeMb = 10 }: FileUploadProps) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [attachments, setAttachments] = useState<FileAttachment[]>([]);

    const handleFiles = useCallback(
        (fileList: FileList) => {
            const newAttachments: FileAttachment[] = [];

            for (const file of Array.from(fileList)) {
                if (attachments.length + newAttachments.length >= maxFiles) {
                    toast.error(`Maximum ${maxFiles} files`);
                    break;
                }
                if (file.size > maxSizeMb * 1024 * 1024) {
                    toast.error(`${file.name} exceeds ${maxSizeMb}MB`);
                    continue;
                }
                if (!ALLOWED_TYPES.includes(file.type)) {
                    toast.error(`${file.name}: unsupported type`);
                    continue;
                }

                const attachment: FileAttachment = { file };
                if (file.type.startsWith('image/')) {
                    attachment.preview = URL.createObjectURL(file);
                }
                newAttachments.push(attachment);
            }

            if (newAttachments.length > 0) {
                const updated = [...attachments, ...newAttachments];
                setAttachments(updated);
                onChange(updated);
            }
        },
        [attachments, maxFiles, maxSizeMb, onChange],
    );

    const handleRemove = useCallback(
        (index: number) => {
            setAttachments((prev) => {
                const item = prev[index];
                if (item?.preview) URL.revokeObjectURL(item.preview);
                const updated = prev.filter((_, i) => i !== index);
                onChange(updated);
                return updated;
            });
        },
        [onChange],
    );

    const handleClick = useCallback(() => {
        inputRef.current?.click();
    }, []);

    return (
        <>
            <input
                ref={inputRef}
                type="file"
                multiple
                accept={ALLOWED_TYPES.join(',')}
                className="hidden"
                onChange={(e) => {
                    if (e.target.files) handleFiles(e.target.files);
                    e.target.value = '';
                }}
            />

            {attachments.length > 0 && (
                <div className="mb-2 flex flex-wrap gap-1.5">
                    {attachments.map((a, i) => (
                        <div
                            key={i}
                            className="group relative flex items-center gap-1 rounded-md border bg-muted/50 px-2 py-1"
                        >
                            {a.preview ? (
                                <img
                                    src={a.preview}
                                    alt={a.file.name}
                                    className="size-6 rounded object-cover"
                                />
                            ) : null}
                            <span className="max-w-[100px] truncate text-[10px] text-muted-foreground">
                                {a.file.name}
                            </span>
                            <button
                                type="button"
                                onClick={() => handleRemove(i)}
                                className="rounded-full p-0.5 text-muted-foreground transition-colors hover:text-destructive"
                                aria-label={`Remove ${a.file.name}`}
                            >
                                <X className="size-3" />
                            </button>
                        </div>
                    ))}
                </div>
            )}

            <div className="mb-1.5">
                <button
                    type="button"
                    onClick={handleClick}
                    className="rounded-md p-1 text-muted-foreground transition-colors duration-100 hover:bg-muted hover:text-foreground"
                    aria-label="Attach file"
                    data-pan="global-chat-attach"
                >
                    <Paperclip className="size-3.5" />
                </button>
            </div>
        </>
    );
}
