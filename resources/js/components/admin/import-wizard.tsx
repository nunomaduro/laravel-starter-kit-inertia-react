import {
    AlertCircleIcon,
    CheckCircleIcon,
    ChevronRightIcon,
    FileTextIcon,
    Loader2Icon,
    XCircleIcon,
} from 'lucide-react';
import * as React from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { FileDropzone } from '@/components/ui/file-dropzone';
import { Progress } from '@/components/ui/progress';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { cn } from '@/lib/utils';

export interface ImportTargetField {
    id: string;
    label: string;
    required?: boolean;
    description?: string;
}

export interface ImportResult {
    success: number;
    errors: number;
    total: number;
    errorDetails?: string[];
}

interface ImportWizardProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    targetFields: ImportTargetField[];
    onImport: (
        file: File,
        mapping: Record<string, string>,
    ) => Promise<ImportResult>;
    variant?: 'sheet' | 'dialog';
    className?: string;
}

type Step = 'upload' | 'mapping' | 'preview' | 'progress';

interface ParsedCSV {
    headers: string[];
    rows: string[][];
}

function parseCSV(text: string): ParsedCSV {
    const lines = text.trim().split(/\r?\n/);
    if (lines.length === 0) {
        return { headers: [], rows: [] };
    }

    const splitLine = (line: string): string[] => {
        const result: string[] = [];
        let current = '';
        let inQuotes = false;

        for (let i = 0; i < line.length; i++) {
            const ch = line[i];
            if (ch === '"') {
                inQuotes = !inQuotes;
            } else if (ch === ',' && !inQuotes) {
                result.push(current.trim());
                current = '';
            } else {
                current += ch;
            }
        }
        result.push(current.trim());
        return result;
    };

    const headers = splitLine(lines[0] ?? '');
    const rows = lines.slice(1).map(splitLine);

    return { headers, rows };
}

