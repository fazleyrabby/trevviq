<?php

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Enums\LocationType;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Enums\VideoStatus;
use App\Enums\VideoVisibility;
use App\Enums\VisitSource;
use App\Enums\VisitVerification;
use App\Models\Event;
use App\Models\Location;
use App\Models\LocationImportBatch;
use App\Models\Report;
use App\Models\Review;
use App\Models\ReviewHelpful;
use App\Models\SavedVideo;
use App\Models\TravellerLocation;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoLike;
use App\Services\Location\LocationContentService;
use App\Services\Location\LocationIndexationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Comprehensive, idempotent demo dataset covering every module: travellers,
 * a global location tree, reviews (approved + pending), helpful votes, videos
 * (published + pending), likes, saves, travel history, follows, reports, and an
 * import batch. Safe to run repeatedly.
 */
class DemoSeeder extends Seeder
{
    /** @var array<int, User> */
    private array $travellers = [];

    private User $viewer;

    public function run(): void
    {
        $this->seedUsers();

        $locations = $this->seedLocations();
        $places = array_filter($locations, static fn (Location $l): bool => $l->type->isPlace());

        $reviews = $this->seedReviews($places);
        $this->seedHelpfulVotes($reviews);
        $videos = $this->seedVideos($places);
        $this->seedVideoInteractions($videos);
        $this->seedEvents($locations);
        $this->seedVisits($locations);
        $this->seedFollows();
        $this->seedReports($reviews, $videos);
        $this->seedImportBatch();

        $service = app(LocationContentService::class);

        foreach ($places as $place) {
            $service->recompute($place);
        }

        // Set the SEO indexation gate synchronously (the recompute above only
        // queues the job; seeding should not depend on a running worker).
        app(LocationIndexationService::class)->recomputeAll();
    }

    private function seedUsers(): void
    {
        $primary = (string) config('trevviq.demo.user_email', 'traveller@trevviq.test');

        $definitions = [
            ['name' => 'Fazley Rahman', 'username' => 'fazley', 'email' => $primary, 'country_code' => 'BD', 'bio' => 'Chasing sunsets along the Bay of Bengal.'],
            ['name' => 'Aiko Tanaka', 'username' => 'aiko', 'email' => 'aiko@trevviq.test', 'country_code' => 'JP', 'bio' => 'Tokyo-based, always looking for the next alley.'],
            ['name' => 'Marco Rossi', 'username' => 'marco', 'email' => 'marco@trevviq.test', 'country_code' => 'FR', 'bio' => 'Slow travel through Europe.'],
            ['name' => 'Priya Nair', 'username' => 'priya', 'email' => 'priya@trevviq.test', 'country_code' => 'IN', 'bio' => 'Street food, temples, and long train rides.'],
            ['name' => 'Leo Chai', 'username' => 'leo', 'email' => 'leo@trevviq.test', 'country_code' => 'TH', 'bio' => 'Island hopping and night markets.'],
        ];

        foreach ($definitions as $definition) {
            $this->travellers[] = User::updateOrCreate(
                ['email' => $definition['email']],
                [
                    'name' => $definition['name'],
                    'username' => $definition['username'],
                    'country_code' => $definition['country_code'],
                    'bio' => $definition['bio'],
                    'role' => UserRole::Traveller,
                    'email_verified_at' => now(),
                    'password' => 'password',
                ],
            );
        }

        $this->viewer = User::updateOrCreate(
            ['email' => 'viewer@trevviq.test'],
            [
                'name' => 'Vera Viewer',
                'username' => 'vera',
                'country_code' => 'US',
                'bio' => 'Here to plan the next trip.',
                'role' => UserRole::Viewer,
                'email_verified_at' => now(),
                'password' => 'password',
            ],
        );
    }

