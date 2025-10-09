<?php

declare(strict_types=1);

use App\Rules\ValidEmail;

it('works with valid email', function (string $email): void {
    $rule = new ValidEmail;

    $failed = false;

    $rule->validate('email', $email, function () use (&$failed): void {
        $failed = true;
    });

    expect($failed)->toBeFalse();
})->with([
    // Standard Valid Emails
    'simple@example.com',
    'very.common@example.com',
    'disposable.style.email.with+symbol@example.com',
    'other.email-with-hyphen@example.com',
    'x@example.com',
    'example-indeed@strange-example.com',
    'admin@mailserver1.com',
    'user.name+tag+sorting@example.com',
    'user.name@sub.domain.com',
    'firstname-lastname@example.com',

    // Emails with Numbers
    '1234567890@example.com',
    'user.123@example.com',
    'user123@example.com',
    '9876543210@example.net',
    'test456@domain123.com',

    // Emails with Long Local Parts
    'a.very.long.email.address.but.valid@example.com',
    'another.really.long.email.address@example.co.uk',
    'longlocalpart123456789012345678901234567890@example.com',
    'superlongemailaddresswith123456789@example.org',
    'excessive-length-testing-allowed@example.com',

    // Emails with Special Characters
    'user@ex-ample.com',

    // Emails with Subdomains
    'user@mail.example.com',
    'contact@support.company.com',
    'info@help.docs.example.com',
    'customer.service@global.enterprise.org',
    'feedback@eu.store.example.net',

    // Emails with Newer TLDs
    'user@company.app',
    'support@business.dev',
    'test@something.xyz',
    'email@custom.tld',
    'person@organization.online',

    // Emails with Uncommon TLDs
    'user@domain.museum',
    'info@charity.foundation',
    'admin@website.travel',
    'sales@company.agency',
    'team@startup.tech',
]);

it('fails with invalid email', function (string $email): void {
    $rule = new ValidEmail;

    $failed = false;

    $rule->validate('email', $email, function () use (&$failed): void {
        $failed = true;
    });

    expect($failed)->toBeTrue();
})->with([
    // Only Lowercase:
    'R@r.com',
    'r@R.com',

    // Empty on any part
    '@example.com',
    'user@',
    'user@.com',
    'user@.example',
    'user@.example.com',
    'user@sub..example.com',
    'user',
    '',

    // IP Addresses
    'user@123.123.123.123',
    'user@[192.168.1.1]',
    'user@[IPv6:2001:db8::1]',

    // Quotes
    '"user@with-quotes"@example.com',
    "'user@with-quotes'@example.com",
    '"very.unusual.@.email"@example.com',
    '"quoted.local@part"@example.com',
    '"user name"@example.com',

    // International & Unicode Emails
    'üñîçødé@example.com',
    'δοκιμή@παράδειγμα.ελ',
    '测试@测试.中国',
    'пример@пример.рус',
    'उपयोगकर्ता@उदाहरण.भारत',

    // Edge Case Emails
    'mat@me',
    'user@localserver',
    'user@localdomain',
    'user@sub.-domain.com',
    '𝓊𝓃𝒾𝒸ℴ𝒹ℯ@𝒹ℴ𝓂𝒶𝒾𝓃.𝒸ℴ𝓂',
]);
