<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalVoiceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['crawler.service_secret' => 'test-internal-secret']);
    }

    public function test_internal_agent_config_rejects_missing_shared_secret(): void
    {
        $this->getJson('/api/v1/agents/config?agent_id=1')
            ->assertUnauthorized();
    }

    public function test_voice_engine_can_load_an_active_agent_with_shared_secret(): void
    {
        $user = User::factory()->create();
        $agent = Agent::create([
            'user_id' => $user->id,
            'name' => 'Example Reception Desk',
            'system_prompt' => 'Answer only from approved business facts.',
            'configuration' => ['services' => [['name' => 'Consultation']]],
            'status' => 'active',
            'phone_number' => '+254700000000',
        ]);

        $this->withHeader('X-Crawler-Secret', 'test-internal-secret')
            ->getJson('/api/v1/agents/config?agent_id='.$agent->id)
            ->assertOk()
            ->assertJsonPath('config.business_name', 'Example Reception Desk')
            ->assertJsonPath('config.user_id', $user->id);
    }

    public function test_internal_payment_status_is_resolved_by_call_reference(): void
    {
        $user = User::factory()->create();
        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 500,
            'phone_number' => '254700000000',
            'transaction_reference' => 'checkout-reference',
            'status' => 'completed',
            'provider' => 'mpesa',
            'metadata' => ['account_ref' => 'call-123'],
        ]);

        $this->withHeader('X-Crawler-Secret', 'test-internal-secret')
            ->getJson('/api/v1/internal/payments/status?call_id=call-123')
            ->assertOk()
            ->assertJson([
                'status' => 'completed',
                'payment_id' => $payment->id,
            ]);
    }
}
