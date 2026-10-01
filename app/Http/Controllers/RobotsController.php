<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $sitemap = route('sitemap.index');

        $content = <<<TXT
        User-agent: *
        Allow: /
        Disallow: /admin
        Disallow: /search

        Sitemap: {$sitemap}
        TXT;

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
