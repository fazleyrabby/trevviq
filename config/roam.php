<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content Indexation Gate
    |--------------------------------------------------------------------------
    |
    | A location becomes indexable when it has at least this many approved
    | content items (videos + reviews + events), is verified, carries an
    | editorial description, or is an ancestor of an indexable location.
    |
    */
    'indexation' => [
        'content_threshold' => (int) env('ROAM_INDEXATION_THRESHOLD', 3),
        'description_min_length' => (int) env('ROAM_INDEXATION_DESCRIPTION_LENGTH', 120),
    ],

    'sitemap' => [
        'chunk_size' => (int) env('ROAM_SITEMAP_CHUNK', 50000),
        'cache_ttl' => (int) env('ROAM_SITEMAP_CACHE_TTL', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reserved URL Segments
    |--------------------------------------------------------------------------
    |
    | First path segments that may never be used as a location slug so the
    | catch-all /travel/{path} route can never shadow application routes.
    |
    */
    'reserved_slugs' => [
        'explore', 'search', 'map', 'video', 'videos', 'event', 'events',
        'traveller', 'travellers', 'admin', 'api', 'dashboard', 'login',
        'register', 'logout', 'profile', 'travel', 'about', 'terms',
        'privacy', 'sitemap',
    ],

    /*
    |--------------------------------------------------------------------------
    | Discovery Defaults
    |--------------------------------------------------------------------------
    */
    'nearby' => [
        'default_radius_meters' => (int) env('ROAM_NEARBY_RADIUS', 5000),
        'default_limit' => (int) env('ROAM_NEARBY_LIMIT', 12),
        'max_limit' => 48,
    ],

    'search' => [
        'default_per_page' => 20,
    ],

    'reviews' => [
        // MVP default: publish traveller reviews immediately. Set to false to
        // route new reviews to the moderation queue (Section 80).
        'auto_approve' => (bool) env('ROAM_REVIEWS_AUTO_APPROVE', true),

        // A user may review the same location once per cooldown window.
        'cooldown_days' => (int) env('ROAM_REVIEW_COOLDOWN_DAYS', 30),
    ],

    'video' => [
        // Storage disk for originals, renditions, and thumbnails. Swap to an
        // S3/R2 disk in production (Section 82).
        'disk' => env('ROAM_VIDEO_DISK', 'public'),

        // "fake" (no external binary; tests/local) or "ffmpeg" (production).
        'processor' => env('ROAM_VIDEO_PROCESSOR', 'fake'),
        'ffmpeg_bin' => env('ROAM_FFMPEG_BIN', 'ffmpeg'),
        'ffprobe_bin' => env('ROAM_FFPROBE_BIN', 'ffprobe'),

        // Server-enforced upload limits (Section 81).
        'max_duration' => (int) env('ROAM_VIDEO_MAX_DURATION', 60),
        'max_size_mb' => (int) env('ROAM_VIDEO_MAX_SIZE_MB', 100),
        'mime_types' => ['video/mp4', 'video/quicktime'],

        // Publish automatically once processing passes; set false to route to
        // the human moderation queue (Section 80).
        'auto_publish' => (bool) env('ROAM_VIDEO_AUTO_PUBLISH', true),
    ],

    'events' => [
        // Publish traveller/organizer events immediately; set false to route
        // them to the moderation queue.
        'auto_approve' => (bool) env('ROAM_EVENTS_AUTO_APPROVE', true),
    ],

    'demo' => [
        // One-click demo logins. Off by default in production; on elsewhere so
        // the seeded demo accounts can be used without typing credentials.
        'enabled' => (bool) env('ROAM_DEMO_LOGIN', env('APP_ENV', 'production') !== 'production'),

        'user_email' => env('ROAM_DEMO_USER_EMAIL', 'traveller@roam.test'),
        'user_password' => env('ROAM_DEMO_USER_PASSWORD', 'password'),

        'admin_email' => env('ROAM_DEMO_ADMIN_EMAIL', 'admin@roam.test'),
        'admin_password' => env('ROAM_DEMO_ADMIN_PASSWORD', 'password'),
    ],

];
