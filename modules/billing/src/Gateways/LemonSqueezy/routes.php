<?php

declare(strict_types=1);

/*
 * LemonSqueezy webhooks are handled by the lemonsqueezy/laravel package.
 * No additional gateway-specific routes are needed here. The OrderCreated
 * event listener (AddCreditsFromLemonSqueezyOrder) handles the integration.
 *
 * If custom LemonSqueezy webhook routes are needed in the future, add them here.
 */
