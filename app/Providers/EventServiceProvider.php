<?php

namespace App\Providers;

use App\Events\MovieCreated;
use App\Listeners\AssociateGenre;
use App\Listeners\AssociatePeople;
use App\Listeners\StoreMoviePoster;
use App\Listeners\StoreMovieBackdrop;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        MovieCreated::class => [
            StoreMoviePoster::class,
            StoreMovieBackdrop::class,
            AssociateGenre::class,
            AssociatePeople::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
