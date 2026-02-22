import { usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';

type Flash = { success?: string; error?: string };

declare module '@inertiajs/react' {
    interface PageProps {
        flash?: Flash | null;
        status?: string | null;
    }
}

export function FlashListener(): null {
    const { props } = usePage();
    const shown = useRef(false);

    useEffect(() => {
        const flash = props.flash as Flash | undefined;
        const status = props.status as string | undefined;
        if (!flash && !status) {
            shown.current = false;
            return;
        }
        if (shown.current) return;
        if (flash?.success) {
            toast.success(flash.success);
            shown.current = true;
        } else if (flash?.error) {
            toast.error(flash.error);
            shown.current = true;
        } else if (status) {
            toast.success(status);
            shown.current = true;
        }
    }, [props.flash, props.status]);

    return null;
}
