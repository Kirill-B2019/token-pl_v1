<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Transaction;
use App\Models\UserCard;
use App\Services\TwoCanPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TwoCanPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected TwoCanPaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = app(TwoCanPaymentService::class);
    }

    /** @test */
    public function user_can_view_balance_topup_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('client.balance.topup'));

        $response->assertStatus(200);
        $response->assertViewIs('client.balance.topup');
        $response->assertViewHas(['user', 'minAmount', 'maxAmount']);
    }

    /** @test */
    public function payment_form_validation_works()
    {
        $user = User::factory()->create();

        // Test valid amount
        $response = $this->actingAs($user)->post(route('client.balance.topup.submit'), [
            'amount' => 100.00,
            '_token' => csrf_token(),
        ]);

        // Should redirect (even if payment fails, due to mock)
        $response->assertStatus(302);
    }

    /** @test */
    public function payment_form_validates_minimum_amount()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('client.balance.topup.submit'), [
            'amount' => 5.00, // Below minimum
            '_token' => csrf_token(),
        ]);

        $response->assertSessionHasErrors('amount');
    }

    /** @test */
    public function payment_creation_validates_amount()
    {
        $user = User::factory()->create();

        // Test minimum amount
        $response = $this->actingAs($user)->post(route('client.balance.topup.submit'), [
            'amount' => 5.00, // Below minimum
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors('amount');

        // Test maximum amount
        $response = $this->actingAs($user)->post(route('client.balance.topup.submit'), [
            'amount' => 100000.00, // Above maximum
            '_token' => csrf_token(),
        ]);
        $response->assertSessionHasErrors('amount');
    }

    /** @test */
    public function user_can_view_payment_success_page()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'payment_reference' => 'test_payment_123',
        ]);

        $response = $this->actingAs($user)->get(route('client.payment.success', [
            'payment_id' => 'test_payment_123'
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('client.payment.success');
        $response->assertViewHas('transaction');
    }

    /** @test */
    public function user_can_view_payment_fail_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('client.payment.fail', [
            'error' => 'Payment failed'
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('client.payment.fail');
        $response->assertViewHas('error');
    }

    /** @test */
    public function balance_rub_methods_work_correctly()
    {
        $user = User::factory()->create(['balance_rub' => 100.00]);

        // Test add balance
        $user->addRubBalance(50.00);
        $this->assertEquals(150.00, $user->fresh()->balance_rub);

        // Test subtract balance
        $user->subtractRubBalance(30.00);
        $this->assertEquals(120.00, $user->fresh()->balance_rub);

        // Test has enough balance
        $this->assertTrue($user->hasEnoughRubBalance(100.00));
        $this->assertFalse($user->hasEnoughRubBalance(200.00));

        // Test formatted balance
        $this->assertEquals('120.00 ₽', $user->formatted_rub_balance);
    }

    /** @test */
    public function payment_service_creates_transaction()
    {
        $user = User::factory()->create(['balance_rub' => 0]);

        // Mock HTTP client
        Http::shouldReceive('timeout')->andReturnSelf();
        Http::shouldReceive('post')->andReturn(
            new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                    'success' => true,
                    'payment_id' => 'test_payment_123',
                    'payment_url' => 'https://2can.ru/payment/test_payment_123',
                ]))
            )
        );

        $service = app(TwoCanPaymentService::class);
        $result = $service->createPayment($user, 100.00);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'deposit',
            'deposit_type' => 'rub',
            'amount' => 100.00,
            'status' => 'pending',
            'payment_reference' => $result['payment_id'],
        ]);
    }

    /** @test */
    public function cards_attach_route_exists()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('client.cards.attach'));

        $response->assertStatus(200);
        $response->assertViewIs('client.cards.attach');
    }

    /** @test */
    public function user_can_view_cards_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('client.cards.index'));

        $response->assertStatus(200);
        $response->assertViewIs('client.cards.index');
    }

    /** @test */
    public function card_form_validation_works()
    {
        $user = User::factory()->create();

        // Test invalid card number format
        $response = $this->actingAs($user)->post(route('client.cards.attach.submit'), [
            'number' => '123', // Too short
            'expiry' => '12/25',
            'cvv' => '123',
            '_token' => csrf_token(),
        ]);

        $response->assertSessionHasErrors('number');

        // Test valid card data (should pass basic validation)
        $response = $this->actingAs($user)->post(route('client.cards.attach.submit'), [
            'number' => '4111111111111111', // Valid format
            'expiry' => '12/25',
            'cvv' => '123',
            '_token' => csrf_token(),
        ]);

        // Should redirect (even if tokenization fails, due to mock)
        $response->assertStatus(302);
    }

    /** @test */
    public function user_card_methods_work_correctly()
    {
        $user = User::factory()->create();
        $card = \App\Models\UserCard::factory()->create([
            'user_id' => $user->id,
            'expiry_month' => 12,
            'expiry_year' => 2025,
        ]);

        // Test masked number
        $this->assertEquals($card->card_mask, $card->masked_number);

        // Test formatted expiry
        $this->assertEquals('12/2025', $card->formatted_expiry);

        // Test not expired
        $this->assertFalse($card->isExpired());

        // Test expired
        $expiredCard = \App\Models\UserCard::factory()->create([
            'user_id' => $user->id,
            'expiry_month' => 1,
            'expiry_year' => 2020,
        ]);
        $this->assertTrue($expiredCard->isExpired());
    }
}