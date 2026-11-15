<?php

namespace Tests\Feature\Notifications;

use App\Models\Publication;
use App\Models\User;
use App\Notifications\PublicationPublishedNotification;
use App\Services\PublicationMemberNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicationPublishedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifier_sends_to_active_members_only(): void
    {
        Notification::fake();

        $member = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
        ]);
        $inactive = User::factory()->create([
            'is_active' => false,
            'user_type' => 'member',
        ]);

        $publication = Publication::query()->create([
            'user_id' => $member->id,
            'type' => Publication::TYPE_EVENT,
            'title' => 'Convention Salang',
            'description' => 'RDV réseau',
            'is_published' => true,
        ]);

        $sent = app(PublicationMemberNotifier::class)->notifyAllMembers($publication);

        $this->assertSame(1, $sent);
        Notification::assertSentTo($member, PublicationPublishedNotification::class);
        Notification::assertSentTo($member, PublicationPublishedNotification::class, function ($notification, $channels) use ($member) {
            $via = $notification->via($member);

            return in_array('mail', $via, true) && in_array('database', $via, true);
        });
        Notification::assertNotSentTo($inactive, PublicationPublishedNotification::class);
    }

    public function test_publication_notification_expires_after_24_hours_and_is_pruned(): void
    {
        $member = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
        ]);

        $publication = Publication::query()->create([
            'user_id' => $member->id,
            'type' => Publication::TYPE_PROMOTION,
            'title' => 'Promo flash',
            'is_published' => true,
        ]);

        $member->notifyNow(new PublicationPublishedNotification(
            $publication,
            now()->subMinute()
        ));

        $this->assertSame(1, $member->notifications()->count());

        $notification = $member->notifications()->first();
        $this->assertTrue(PublicationPublishedNotification::isExpired($notification));

        Artisan::call('notifications:prune-expired-publications');

        $this->assertSame(0, $member->notifications()->count());
    }

    public function test_draft_publication_does_not_notify(): void
    {
        Notification::fake();

        User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
        ]);

        $author = User::factory()->create([
            'is_active' => true,
            'user_type' => 'member',
        ]);

        $publication = Publication::query()->create([
            'user_id' => $author->id,
            'type' => Publication::TYPE_EVENT,
            'title' => 'Brouillon',
            'is_published' => false,
        ]);

        $sent = app(PublicationMemberNotifier::class)->notifyAllMembers($publication);

        $this->assertSame(0, $sent);
        Notification::assertNothingSent();
    }
}
