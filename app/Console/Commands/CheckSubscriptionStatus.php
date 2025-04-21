<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckSubscriptionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check subscription statuses and create notifications';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();
        $threeDaysFromNow = $now->copy()->addDays(3);

        // Check for expired subscriptions
        $expiredSubscriptions = Subscription::where('expires_at', '<', $now)
            ->where('status', '!=', 'inactive')
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            // Update status to inactive
            $subscription->update(['status' => 'inactive']);

            // Create notification
            Notification::create([
                'subscription_id' => $subscription->id,
                'type' => 'expired',
                'message' => "Subscription for {$subscription->user->name} has expired."
            ]);
        }

        // Check for subscriptions expiring in 3 days
        $expiringSubscriptions = Subscription::whereBetween('expires_at', [$now, $threeDaysFromNow])
            ->where('status', 'active')
            ->get();

        foreach ($expiringSubscriptions as $subscription) {
            // Check if notification already exists
            $existingNotification = Notification::where('subscription_id', $subscription->id)
                ->where('type', 'expiring')
                ->where('created_at', '>', $now->subDay())
                ->first();

            if (!$existingNotification) {
                Notification::create([
                    'subscription_id' => $subscription->id,
                    'type' => 'expiring',
                    'message' => "Subscription for {$subscription->user->name} will expire in 3 days."
                ]);
            }
        }

        $this->info('Subscription status check completed.');
    }
}