    /**
     * @return array<int, Location>
     */
    private function seedLocations(): array
    {
        $locations = [];

        $tree = [
            'Bangladesh' => ['code' => 'BD', 'population' => 169_800_000, 'lat' => 23.6850, 'lng' => 90.3563, 'cities' => [
                'Chattogram' => ['lat' => 22.3569, 'lng' => 91.7832, 'population' => 5_200_000, 'places' => [
                    ['Patenga Beach', LocationType::Beach, 22.2333, 91.7917, 'A long stretch of sand where the Karnaphuli meets the Bay of Bengal — famous for its sunsets and sea breeze.'],
                    ['Sea Breeze Restaurant', LocationType::Restaurant, 22.2360, 91.7900, null],
                    ['Hotel Agrabad', LocationType::Hotel, 22.3300, 91.8300, null],
                    ["Foy's Lake", LocationType::Park, 22.3667, 91.8000, null],
                ]],
                'Dhaka' => ['lat' => 23.8103, 'lng' => 90.4125, 'population' => 21_000_000, 'places' => [
                    ['Ahsan Manzil', LocationType::Museum, 23.7083, 90.4062, 'The pink palace on the Buriganga, once the seat of the Nawabs of Dhaka.'],
                    ['Star Kabab', LocationType::Restaurant, 23.7500, 90.3900, null],
                ]],
            ]],
            'Japan' => ['code' => 'JP', 'population' => 125_700_000, 'lat' => 36.2048, 'lng' => 138.2529, 'cities' => [
                'Tokyo' => ['lat' => 35.6762, 'lng' => 139.6503, 'population' => 13_960_000, 'places' => [
                    ['Shibuya Crossing', LocationType::Landmark, 35.6595, 139.7005, "The world's busiest pedestrian scramble, framed by neon and giant screens."],
                    ['Senso-ji Temple', LocationType::Landmark, 35.7148, 139.7967, null],
                    ['Tsukiji Outer Market', LocationType::Cafe, 35.6654, 139.7707, null],
                ]],
                'Kyoto' => ['lat' => 35.0116, 'lng' => 135.7681, 'population' => 1_460_000, 'places' => [
                    ['Fushimi Inari Shrine', LocationType::Landmark, 34.9671, 135.7727, 'Thousands of vermilion torii gates climbing Mount Inari.'],
                    ['Arashiyama Bamboo Grove', LocationType::Park, 35.0170, 135.6720, null],
                ]],
            ]],
            'France' => ['code' => 'FR', 'population' => 68_170_000, 'lat' => 46.2276, 'lng' => 2.2137, 'cities' => [
                'Paris' => ['lat' => 48.8566, 'lng' => 2.3522, 'population' => 2_148_000, 'places' => [
                    ['Eiffel Tower', LocationType::Landmark, 48.8584, 2.2945, null],
                    ['Louvre Museum', LocationType::Museum, 48.8606, 2.3376, null],
                    ['Cafe de Flore', LocationType::Cafe, 48.8541, 2.3325, null],
                ]],
            ]],
            'Italy' => ['code' => 'IT', 'population' => 58_940_000, 'lat' => 41.8719, 'lng' => 12.5674, 'cities' => [
                'Rome' => ['lat' => 41.9028, 'lng' => 12.4964, 'population' => 2_870_000, 'places' => [
                    ['Colosseum', LocationType::Landmark, 41.8902, 12.4922, null],
                    ['Trevi Fountain', LocationType::Landmark, 41.9009, 12.4833, null],
                    ['Da Enzo al 29', LocationType::Restaurant, 41.8889, 12.4771, null],
                ]],
            ]],
            'Thailand' => ['code' => 'TH', 'population' => 71_800_000, 'lat' => 15.8700, 'lng' => 100.9925, 'cities' => [
                'Bangkok' => ['lat' => 13.7563, 'lng' => 100.5018, 'population' => 10_540_000, 'places' => [
                    ['Wat Arun', LocationType::Landmark, 13.7437, 100.4889, null],
                    ['Chatuchak Weekend Market', LocationType::Other, 13.7999, 100.5500, null],
                    ['Jay Fai', LocationType::Restaurant, 13.7526, 100.5049, null],
                ]],
                'Chiang Mai' => ['lat' => 18.7883, 'lng' => 98.9853, 'population' => 130_000, 'places' => [
                    ['Wat Phra That Doi Suthep', LocationType::Landmark, 18.8047, 98.9216, null],
                    ['Ristr8to Coffee', LocationType::Cafe, 18.7899, 98.9673, null],
                ]],
            ]],
            'Peru' => ['code' => 'PE', 'population' => 34_350_000, 'lat' => -9.1900, 'lng' => -75.0152, 'cities' => [
                'Cusco' => ['lat' => -13.5319, 'lng' => -71.9675, 'population' => 430_000, 'places' => [
                    ['Machu Picchu', LocationType::Attraction, -13.1631, -72.5450, 'The 15th-century Inca citadel set high in the Andes.'],
                    ['Plaza de Armas', LocationType::Other, -13.5183, -71.9781, null],
                ]],
            ]],
        ];

        $regions = [];
        $regionOf = [
            'Chattogram' => 'Chattogram Division',
            'Dhaka' => 'Dhaka Division',
            'Tokyo' => 'Kanto',
            'Kyoto' => 'Kansai',
            'Paris' => 'Ile-de-France',
            'Rome' => 'Lazio',
            'Bangkok' => 'Central Thailand',
            'Chiang Mai' => 'Northern Thailand',
            'Cusco' => 'Cusco Region',
        ];

        foreach ($tree as $countryName => $country) {
            $countryLocation = $this->place(null, LocationType::Country, $countryName, [
                'country_code' => $country['code'],
                'latitude' => $country['lat'],
                'longitude' => $country['lng'],
                'population' => $country['population'],
            ]);
            $locations[] = $countryLocation;

            foreach ($country['cities'] as $cityName => $city) {
                $regionName = $regionOf[$cityName] ?? $cityName.' Region';
                $regionKey = $countryName.'|'.$regionName;

                if (! isset($regions[$regionKey])) {
                    $regions[$regionKey] = $this->place($countryLocation, LocationType::Region, $regionName, [
                        'country_code' => $country['code'],
                        'latitude' => $city['lat'],
                        'longitude' => $city['lng'],
                    ]);
                    $locations[] = $regions[$regionKey];
                }

                $cityLocation = $this->place($regions[$regionKey], LocationType::City, $cityName, [
                    'country_code' => $country['code'],
                    'city_name' => $cityName,
                    'latitude' => $city['lat'],
                    'longitude' => $city['lng'],
                    'population' => $city['population'],
                ]);
                $locations[] = $cityLocation;

                foreach ($city['places'] as [$name, $type, $lat, $lng, $description]) {
                    $locations[] = $this->place($cityLocation, $type, $name, array_filter([
                        'country_code' => $country['code'],
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'description' => $description,
                    ], static fn ($value): bool => $value !== null));
                }
            }
        }

        return $locations;
    }

