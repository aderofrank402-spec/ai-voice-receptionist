<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Crawl Webhook Secret
    |--------------------------------------------------------------------------
    | Shared secret for webhook auth when the crawler POSTs progress/result
    | to /api/crawler-progress (X-Crawler-Secret or X-Crawler-Signature).
    */
    'service_secret' => env('CRAWLER_SERVICE_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Docker Compose (Option A)
    |--------------------------------------------------------------------------
    | Path to docker-compose file for the crawler, relative to project root.
    | Default: docker-compose.crawler.yml (in project root). No .env needed.
    */
    'docker_compose_file' => env('CRAWLER_DOCKER_COMPOSE_FILE', 'docker-compose.crawler.yml'),

];
