<?php

declare(strict_types=1);

/**
 * Marketing Hero upgrade cards configuration.
 *
 * Add cards by appending associative arrays to this list.
 * Required keys:
 * - id: stable unique string identifier.
 * - title: card heading.
 * - description: concise explanation of value.
 * - button_label: button text.
 * - url: destination URL.
 * - open_in_new_tab: bool to open in a new browser tab.
 * Optional keys:
 * - badge: small highlight text like "Popular".
 */
return [
    [
        'id' => 'website-upgrade',
        'title' => 'Upgrade your website',
        'description' => 'Improve conversion and lead quality with a professionally optimized website.',
        'button_label' => 'Explore Website Upgrades',
        'url' => 'https://adrielpartners.com/websites',
        'open_in_new_tab' => true,
        'badge' => 'Popular',
    ],
];
