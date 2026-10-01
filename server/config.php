<?php
/*
 * HOP FEST website settings — edit this file on the web server.
 * Never put a real password in the GitHub repo.
 */
return [
    // Password for viewing sign-ups at  https://your-site/signups.php
    // The page stays locked until you change this from CHANGE-ME.
    'admin_password' => 'CHANGE-ME',

    // Optional: an email address that gets a message for every new sign-up.
    // Leave empty ('') to turn this off. Needs email to be enabled on the hosting.
    'notify_email' => '',

    // Shown in the footer under "Contact us" and as the Instagram link.
    // Leave any of these empty ('') to hide them.
    'contact' => [
        'email'     => 'hopfest.legaxy@gmail.com',
        'phone'     => '',
        'instagram' => 'hopfestofficial',   // just the handle
    ],
];
