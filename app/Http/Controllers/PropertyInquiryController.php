<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateConversation;
use App\Actions\SendMessage;
use App\Http\Requests\StoreInquiryRequest;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;

final readonly class PropertyInquiryController
{
    public function __invoke(
        StoreInquiryRequest $request,
        Property $property,
        CreateConversation $createConversation,
        SendMessage $sendMessage,
    ): RedirectResponse {
        /** @var array{body: string} $validated */
        $validated = $request->validated();

        /** @var \App\Models\User $user */
        $user = $request->user();

        $conversation = $createConversation->handle($user, $property);
        $sendMessage->handle($conversation, $user, $validated['body']);

        return redirect()->route('conversations.show', $conversation);
    }
}
