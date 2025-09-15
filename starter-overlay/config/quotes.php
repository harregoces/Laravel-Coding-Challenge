<?php

return [
    'ttl' => (int) env('QUOTES_TTL', 30),
    'client' => env('QUOTES_CLIENT', 'real'), // 'stub' | 'real'
];
