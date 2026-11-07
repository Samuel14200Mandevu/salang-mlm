<?php

namespace Tests\Feature\Notifications;

use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\ContactMessageReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    private function seedContactNotification(User $user): string
    {
        $message = ContactMessage::query()->create([
            'name' => 'Alice Test',
            'email' => 'alice@example.com',
            'phone' => '+243900000001',
            'subject' => 'Partenariat',
            'message' => 'Je souhaite discuter d’un partenariat commercial avec Salang.',
            'status' => ContactMessage::STATUS_NEW,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
        ]);

        $user->notifyNow(new ContactMessageReceivedNotification($message));

        return $user->notifications()->firstOrFail()->id;
    }

    public function test_user_can_open_notification_center(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Centre de notifications');
    }

    public function test_user_can_view_notification_details_and_mark_read(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $id = $this->seedContactNotification($user);

        $this->assertNull($user->notifications()->find($id)?->read_at);

        $this->actingAs($user)
            ->get(route('notifications.show', $id))
            ->assertOk()
            ->assertSee('Nouveau message contact')
            ->assertSee('Alice Test')
            ->assertSee('alice@example.com')
            ->assertSee('Partenariat');

        $this->assertNotNull($user->notifications()->find($id)?->read_at);
    }

    public function test_user_cannot_view_another_users_notification(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $other = User::factory()->create(['is_active' => true]);
        $id = $this->seedContactNotification($owner);

        $this->actingAs($other)
            ->get(route('notifications.show', $id))
            ->assertNotFound();
    }

    public function test_unread_filter_lists_unread_items(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->seedContactNotification($user);

        $this->actingAs($user)
            ->get(route('notifications.index', ['status' => 'unread']))
            ->assertOk()
            ->assertSee('Nouveau message contact');
    }
}
