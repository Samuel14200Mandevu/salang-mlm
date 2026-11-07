<?php

namespace Tests\Feature\Contact;

use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\ContactMessageReceivedNotification;
use App\Services\ContactSpamGuard;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContactFormSpamGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    private function validToken(int $secondsAgo = 5): string
    {
        return Crypt::encryptString(json_encode([
            't' => now()->subSeconds($secondsAgo)->timestamp,
            'n' => 'testtokn',
        ]));
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'phone' => '+243900000000',
            'subject' => ContactSpamGuard::SUBJECTS[0],
            'message' => 'Bonjour, je souhaite des informations sur l’adhésion et le fonctionnement du plan.',
            'website' => '',
            'company_url' => '',
            'form_token' => $this->validToken(),
        ], $overrides);
    }

    public function test_contact_page_includes_form_token_and_honeypots(): void
    {
        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('name="form_token"', false);
        $response->assertSee('name="website"', false);
        $response->assertSee('name="company_url"', false);
    }

    public function test_legitimate_message_is_stored(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_active' => true, 'email' => 'admin@gmail.com']);
        $admin->assignRole('admin');
        $it = User::factory()->create(['is_active' => true, 'email' => 'itmanager@gmail.com']);
        $it->assignRole('it_manager');
        $cashier = User::factory()->create(['is_active' => true]);
        $cashier->assignRole('cashier');

        $response = $this->from(route('contact'))
            ->post(route('contact.store'), $this->validPayload());

        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('contact_messages', 1);
        $this->assertDatabaseHas('contact_messages', [
            'email' => 'jean.dupont@example.com',
            'status' => ContactMessage::STATUS_NEW,
        ]);

        Notification::assertSentTo($admin, ContactMessageReceivedNotification::class);
        Notification::assertSentTo($it, ContactMessageReceivedNotification::class);
        Notification::assertNotSentTo($cashier, ContactMessageReceivedNotification::class);
    }

    public function test_honeypot_filled_is_silently_rejected(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $response = $this->from(route('contact'))
            ->post(route('contact.store'), $this->validPayload([
                'website' => 'https://spam.example',
            ]));

        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('contact_messages', 0);
        Notification::assertNothingSent();
    }

    public function test_too_fast_submission_is_silently_rejected(): void
    {
        $response = $this->from(route('contact'))
            ->post(route('contact.store'), $this->validPayload([
                'form_token' => $this->validToken(1),
            ]));

        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_seo_spam_content_is_silently_rejected(): void
    {
        $response = $this->from(route('contact'))
            ->post(route('contact.store'), $this->validPayload([
                'name' => 'SEO Agency',
                'email' => 'outreach@amazingleadgen.com',
                'message' => 'We offer customized SEO services to boost your website and improve Google rankings with measurable improvements.',
            ]));

        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_duplicate_submission_is_silently_rejected(): void
    {
        $payload = $this->validPayload();

        $this->from(route('contact'))
            ->post(route('contact.store'), $payload)
            ->assertRedirect(route('contact'));

        $this->assertDatabaseCount('contact_messages', 1);

        $this->from(route('contact'))
            ->post(route('contact.store'), $payload)
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_invalid_subject_fails_validation(): void
    {
        $response = $this->from(route('contact'))
            ->post(route('contact.store'), $this->validPayload([
                'subject' => 'Offre SEO gratuite',
            ]));

        $response->assertRedirect(route('contact'));
        $response->assertSessionHasErrors('subject');
        $this->assertDatabaseCount('contact_messages', 0);
    }
}