function ImportWizard({
    open,
    onOpenChange,
    targetFields,
    onImport,
    variant = 'sheet',
    className,
}: ImportWizardProps) {
    const [step, setStep] = React.useState<Step>('upload');
    const [file, setFile] = React.useState<File | null>(null);
    const [parsed, setParsed] = React.useState<ParsedCSV | null>(null);
    const [mapping, setMapping] = React.useState<Record<string, string>>({});
    const [validationErrors, setValidationErrors] = React.useState<
        Record<number, string[]>
    >({});
    const [importing, setImporting] = React.useState(false);
    const [importProgress, setImportProgress] = React.useState(0);
    const [importResult, setImportResult] = React.useState<ImportResult | null>(
        null,
    );

    const reset = () => {
        setStep('upload');
        setFile(null);
        setParsed(null);
        setMapping({});
        setValidationErrors({});
        setImporting(false);
        setImportProgress(0);
        setImportResult(null);
    };

    const handleClose = (open: boolean) => {
        if (!open) {
            reset();
        }
        onOpenChange(open);
    };

    const handleFileDrop = (files: File[]) => {
        const dropped = files[0];
        if (!dropped) {
            return;
        }
        setFile(dropped);

        const reader = new FileReader();
        reader.onload = (e) => {
            const text = e.target?.result as string;
            const csvData = parseCSV(text);
            setParsed(csvData);

            const autoMapping: Record<string, string> = {};
            csvData.headers.forEach((header) => {
                const match = targetFields.find(
                    (f) =>
                        f.label.toLowerCase() === header.toLowerCase() ||
                        f.id.toLowerCase() === header.toLowerCase(),
                );
                if (match) {
                    autoMapping[header] = match.id;
                }
            });
            setMapping(autoMapping);
        };
        reader.readAsText(dropped);
    };

    const handleMappingChange = (csvColumn: string, fieldId: string) => {
        setMapping((prev) => ({
            ...prev,
            [csvColumn]: fieldId === 'skip' ? '' : fieldId,
        }));
    };

    const validatePreview = (): Record<number, string[]> => {
        if (!parsed) {
            return {};
        }
        const errors: Record<number, string[]> = {};

        const requiredFields = targetFields.filter((f) => f.required);

        parsed.rows.slice(0, 10).forEach((row, rowIndex) => {
            const rowErrors: string[] = [];
            requiredFields.forEach((field) => {
                const csvColumn = Object.entries(mapping).find(
                    ([, fId]) => fId === field.id,
                )?.[0];
                const colIndex = csvColumn
                    ? parsed.headers.indexOf(csvColumn)
                    : -1;
                const value = colIndex >= 0 ? row[colIndex] : undefined;
                if (!value || value.trim() === '') {
                    rowErrors.push(`${field.label} is required`);
                }
            });
            if (rowErrors.length > 0) {
                errors[rowIndex] = rowErrors;
            }
        });

        return errors;
    };

    const handleImport = async () => {
        if (!file) {
            return;
        }
        setImporting(true);
        setStep('progress');

        const ticker = setInterval(() => {
            setImportProgress((prev) => Math.min(prev + 10, 90));
        }, 300);

        try {
            const result = await onImport(file, mapping);
            clearInterval(ticker);
            setImportProgress(100);
            setImportResult(result);
        } catch {
            clearInterval(ticker);
            setImportProgress(100);
            setImportResult({
                success: 0,
                errors: 1,
                total: 1,
                errorDetails: ['Import failed. Please try again.'],
            });
        } finally {
            setImporting(false);
        }
    };

    const previewRows = parsed?.rows.slice(0, 10) ?? [];
    const mappedHeaders = parsed?.headers.filter((h) => mapping[h]) ?? [];

    const content = (
        <div className={cn('flex flex-col gap-6', className)}>
            {/* Step indicators */}
            <div className="flex items-center gap-1 text-xs">
                {(['upload', 'mapping', 'preview', 'progress'] as Step[]).map(
                    (s, i, arr) => {
                        const labels: Record<Step, string> = {
                            upload: 'Upload',
                            mapping: 'Map Columns',
                            preview: 'Preview',
                            progress: 'Import',
                        };
                        const isActive = s === step;
                        const isDone = arr.indexOf(step) > i;

                        return (
                            <React.Fragment key={s}>
                                <span
                                    className={cn(
                                        'rounded-full px-2 py-0.5 font-medium transition-colors',
                                        isActive &&
                                            'bg-primary text-primary-foreground',
                                        isDone &&
                                            'text-muted-foreground line-through',
                                        !isActive &&
                                            !isDone &&
                                            'text-muted-foreground',
                                    )}
                                >
                                    {labels[s]}
                                </span>
                                {i < arr.length - 1 && (
                                    <ChevronRightIcon className="size-3 text-muted-foreground" />
                                )}
                            </React.Fragment>
                        );
                    },
                )}
            </div>

            {/* Step: Upload */}
            {step === 'upload' && (
                <div className="space-y-4">
                    <FileDropzone
                        accept={{
                            'text/csv': ['.csv'],
                            'application/vnd.ms-excel': ['.xls'],
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                                ['.xlsx'],
                        }}
                        maxFiles={1}
                        onDrop={handleFileDrop}
                        label="Drag & drop your CSV or Excel file here"
                        description="Supported formats: .csv, .xls, .xlsx"
                    />
                    {file && parsed && (
                        <div className="flex items-center gap-2 rounded-lg border bg-muted/40 p-3 text-sm">
                            <FileTextIcon className="size-4 text-muted-foreground" />
                            <span className="flex-1 font-medium">
                                {file.name}
                            </span>
                            <Badge variant="secondary">
                                {parsed.rows.length} rows
                            </Badge>
                            <Badge variant="secondary">
                                {parsed.headers.length} columns
                            </Badge>
                        </div>
                    )}
                    <div className="flex justify-end">
                        <Button
                            disabled={!file || !parsed}
                            onClick={() => setStep('mapping')}
                        >
                            Next: Map Columns
                        </Button>
                    </div>
                </div>
            )}

            {/* Step: Column mapping */}
            {step === 'mapping' && parsed && (
                <div className="space-y-4">
                    <p className="text-sm text-muted-foreground">
                        Match your CSV columns to the target fields. Unmatched
                        columns will be skipped.
                    </p>
                    <div className="space-y-2">
                        {parsed.headers.map((header) => (
                            <div
                                key={header}
                                className="flex items-center gap-3 rounded-lg border p-3"
                            >
                                <div className="w-40 truncate">
                                    <span className="text-sm font-medium">
                                        {header}
                                    </span>
                                    <p className="text-xs text-muted-foreground">
                                        Column from file
                                    </p>
                                </div>
                                <ChevronRightIcon className="size-4 shrink-0 text-muted-foreground" />
                                <Select
                                    value={mapping[header] ?? 'skip'}
                                    onValueChange={(v) =>
                                        handleMappingChange(header, v)
                                    }
                                >
                                    <SelectTrigger className="flex-1">
                                        <SelectValue placeholder="Skip this column" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="skip">
                                            <span className="text-muted-foreground">
                                                Skip column
                                            </span>
                                        </SelectItem>
                                        {targetFields.map((field) => (
                                            <SelectItem
                                                key={field.id}
                                                value={field.id}
                                            >
                                                <span>{field.label}</span>
                                                {field.required && (
                                                    <span className="ml-1 text-destructive">
                                                        *
                                                    </span>
                                                )}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        ))}
                    </div>
                    <div className="flex justify-between">
                        <Button
                            variant="outline"
                            onClick={() => setStep('upload')}
                        >
                            Back
                        </Button>
                        <Button
                            onClick={() => {
                                const errors = validatePreview();
                                setValidationErrors(errors);
                                setStep('preview');
                            }}
                        >
                            Next: Preview
                        </Button>
                    </div>
                </div>
            )}

            {/* Step: Preview */}
            {step === 'preview' && parsed && (
                <div className="space-y-4">
                    <p className="text-sm text-muted-foreground">
                        Showing first {previewRows.length} of{' '}
                        {parsed.rows.length} rows.{' '}
                        {Object.keys(validationErrors).length > 0 && (
                            <span className="font-medium text-destructive">
                                {Object.keys(validationErrors).length} row(s)
                                have validation errors.
                            </span>
                        )}
                    </p>
                    <div className="max-h-80 overflow-auto rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-10">#</TableHead>
                                    {mappedHeaders.map((h) => (
                                        <TableHead key={h}>
                                            {targetFields.find(
                                                (f) => f.id === mapping[h],
                                            )?.label ?? h}
                                        </TableHead>
                                    ))}
                                    <TableHead className="w-10" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {previewRows.map((row, rowIndex) => {
                                    const hasError = Boolean(
                                        validationErrors[rowIndex],
                                    );
                                    return (
                                        <TableRow
                                            key={rowIndex}
                                            className={cn(
                                                hasError && 'bg-destructive/5',
                                            )}
                                        >
                                            <TableCell className="text-xs text-muted-foreground">
                                                {rowIndex + 1}
                                            </TableCell>
                                            {mappedHeaders.map((h) => {
                                                const colIndex =
                                                    parsed.headers.indexOf(h);
                                                return (
                                                    <TableCell
                                                        key={h}
                                                        className="text-sm"
                                                    >
                                                        {row[colIndex] ?? ''}
                                                    </TableCell>
                                                );
                                            })}
                                            <TableCell>
                                                {hasError && (
                                                    <AlertCircleIcon
                                                        className="size-4 text-destructive"
                                                        aria-label={validationErrors[
                                                            rowIndex
                                                        ]?.join(', ')}
                                                    />
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    );
                                })}
                            </TableBody>
                        </Table>
                    </div>
                    <div className="flex justify-between">
                        <Button
                            variant="outline"
                            onClick={() => setStep('mapping')}
                        >
                            Back
                        </Button>
                        <Button onClick={() => void handleImport()}>
                            Import {parsed.rows.length} Rows
                        </Button>
                    </div>
                </div>
            )}

            {/* Step: Progress */}
            {step === 'progress' && (
                <div className="space-y-6 py-4">
                    {importing ? (
                        <div className="space-y-4 text-center">
                            <Loader2Icon className="mx-auto size-10 animate-spin text-primary" />
                            <p className="font-medium">Importing data...</p>
                            <Progress value={importProgress} className="h-2" />
                            <p className="text-sm text-muted-foreground">
                                {importProgress}% complete
                            </p>
                        </div>
                    ) : importResult ? (
                        <div className="space-y-4">
                            <div className="flex items-center gap-3">
                                {importResult.errors === 0 ? (
                                    <CheckCircleIcon className="size-10 text-success" />
                                ) : (
                                    <AlertCircleIcon className="size-10 text-warning" />
                                )}
                                <div>
                                    <p className="text-lg font-semibold">
                                        Import Complete
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {importResult.total} rows processed
                                    </p>
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <Card className="border-success/30 bg-success/5">
                                    <CardContent className="p-4 text-center">
                                        <p className="text-2xl font-bold text-success">
                                            {importResult.success}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            Successful
                                        </p>
                                    </CardContent>
                                </Card>
                                <Card className="border-destructive/30 bg-destructive/5">
                                    <CardContent className="p-4 text-center">
                                        <p className="text-2xl font-bold text-destructive">
                                            {importResult.errors}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            Errors
                                        </p>
                                    </CardContent>
                                </Card>
                            </div>

                            {importResult.errorDetails &&
                                importResult.errorDetails.length > 0 && (
                                    <div className="space-y-1 rounded-lg border border-destructive/30 bg-destructive/5 p-3">
                                        <p className="text-xs font-semibold text-destructive">
                                            Error Details
                                        </p>
                                        {importResult.errorDetails.map(
                                            (err, i) => (
                                                <div
                                                    key={i}
                                                    className="flex items-start gap-1.5 text-xs text-muted-foreground"
                                                >
                                                    <XCircleIcon className="mt-0.5 size-3 shrink-0 text-destructive" />
                                                    <span>{err}</span>
                                                </div>
                                            ),
                                        )}
                                    </div>
                                )}

                            <div className="flex justify-end gap-2">
                                <Button variant="outline" onClick={reset}>
                                    Import Another File
                                </Button>
                                <Button onClick={() => handleClose(false)}>
                                    Done
                                </Button>
                            </div>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            <Skeleton className="mx-auto h-10 w-10 rounded-full" />
                            <Skeleton className="mx-auto h-4 w-1/2" />
                            <Skeleton className="h-2 w-full" />
                        </div>
                    )}
                </div>
            )}
        </div>
    );

    if (variant === 'dialog') {
        return (
            <Dialog open={open} onOpenChange={handleClose}>
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Import Data</DialogTitle>
                        <DialogDescription>
                            Upload a CSV or Excel file to import data into the
                            system.
                        </DialogDescription>
                    </DialogHeader>
                    {content}
                </DialogContent>
            </Dialog>
        );
    }

    return (
        <Sheet open={open} onOpenChange={handleClose}>
            <SheetContent className="w-full overflow-y-auto sm:max-w-2xl">
                <SheetHeader>
                    <SheetTitle>Import Data</SheetTitle>
                    <SheetDescription>
                        Upload a CSV or Excel file to import data into the
                        system.
                    </SheetDescription>
                </SheetHeader>
                <div className="mt-6">{content}</div>
            </SheetContent>
        </Sheet>
    );
}

export { ImportWizard };
