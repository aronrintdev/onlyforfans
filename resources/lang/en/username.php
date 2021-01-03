<?php
/**
 * Username validation strings
 */

return [
    'already_in_use' => 'A user with that username already exists, please choose a different one',

    // This is a backup if other localization fails
    'invalid' => 'That username is invalid',

    'default' => [
        'word' => 'Your username may not not be the word :rule',
        'regex' => 'Your username may not contain the word :rule',
    ],
    'custom' => [
        /**
         * Place any added explanations from the database here.
         * Key with the rule or explanation text. Key is preferred over explanation
         */
        'allfans' => 'The word allfans may not be used anywhere in your username',
    ],
];