    /**
     * @param  array<int, Location>  $places
     * @return array<int, Review>
     */
    private function seedReviews(array $places): array
    {
        $byName = collect($places)->keyBy('name');

        $reviews = [
            ['fazley', 'Patenga Beach', 5, 'Sunset here is incredible', 'Golden hour over the Bay of Bengal. Come an hour before sunset and stay for the sea breeze.', ReviewStatus::Approved],
            ['aiko', 'Patenga Beach', 4, 'Windswept and lovely', 'Not for swimming, but the promenade and the views make it worth the trip from the city.', ReviewStatus::Approved],
            ['marco', 'Patenga Beach', 5, 'A must in Chattogram', 'Local snacks along the shore and a gorgeous horizon. Easy to reach by CNG.', ReviewStatus::Approved],
            ['aiko', 'Shibuya Crossing', 5, 'Organised chaos', 'Thousands of people crossing at once, yet it somehow works. Best viewed from the station window.', ReviewStatus::Approved],
            ['fazley', 'Shibuya Crossing', 4, 'Best at night', 'The crowds and the lights together are the whole show. Arrive just after dusk.', ReviewStatus::Approved],
            ['marco', 'Eiffel Tower', 5, 'Iconic for a reason', 'Worth going up at least once. The sparkle on the hour is a nice surprise.', ReviewStatus::Approved],
            ['priya', 'Eiffel Tower', 4, 'Busy but magical', 'Book tickets ahead, go early, and the crowds are manageable.', ReviewStatus::Approved],
            ['priya', 'Fushimi Inari Shrine', 5, 'Walk it early', 'Arrive before sunrise and you can have the torii gates almost to yourself.', ReviewStatus::Approved],
            ['leo', 'Wat Arun', 5, 'Best at sunset', 'Cross the river by ferry and watch the temple glow at golden hour.', ReviewStatus::Approved],
            ['marco', 'Colosseum', 4, 'Layered history', 'A guided visit is worth it — the underground tour is fascinating.', ReviewStatus::Approved],
            ['fazley', 'Machu Picchu', 5, 'A trip of a lifetime', 'Take the first bus up to beat the crowds and watch the mist lift off the terraces.', ReviewStatus::Approved],
            ['priya', 'Ahsan Manzil', 4, 'A pink palace', 'Quietly beautiful and a calm break from the Dhaka traffic.', ReviewStatus::Approved],
            ['leo', 'Tsukiji Outer Market', 5, 'Breakfast of champions', 'Go hungry. The tuna and tamagoyaki skewers are unreal.', ReviewStatus::Approved],
            ['aiko', 'Arashiyama Bamboo Grove', 4, 'Go at dawn', 'Serene if you arrive early; a crush by mid-morning.', ReviewStatus::Approved],
            // Pending (moderation queue)
            ['vera', 'Cafe de Flore', 3, 'Overpriced but historic', 'Lovely atmosphere, steep prices — go for one coffee and the people-watching.', ReviewStatus::Pending],
            ['leo', 'Chatuchak Weekend Market', 4, 'Endless market', 'Bring cash and comfortable shoes; you will get lost, and that is the point.', ReviewStatus::Pending],
        ];

        $models = [];

        foreach ($reviews as [$username, $placeName, $rating, $title, $body, $status]) {
            $location = $byName->get($placeName);
            $user = $this->userByUsername($username);

            if ($location === null || $user === null) {
                continue;
            }

            $models[] = Review::updateOrCreate(
                ['user_id' => $user->id, 'location_id' => $location->id],
                [
                    'rating' => $rating,
                    'title' => $title,
                    'body' => $body,
                    'status' => $status,
                    'visit_date' => now()->subDays(random_int(5, 220)),
                ],
            );
        }

        return $models;
    }

