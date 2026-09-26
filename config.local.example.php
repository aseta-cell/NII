<?php
// Copy this file to config.local.php on the server and fill in real values.
// config.local.php is ignored by git, so passwords never end up on GitHub.

return [
    "db_host" => "localhost",
    "db_name" => "journal",
    "db_user" => "journal_user",
    "db_pass" => "PUT-STRONG-PASSWORD-HERE",

    "site_url" => "https://nii-arai-publishhouse.kz",
    "debug"    => false,

    // Codes people must enter to register as reviewer / editor
    "reviewer_code" => "PUT-SECRET-REVIEWER-CODE",
    "editor_code"   => "PUT-SECRET-EDITOR-CODE",

    // "currency" => "KZT",
    // "plans" => [
    //     "author"        => ["name" => "Author",        "amount" => 70000,  "link" => ""],
    //     "professional"  => ["name" => "Professional",  "amount" => 240000, "link" => ""],
    //     "institutional" => ["name" => "Institutional", "amount" => null,   "link" => "institutional.html"],
    // ],

    // New special-access password (optional): put its hash here
    // "free_access_hash" => '$2y$12$...',

    "payment_instructions" => "Kaspi Gold: +7 700 000 00 00 (Name S.)\nBank: ... IBAN: KZ...\nIn the transfer comment write your payment number.",
];
