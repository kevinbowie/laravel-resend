<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Resend\Laravel\Facades\Resend;

class SendRegisteredNotification implements ShouldQueue
{
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        if ($user->email !== config('mail.mailers.resend.to.dev_address')) {
            throw new \Exception('this mail can be send only for dev address');
        }

        Resend::emails()
            ->send([
                'from' => 'Testing <' . config('mail.from.address') . '>',
                'to' => [$user->email],
                'subject' => 'Welcome to ' . config('app.name'),
                'html' => "<p>hello world</p>",
            ]);
    }
}