    /**
     * @param  array<int, Review>  $reviews
     */
    private function seedHelpfulVotes(array $reviews): void
    {
        foreach ($reviews as $index => $review) {
            if (! $review->status->isPublic()) {
                continue;
            }

            $voters = collect($this->travellers)
                ->filter(static fn (User $user): bool => $user->id !== $review->user_id)
                ->take(($index % 3) + 1);

            foreach ($voters as $voter) {
                ReviewHelpful::firstOrCreate(['review_id' => $review->id, 'user_id' => $voter->id]);
            }

            $review->forceFill(['helpful_count' => $review->helpfuls()->count()])->save();
        }
    }

    /**
     * @param  array<int, Location>  $places
     * @return array<int, Video>
     */
    private function seedVideos(array $places): array
    {
        $byName = collect($places)->keyBy('name');

        $videos = [
            ['fazley', 'Patenga Beach', 'Sunset at Patenga Beach', 'Golden hour and the sea breeze on the Bay of Bengal.', 42, 1840, VideoStatus::Published],
            ['aiko', 'Shibuya Crossing', 'Crossing Shibuya at night', 'The scramble crossing in full flow after dark.', 28, 3120, VideoStatus::Published],
            ['marco', 'Eiffel Tower', 'Eiffel Tower sparkle', 'The hourly twinkle from the Trocadero.', 55, 2210, VideoStatus::Published],
            ['fazley', 'Sea Breeze Restaurant', 'Street food in Chattogram', 'A quick tour of the snacks near Patenga.', 60, 940, VideoStatus::Published],
            ['priya', 'Fushimi Inari Shrine', '3 minutes under the torii gates', 'A dawn walk up Mount Inari.', 47, 1560, VideoStatus::Published],
            ['leo', 'Wat Arun', 'Sunset over Wat Arun', 'The temple of dawn from the river at golden hour.', 33, 1280, VideoStatus::Published],
            ['priya', 'Machu Picchu', 'Mist lifting over Machu Picchu', 'The first light on the terraces.', 58, 4020, VideoStatus::Published],
            ['marco', 'Colosseum', 'Inside the Colosseum', 'A walk around the arena floor.', 51, 1730, VideoStatus::Published],
            ['aiko', 'Tsukiji Outer Market', 'Breakfast at Tsukiji', 'Tuna, tamagoyaki, and endless stalls.', 39, 2010, VideoStatus::Published],
            ['leo', 'Chatuchak Weekend Market', 'Getting lost in Chatuchak', '15,000 stalls and no map.', 60, 880, VideoStatus::Published],
            // Pending (moderation queue)
            ['vera', 'Cafe de Flore', 'Coffee at Cafe de Flore', 'A quiet morning on the terrace.', 22, 130, VideoStatus::Pending],
        ];

        // Small sample clips committed under database/seeders/assets/videos,
        // cycled across the demo videos so every card and player has real media.
        $assets = ['sunset-patenga', 'shibuya-night', 'eiffel-sparkle', 'chattogram-food', 'fushimi-torii', 'machu-picchu'];

        $models = [];

        foreach ($videos as $index => [$username, $placeName, $title, $description, $duration, $likes, $status]) {
            $location = $byName->get($placeName);
            $user = $this->userByUsername($username);

            if ($location === null || $user === null) {
                continue;
            }

            $video = Video::updateOrCreate(
                ['user_id' => $user->id, 'location_id' => $location->id, 'title' => $title],
                [
                    'description' => $description,
                    'duration' => $duration,
                    'width' => 1080,
                    'height' => 1920,
                    'mime_type' => 'video/mp4',
                    'status' => $status,
                    'visibility' => VideoVisibility::Public,
                    'view_count' => $likes * 6,
                    'share_count' => intdiv($likes, 20),
                    'published_at' => $status === VideoStatus::Published ? now()->subDays(random_int(1, 60)) : null,
                ],
            );

            $this->attachVideoMedia($video, $assets[$index % count($assets)]);

            $models[] = $video;
        }

        return $models;
    }

