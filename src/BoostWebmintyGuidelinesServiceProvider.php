<?php

declare(strict_types=1);

namespace Webminty\BoostWebmintyGuidelines;

use Illuminate\Support\ServiceProvider;

class BoostWebmintyGuidelinesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Boost auto-discovers resources/boost/ directory
    }
}
