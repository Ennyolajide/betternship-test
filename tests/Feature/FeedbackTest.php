<?php

use App\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the dashboard with submitted feedback', function () {
    $name = fake()->name();
    $email = fake()->unique()->safeEmail();

    Feedback::factory()->create(['customer_name' => $name, 'email' => $email]);

    $this->get('/feedbacks')
        ->assertOk()
        ->assertSee($name)
        ->assertSee($email)
        ->assertSee('Pending');
});

it('stores valid feedback via Eloquent and returns JSON', function () {
    $email = fake()->unique()->safeEmail();

    $response = $this->postJson('/feedback', [
        'customer_name' => fake()->name(),
        'email'         => $email,
        'feedback'      => fake()->paragraph(),
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Feedback submitted successfully!')
        ->assertJsonPath('feedback.status', 'Pending');

    $this->assertDatabaseHas('feedback', [
        'email'  => $email,
        'status' => 'Pending',
    ]);
});

it('rejects invalid feedback with 422 validation errors', function () {
    $this->postJson('/feedback', [
        'customer_name' => '',
        'email'         => fake()->word(),
        'feedback'      => fake()->text(15),
    ])->assertStatus(422)
      ->assertJsonStructure(['message', 'errors' => ['customer_name', 'email', 'feedback']]);

    expect(Feedback::count())->toBe(0);
});

it('marks feedback as reviewed and persists the change', function () {
    $feedback = Feedback::factory()->create(['status' => 'Pending']);

    $this->patchJson("/feedback/{$feedback->id}/status")
        ->assertOk()
        ->assertJsonPath('status', 'Reviewed');

    $this->assertDatabaseHas('feedback', [
        'id'     => $feedback->id,
        'status' => 'Reviewed',
    ]);
});