    /**
     * Copy a committed sample clip + poster onto the video disk and point the
     * record at them so the feed and player have real media.
     */
    private function attachVideoMedia(Video $video, string $asset): void
    {
        $source = database_path("seeders/assets/videos/{$asset}.mp4");

        if (! is_file($source)) {
            return;
        }

        $disk = Storage::disk($video->disk());
        $videoPath = "videos/demo/{$asset}.mp4";
        $disk->put($videoPath, (string) file_get_contents($source));

        $thumbnailSource = database_path("seeders/assets/videos/{$asset}.jpg");
        $thumbnailPath = null;

        if (is_file($thumbnailSource)) {
            $thumbnailPath = "videos/demo/{$asset}.jpg";
            $disk->put($thumbnailPath, (string) file_get_contents($thumbnailSource));
        }

        $video->forceFill([
            'processed_path' => $videoPath,
            'thumbnail_path' => $thumbnailPath,
            'width' => 480,
            'height' => 854,
            'duration' => 4,
            'stored_bytes' => filesize($source),
            'mime_type' => 'video/mp4',
        ])->saveQuietly();
    }

    /**
     * @param  array<int, Video>  $videos
     */
    private function seedVideoInteractions(array $videos): void
    {
        foreach ($videos as $index => $video) {
            if ($video->status !== VideoStatus::Published) {
                continue;
            }

            $fans = collect($this->travellers)
                ->filter(static fn (User $user): bool => $user->id !== $video->user_id)
                ->take(($index % 3) + 1);

            foreach ($fans as $fan) {
                VideoLike::firstOrCreate(['video_id' => $video->id, 'user_id' => $fan->id]);
            }

            if ($index % 2 === 0) {
                SavedVideo::firstOrCreate(['video_id' => $video->id, 'user_id' => $this->viewer->id]);
            }

            $video->forceFill(['like_count' => $video->likes()->count()])->save();
        }
    }

