<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create(['role' => 'user']);
    }

    public function test_it_applies_a_common_coupon_successfully()
    {
        $commonCoupon = Coupon::create([
            'code' => 'COMMON10',
            'discount' => 10,
            'type' => 'percent',
            'is_common' => true,
            'expires_at' => now()->addDays(10),
        ]);


        $response = $this->actingAs($this->user)->postJson(route('coupons.apply'), [
            'code' => 'COMMON10',
            'total' => 100,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Coupon applied successfully',
                'discount' => 10,
                'new_total' => 90,
            ]);
    }

    public function test_it_applies_a_user_specific_coupon_successfully()
    {
        $userSpecificCoupon = Coupon::create([
            'code' => 'SPECIALUSER10',
            'discount' => 10,
            'type' => 'percent',
            'expires_at' => now()->addDays(10),
        ]);

        $this->user->coupons()->attach($userSpecificCoupon);

        $response = $this->actingAs($this->user)->postJson(route('coupons.apply'), [
            'code' => 'SPECIALUSER10',
            'total' => 100,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Coupon applied successfully',
                'discount' => 10,
                'new_total' => 90,
            ]);
    }

    public function test_it_rejects_invalid_or_expired_coupon()
    {

        $response = $this->actingAs($this->user)->postJson(route('coupons.apply'), [
            'code' => 'INVALIDCOUPON',
            'total' => 100,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid or expired coupon',
            ]);
    }

    public function test_it_rejects_user_specific_coupon_for_non_eligible_user()
    {
        $userSpecificCoupon = Coupon::create([
            'code' => 'SPECIALUSER10',
            'discount' => 10,
            'type' => 'percent',
            'expires_at' => now()->addDays(10),
        ]);


        $response = $this->actingAs($this->user)->postJson(route('coupons.apply'), [
            'code' => 'SPECIALUSER10',
            'total' => 100,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'This coupon is not available for you',
            ]);
    }

    public function test_admin_can_create_coupon()
    {
        
        $response = $this->actingAs($this->admin)->postJson(route('admin.coupons.create'), [
            'code' => 'TEST10',
            'discount' => 10,
            'type' => 'percent',
            'is_common' => false,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'code', 'discount', 'type', 'is_common', 'expires_at', 'created_at', 'updated_at'
            ]);

        $this->assertDatabaseHas('coupons', ['code' => 'TEST10']);
    }

    public function test_admin_can_assign_coupon_to_user()
    {
        $user = User::factory()->create();

        $coupon = Coupon::create([
            'code' => 'TEST10',
            'discount' => 10,
            'type' => 'percent',
            'is_common' => false,
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.coupons.assign', $user->id), ['code' => 'TEST10']);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Coupon assigned successfully']);

        $this->assertDatabaseHas('coupon_user', [
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
        ]);
    }
}
