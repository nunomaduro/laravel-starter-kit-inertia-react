export interface FeatureItem {
    title: string;
    description: string;
}

export interface FeaturesBlockProps {
    heading: string;
    subheading?: string;
    items: FeatureItem[];
}

export function FeaturesBlock({
    heading,
    subheading,
    items,
}: FeaturesBlockProps) {
    return (
        <section className="container py-16 md:py-24">
            <div className="mx-auto max-w-2xl text-center">
                <h2 className="text-3xl font-bold tracking-tight md:text-4xl">
                    {heading}
                </h2>
                {subheading && (
                    <p className="mt-4 text-lg text-muted-foreground">
                        {subheading}
                    </p>
                )}
            </div>
            <ul className="mx-auto mt-12 grid max-w-5xl gap-8 sm:grid-cols-2 lg:grid-cols-3">
                {items.map((item) => (
                    <li
                        key={item.title}
                        className="rounded-lg border bg-card p-6"
                    >
                        <h3 className="font-semibold">{item.title}</h3>
                        <p className="mt-2 text-sm text-muted-foreground">
                            {item.description}
                        </p>
                    </li>
                ))}
            </ul>
        </section>
    );
}
