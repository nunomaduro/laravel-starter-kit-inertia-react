import { usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';

type Flash = {
    success?: string;
    error?: string;
    info?: string;
    warning?: string;
};

declare module '@inertiajs/react' {
    interface PageProps {
        flash?: Flash | null;
        status?: string | null;
    }
}

export function FlashListener(): null {
    const { props } = usePage();
    const shownRef = useRef(false);

    useEffect(() => {
        const flash = props.flash as Flash | undefined;
        const status = props.status as string | undefined;
        if (!flash && !status) {
            shownRef.current = false;
            return;
        }
        if (shownRef.current) return;
        if (flash?.success) {
            toast.success(flash.success);
            shownRef.current = true;
        } else if (flash?.error) {
            toast.error(flash.error);
            shownRef.current = true;
        } else if (flash?.info) {
            toast.info(flash.info);
            shownRef.current = true;
        } else if (flash?.warning) {
            toast.warning(flash.warning);
            shownRef.current = true;
        } else if (status) {
            toast.success(status);
            shownRef.current = true;
        }
    }, [props.flash, props.status]);

    return null;
}
