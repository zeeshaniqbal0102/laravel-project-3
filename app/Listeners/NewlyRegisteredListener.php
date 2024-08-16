<?php

namespace App\Listeners;

use App\Events\NewlyRegistered;
use App\Mail\WelcomeMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NewlyRegisteredListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(NewlyRegistered $event)
    {   
        if($event) {
            Mail::to($event->user->email)->send(new WelcomeMessage($event->user));
        }
    }
}