    /**
     * @param  array<int, Location>  $locations
     */
    private function seedEvents(array $locations): void
    {
        $byName = collect($locations)->keyBy('name');

        $events = [
            ['Patenga Beach Cleanup', 'Patenga Beach', 'fazley', 10, EventStatus::Published, 'Community beach clean-up; gloves and bags provided.'],
            ['Shibuya Night Photo Walk', 'Shibuya Crossing', 'aiko', 21, EventStatus::Published, 'A guided walk through the neon, cameras welcome.'],
            ['Eiffel Tower Picnic Meetup', 'Eiffel Tower', 'marco', 35, EventStatus::Published, 'BYO blanket; we meet at the Trocadero at dusk.'],
            ['Wat Arun Sunset Ferry', 'Wat Arun', 'leo', 14, EventStatus::Published, 'Ferry crossing timed for golden hour.'],
            ['Machu Picchu Sunrise Trek', 'Machu Picchu', 'priya', 60, EventStatus::Published, 'Early start to catch first light over the terraces.'],
            ['Rome Street Food Tour', 'Colosseum', 'marco', 7, EventStatus::Published, 'A tasting walk around the old city.'],
            ['Kyoto Tea Ceremony', 'Fushimi Inari Shrine', 'aiko', 18, EventStatus::Pending, 'A traditional tea ceremony in a Kyoto machiya.'],
        ];

        foreach ($events as [$name, $placeName, $organizerUsername, $daysAhead, $status, $description]) {
            $location = $byName->get($placeName);
            $organizer = $this->userByUsername($organizerUsername);

            if ($location === null || $organizer === null) {
                continue;
            }

            Event::updateOrCreate(
                ['name' => $name, 'organizer_id' => $organizer->id, 'location_id' => $location->id],
                [
                    'description' => $description,
                    'starts_at' => now()->addDays($daysAhead)->setTime(9, 0),
                    'ends_at' => null,
                    'address' => $location->address,
                    'status' => $status,
                ],
            );
        }
    }

    /**
     * @param  array<int, Location>  $locations
     */
    private function seedVisits(array $locations): void
    {
        $byName = collect($locations)->keyBy('name');

        $visits = [
            ['fazley', ['Patenga Beach', 'Chattogram', 'Dhaka', 'Shibuya Crossing', 'Machu Picchu']],
            ['aiko', ['Shibuya Crossing', 'Tokyo', 'Kyoto', 'Fushimi Inari Shrine']],
            ['marco', ['Eiffel Tower', 'Paris', 'Colosseum', 'Rome']],
            ['priya', ['Fushimi Inari Shrine', 'Machu Picchu', 'Ahsan Manzil']],
            ['leo', ['Wat Arun', 'Bangkok', 'Chatuchak Weekend Market', 'Chiang Mai']],
        ];

        foreach ($visits as [$username, $names]) {
            $user = $this->userByUsername($username);

            if ($user === null) {
                continue;
            }

            foreach ($names as $name) {
                $location = $byName->get($name);

                if ($location === null) {
                    continue;
                }

                TravellerLocation::updateOrCreate(
                    ['user_id' => $user->id, 'location_id' => $location->id],
                    [
                        'visited_at' => now()->subDays(random_int(10, 300)),
                        'source' => VisitSource::Claimed,
                        'verification_status' => VisitVerification::Unverified,
                    ],
                );
            }
        }
    }

