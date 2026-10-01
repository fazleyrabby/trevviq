<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Render the sitemap entry point.
     *
     * Emits a single urlset when the indexable location set fits in one chunk,
     * otherwise a sitemap index referencing the per-page chunk URLs.
     */
    public function index(): Response
    {
        $total = $this->indexableCount();
        $chunk = $this->chunkSize();
        $fingerprint = $this->fingerprint($total);

        $xml = Cache::remember(
            "sitemap:index:{$fingerprint}",
            $this->cacheTtl(),
            function () use ($total, $chunk): string {
                if ($total <= $chunk) {
                    $locations = $this->indexableQuery()
                        ->orderBy('id')
                        ->get();

                    return $this->urlset($locations);
                }

                $pageCount = (int) ceil($total / $chunk);
                $entries = '';

                for ($n = 1; $n <= $pageCount; $n++) {
                    $loc = $this->escape(route('sitemap.page', ['page' => $n]));
                    $entries .= "<sitemap><loc>{$loc}</loc></sitemap>";
                }

                return $this->xmlHeader()
                    .'<sitemapindex xmlns="'.$this->namespace().'">'.$entries.'</sitemapindex>';
            },
        );

        return $this->response($xml);
    }

    /**
     * Render a single 1-indexed sitemap chunk.
     */
    public function page(int $page): Response
    {
        $total = $this->indexableCount();
        $chunk = $this->chunkSize();
        $offset = ($page - 1) * $chunk;

        if ($page < 1 || $offset >= $total) {
            abort(404);
        }

        $fingerprint = $this->fingerprint($total);

        $xml = Cache::remember(
            "sitemap:page:{$page}:{$fingerprint}",
            $this->cacheTtl(),
            function () use ($offset, $chunk): string {
                $locations = $this->indexableQuery()
                    ->orderBy('id')
                    ->offset($offset)
                    ->limit($chunk)
                    ->get();

                return $this->urlset($locations);
            },
        );

        return $this->response($xml);
    }

    /**
     * @return Builder<Location>
     */
    private function indexableQuery(): Builder
    {
        return Location::query()->active()->indexable();
    }

    private function indexableCount(): int
    {
        return $this->indexableQuery()->count();
    }

    private function chunkSize(): int
    {
        return max(1, (int) config('roam.sitemap.chunk_size'));
    }

    private function cacheTtl(): int
    {
        return (int) config('roam.sitemap.cache_ttl');
    }

    /**
     * Cache-busting key component that changes when the indexable set changes.
     */
    private function fingerprint(int $total): string
    {
        $latest = $this->indexableQuery()->max('updated_at');

        return $total.'-'.md5((string) $latest);
    }

    /**
     * @param  Collection<int, Location>  $locations
     */
    private function urlset(Collection $locations): string
    {
        $entries = '';

        foreach ($locations as $location) {
            $loc = $this->escape(route('travel.show', $location->full_slug));
            $entries .= "<url><loc>{$loc}</loc>";

            if ($location->updated_at !== null) {
                $lastmod = $this->escape($location->updated_at->toAtomString());
                $entries .= "<lastmod>{$lastmod}</lastmod>";
            }

            $entries .= '</url>';
        }

        return $this->xmlHeader()
            .'<urlset xmlns="'.$this->namespace().'">'.$entries.'</urlset>';
    }

    private function xmlHeader(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>';
    }

    private function namespace(): string
    {
        return 'http://www.sitemaps.org/schemas/sitemap/0.9';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function response(string $xml): Response
    {
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
