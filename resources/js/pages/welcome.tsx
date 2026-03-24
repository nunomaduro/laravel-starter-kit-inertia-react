import { type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { BuiltWithSection } from './welcome/built-with-section';
import { ComparisonSection } from './welcome/comparison-section';
import { CtaSection } from './welcome/cta-section';
import { DifferentiatorsSection } from './welcome/differentiators-section';
import { featureGroups, pricingTiers } from './welcome/feature-data';
import { FeaturesSection } from './welcome/features-section';
import { HeroSection } from './welcome/hero-section';
import { HowItWorksSection } from './welcome/how-it-works-section';
import { ModulesSection } from './welcome/modules-section';
import { PricingSection } from './welcome/pricing-section';
import { StatsSection } from './welcome/stats-section';
import { WelcomeFooter } from './welcome/welcome-footer';
import { WelcomeHeader } from './welcome/welcome-header';

export default function Welcome() {
    const { auth, features: f } = usePage<SharedData>().props;
    const flags = f ?? {};
    const name = usePage<SharedData>().props.name;

    return (
        <>
            <Head title="AI-Native App Factory" />
            <div className="flex min-h-screen flex-col bg-background text-foreground">
                <WelcomeHeader
                    name={name}
                    isAuthenticated={!!auth.user}
                    flags={flags}
                />

                <HeroSection />

                <div className="h-px w-full bg-border" />

                <HowItWorksSection />

                <div className="h-px w-full bg-border" />

                <ModulesSection />

                <StatsSection />

                <BuiltWithSection />

                <div className="h-px w-full bg-border" />

                <DifferentiatorsSection />

                <div className="h-px w-full bg-border" />

                <FeaturesSection featureGroups={featureGroups} />

                <div className="h-px w-full bg-border" />

                <ComparisonSection />

                <div className="h-px w-full bg-border" />

                <PricingSection tiers={pricingTiers} />

                <div className="h-px w-full bg-border" />

                <CtaSection />

                <WelcomeFooter />
            </div>
        </>
    );
}
