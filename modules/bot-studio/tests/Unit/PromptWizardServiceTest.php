<?php

declare(strict_types=1);

use Modules\BotStudio\Services\PromptWizardService;

/*
|--------------------------------------------------------------------------
| Bot Studio: PromptWizardService Unit Tests
|--------------------------------------------------------------------------
*/

beforeEach(function (): void {
    $this->service = new PromptWizardService();
});

it('generates a prompt from full wizard answers', function (): void {
    $prompt = $this->service->generate([
        'role' => 'customer support specialist for a real estate company',
        'tone' => 'professional',
        'expertise' => 'property listings, pricing, availability, finance options',
        'restrictions' => "Don't give legal advice. Don't discuss competitors. Don't make pricing promises.",
    ]);

    expect($prompt)
        ->toContain('You are a professional customer support specialist for a real estate company for {{org_name}}.')
        ->toContain('Your expertise covers: property listings, pricing, availability, finance options.')
        ->toContain('When greeting users, address them as {{user_name}}.')
        ->toContain('Important restrictions:')
        ->toContain("- Don't give legal advice")
        ->toContain("- Don't discuss competitors")
        ->toContain("- Don't make pricing promises");
});

it('includes org_name and user_name template variables', function (): void {
    $prompt = $this->service->generate([
        'role' => 'assistant',
        'tone' => 'friendly',
    ]);

    expect($prompt)
        ->toContain('{{org_name}}')
        ->toContain('{{user_name}}');
});

it('skips expertise section when not provided', function (): void {
    $prompt = $this->service->generate([
        'role' => 'support agent',
        'tone' => 'professional',
    ]);

    expect($prompt)->not->toContain('Your expertise covers');
});

it('skips restrictions section when not provided', function (): void {
    $prompt = $this->service->generate([
        'role' => 'support agent',
        'tone' => 'professional',
        'expertise' => 'billing, accounts',
    ]);

    expect($prompt)->not->toContain('Important restrictions');
});

it('maps professional tone correctly', function (): void {
    $prompt = $this->service->generate(['role' => 'agent', 'tone' => 'professional']);

    expect($prompt)->toContain('You are a professional agent');
});

it('maps friendly tone to friendly and approachable', function (): void {
    $prompt = $this->service->generate(['role' => 'agent', 'tone' => 'friendly']);

    expect($prompt)->toContain('You are a friendly and approachable agent');
});

it('maps casual tone to casual and conversational', function (): void {
    $prompt = $this->service->generate(['role' => 'agent', 'tone' => 'casual']);

    expect($prompt)->toContain('You are a casual and conversational agent');
});

it('maps technical tone to precise and technical', function (): void {
    $prompt = $this->service->generate(['role' => 'agent', 'tone' => 'technical']);

    expect($prompt)->toContain('You are a precise and technical agent');
});

it('maps empathetic tone to empathetic and understanding', function (): void {
    $prompt = $this->service->generate(['role' => 'agent', 'tone' => 'empathetic']);

    expect($prompt)->toContain('You are a empathetic and understanding agent');
});

it('different tones produce different wording', function (): void {
    $professional = $this->service->generate(['role' => 'agent', 'tone' => 'professional']);
    $casual = $this->service->generate(['role' => 'agent', 'tone' => 'casual']);
    $empathetic = $this->service->generate(['role' => 'agent', 'tone' => 'empathetic']);

    expect($professional)->not->toEqual($casual);
    expect($casual)->not->toEqual($empathetic);
    expect($professional)->not->toEqual($empathetic);
});

it('returns a minimal prompt when role is empty', function (): void {
    $prompt = $this->service->generate([]);

    expect($prompt)
        ->toContain('You are an assistant for {{org_name}}.')
        ->toContain('When greeting users, address them as {{user_name}}.');
});

it('handles missing optional fields gracefully', function (): void {
    $prompt = $this->service->generate(['role' => 'helper']);

    expect($prompt)
        ->toContain('{{org_name}}')
        ->toContain('{{user_name}}')
        ->not->toContain('Your expertise covers')
        ->not->toContain('Important restrictions');
});
