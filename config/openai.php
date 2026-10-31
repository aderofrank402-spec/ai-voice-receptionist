<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key
    |--------------------------------------------------------------------------
    */
    'api_key' => env('OPENAI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Options
    |--------------------------------------------------------------------------
    | Controls Guzzle options for all OpenAI SDK calls.
    | In production: 'verify' should be true (default).
    | In local dev on Windows without CA bundle: set to false via .env
    */
    'verify_ssl' => env('OPENAI_VERIFY_SSL', true),

];
