<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen
        = [
            Registered::class                => [
                SendEmailVerificationNotification::class,
            ],
            \App\Events\CdnWasCreated::class => [
                \App\Listeners\CreateDnsPodRecord::class,
            ],
            \App\Events\CdnWasEdited::class  => [
                \App\Listeners\EditDnsPodRecord::class,
            ],
            \App\Events\CdnWasBatchEdited::class  => [
                \App\Listeners\BatchEditDnsPodRecord::class,
            ],
            \App\Events\CdnWasDelete::class  => [
                \App\Listeners\DeleteDnsPodRecord::class,
            ],
            \App\Events\CdnProviderWasDelete::class  => [
                \App\Listeners\DeleteFullDnsPodRecord::class,
            ],
        ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
