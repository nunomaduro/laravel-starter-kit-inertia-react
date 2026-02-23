<?php

declare(strict_types=1);

use Spatie\Honeypot\SpamResponder\BlankPageResponder;

return [
    /*
     * This switch determines if the honeypot protection should be activated.
     */
    // Managed via Filament: Settings > Security
    'enabled' => true,

    /*
     * Here you can specify name of the honeypot field. Any requests that submit a non-empty
     * value for this name will be discarded. Make sure this name does not
     * collide with a form field that is actually used.
     */
    'name_field_name' => 'my_name',

    /*
     * When this is activated there will be a random string added
     * to the name_field_name. Set to false for Inertia/SPA so the
     * frontend receives a stable field name via shared props.
     */
    'randomize_name_field_name' => false,

    /*
     * When this is activated, requests will be checked if
     * form is submitted faster than this amount of seconds
     */
    'valid_from_timestamp' => true,

    /*
     * This field contains the name of a form field that will be used to verify
     * if the form wasn't submitted too quickly. Make sure this name does not
     * collide with a form field that is actually used.
     */
    'valid_from_field_name' => 'valid_from',

    /*
     * If the form is submitted faster than this amount of seconds
     * the form submission will be considered invalid.
     */
    'amount_of_seconds' => 1,

    /*
     * This class is responsible for sending a response to requests that
     * are detected as being spammy. By default a blank page is shown.
     *
     * A valid responder is any class that implements
     * `Spatie\Honeypot\SpamResponder\SpamResponder`
     */
    'respond_to_spam_with' => BlankPageResponder::class,

    /*
     * When activated, requests will be checked if honeypot fields are missing,
     * if so the request will be stamped as spam. Be careful! When using the
     * global middleware be sure to add honeypot fields to each form.
     */
    'honeypot_fields_required_for_all_forms' => false,

    /*
     * This class is responsible for applying all spam protection
     * rules for a request. In most cases, you shouldn't change
     * this value.
     */
    'spam_protection' => Spatie\Honeypot\SpamProtection::class,

    /*
     * need to add @cspNonce https://github.com/spatie/laravel-csp in style tag hidden items
    */
    'with_csp' => false,
];
