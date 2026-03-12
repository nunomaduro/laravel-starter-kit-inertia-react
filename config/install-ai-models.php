<?php

declare(strict_types=1);

/**
 * Curated list of major AI models for the installer default-model combobox.
 * IDs are OpenRouter-style (provider/model). Pricing is approximate per 1M tokens unless marked Free.
 */
return [
    [
        'id' => 'deepseek/deepseek-r1-0528:free',
        'name' => 'DeepSeek R1 (free)',
        'pricing' => 'Free',
        'free' => true,
    ],
    [
        'id' => 'google/gemini-2.0-flash-001',
        'name' => 'Google Gemini 2.0 Flash',
        'pricing' => 'Free tier',
        'free' => true,
    ],
    [
        'id' => 'meta-llama/llama-3.3-70b-instruct:free',
        'name' => 'Meta Llama 3.3 70B (free)',
        'pricing' => 'Free',
        'free' => true,
    ],
    [
        'id' => 'openai/gpt-4o-mini',
        'name' => 'OpenAI GPT-4o mini',
        'pricing' => '~$0.15/1M in',
        'free' => false,
    ],
    [
        'id' => 'openai/gpt-4o',
        'name' => 'OpenAI GPT-4o',
        'pricing' => '~$2.50/1M in',
        'free' => false,
    ],
    [
        'id' => 'openai/gpt-4o-nano',
        'name' => 'OpenAI GPT-4o nano',
        'pricing' => '~$0.10/1M in',
        'free' => false,
    ],
    [
        'id' => 'openai/o1-mini',
        'name' => 'OpenAI o1 mini',
        'pricing' => '~$1.10/1M in',
        'free' => false,
    ],
    [
        'id' => 'openai/o1',
        'name' => 'OpenAI o1',
        'pricing' => '~$15/1M in',
        'free' => false,
    ],
    [
        'id' => 'anthropic/claude-3-5-haiku',
        'name' => 'Anthropic Claude 3.5 Haiku',
        'pricing' => '~$0.25/1M in',
        'free' => false,
    ],
    [
        'id' => 'anthropic/claude-3-5-sonnet',
        'name' => 'Anthropic Claude 3.5 Sonnet',
        'pricing' => '~$3/1M in',
        'free' => false,
    ],
    [
        'id' => 'anthropic/claude-3-opus',
        'name' => 'Anthropic Claude 3 Opus',
        'pricing' => '~$15/1M in',
        'free' => false,
    ],
    [
        'id' => 'anthropic/claude-sonnet-4',
        'name' => 'Anthropic Claude Sonnet 4',
        'pricing' => '~$3/1M in',
        'free' => false,
    ],
    [
        'id' => 'google/gemini-2.5-flash-preview-05-20',
        'name' => 'Google Gemini 2.5 Flash',
        'pricing' => '~$0.30/1M in',
        'free' => false,
    ],
    [
        'id' => 'google/gemini-1.5-pro',
        'name' => 'Google Gemini 1.5 Pro',
        'pricing' => '~$1.25/1M in',
        'free' => false,
    ],
    [
        'id' => 'deepseek/deepseek-chat',
        'name' => 'DeepSeek Chat',
        'pricing' => '~$0.14/1M in',
        'free' => false,
    ],
    [
        'id' => 'deepseek/deepseek-r1',
        'name' => 'DeepSeek R1 (paid)',
        'pricing' => '~$0.55/1M in',
        'free' => false,
    ],
    [
        'id' => 'mistralai/mistral-small',
        'name' => 'Mistral Small',
        'pricing' => '~$0.20/1M in',
        'free' => false,
    ],
    [
        'id' => 'mistralai/mistral-large',
        'name' => 'Mistral Large',
        'pricing' => '~$2/1M in',
        'free' => false,
    ],
    [
        'id' => 'groq/llama-3.1-8b-instant',
        'name' => 'Groq Llama 3.1 8B',
        'pricing' => 'Free tier',
        'free' => true,
    ],
    [
        'id' => 'groq/llama-3.3-70b-versatile',
        'name' => 'Groq Llama 3.3 70B',
        'pricing' => 'Free tier',
        'free' => true,
    ],
    [
        'id' => 'x-ai/grok-3-mini',
        'name' => 'xAI Grok 3 mini',
        'pricing' => '~$0.20/1M in',
        'free' => false,
    ],
    [
        'id' => 'x-ai/grok-3',
        'name' => 'xAI Grok 3',
        'pricing' => '~$2/1M in',
        'free' => false,
    ],
    [
        'id' => 'meta-llama/llama-3.3-70b-instruct',
        'name' => 'Meta Llama 3.3 70B',
        'pricing' => '~$0.52/1M in',
        'free' => false,
    ],
    [
        'id' => 'qwen/qwen-2.5-72b-instruct',
        'name' => 'Qwen 2.5 72B',
        'pricing' => '~$0.35/1M in',
        'free' => false,
    ],
];