    private function seedFollows(): void
    {
        $byUsername = collect($this->travellers)->keyBy('username');

        $graph = [
            'fazley' => ['aiko', 'marco', 'priya'],
            'aiko' => ['fazley', 'leo'],
            'marco' => ['fazley', 'priya'],
            'priya' => ['fazley', 'aiko'],
            'leo' => ['aiko', 'marco'],
        ];

        foreach ($graph as $follower => $follows) {
            $user = $byUsername->get($follower);

            if ($user === null) {
                continue;
            }

            $ids = collect($follows)->map(fn (string $u): ?int => $byUsername->get($u)?->id)->filter()->all();

            if ($ids !== []) {
                $user->following()->syncWithoutDetaching($ids);
            }
        }
    }

    /**
     * @param  array<int, Review>  $reviews
     * @param  array<int, Video>  $videos
     */
    private function seedReports(array $reviews, array $videos): void
    {
        $targets = [
            [Review::class, $reviews[0] ?? null, 'fazley', ReportReason::Spam, 'Looks like an advert, not a genuine review.', ReportStatus::Open],
            [Review::class, $reviews[2] ?? null, 'leo', ReportReason::Misinformation, 'Claims that seem inaccurate about the location.', ReportStatus::Open],
            [Video::class, $videos[0] ?? null, 'marco', ReportReason::Offensive, 'Music in the clip may be a copyright issue.', ReportStatus::Open],
            [Video::class, $videos[1] ?? null, 'priya', ReportReason::Spam, 'Repeated promotional link in the description.', ReportStatus::Reviewed],
        ];

        foreach ($targets as [$type, $target, $reporterUsername, $reason, $description, $status]) {
            if (! $target instanceof Review && ! $target instanceof Video) {
                continue;
            }

            $reporter = $this->userByUsername($reporterUsername);

            Report::updateOrCreate(
                [
                    'reporter_id' => $reporter?->id,
                    'reportable_type' => $type,
                    'reportable_id' => $target->id,
                ],
                [
                    'reason' => $reason,
                    'description' => $description,
                    'status' => $status,
                    'reviewed_at' => $status === ReportStatus::Open ? null : now()->subDays(2),
                ],
            );
        }
    }

    private function seedImportBatch(): void
    {
        LocationImportBatch::updateOrCreate(
            ['source' => 'geonames', 'dataset_version' => 'demo-'.now()->format('Y-m-d')],
            [
                'status' => 'completed',
                'countries' => ['BD', 'JP', 'FR', 'IT', 'TH', 'PE'],
                'total' => Location::count(),
                'processed' => Location::count(),
                'created' => Location::where('osm_type', 'geonames')->count(),
                'updated' => 0,
                'skipped' => 0,
                'started_at' => now()->subDay(),
                'finished_at' => now()->subDay()->addMinutes(3),
            ],
        );
    }

    private function userByUsername(string $username): ?User
    {
        $user = collect($this->travellers)->firstWhere('username', $username);

        if ($user instanceof User) {
            return $user;
        }

        return $username === $this->viewer->username ? $this->viewer : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function place(?Location $parent, LocationType $type, string $name, array $attributes = []): Location
    {
        return Location::updateOrCreate(
            ['parent_id' => $parent?->id, 'name' => $name],
            array_merge(['type' => $type, 'is_active' => true], $attributes),
        );
    }
}
