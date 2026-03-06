import { usePage } from '@inertiajs/react';

interface HoneypotData {
    enabled: boolean;
    nameFieldName: string;
    validFromFieldName: string;
    encryptedValidFrom: string;
}

interface SharedProps {
    honeypot: HoneypotData | null;
}

/**
 * Renders hidden honeypot fields for spam protection. Include inside any form
 * that posts to a route protected by ProtectAgainstSpam middleware (e.g. register).
 * Only renders when honeypot is enabled and data is available from shared props.
 */
function HoneypotFields() {
    const { honeypot } = usePage().props as unknown as SharedProps;

    if (!honeypot?.enabled) {
        return null;
    }

    return (
        <>
            <input
                type="text"
                name={honeypot.nameFieldName}
                defaultValue=""
                autoComplete="off"
                tabIndex={-1}
                className="absolute -left-[9999px] h-0 w-0 overflow-hidden"
                aria-hidden
            />
            <input
                type="text"
                name={honeypot.validFromFieldName}
                defaultValue={honeypot.encryptedValidFrom}
                autoComplete="off"
                tabIndex={-1}
                className="absolute -left-[9999px] h-0 w-0 overflow-hidden"
                aria-hidden
            />
        </>
    );
}

export { HoneypotFields };
export default HoneypotFields;
