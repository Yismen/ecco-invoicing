<?php

use App\Models\Chat;
use App\Models\Project;
use App\Models\User;

it('save correct fields', function () {
    $data = Chat::factory()->make();

    Chat::create($data->toArray());

    $this->assertDatabaseHas(Chat::class, $data->only([
        'sender_id',
        'receiver_id',
        'message',
        // 'sent_at',
        'read_at'
    ]));
});

it('belongs to a sender', function () {
    $data = Chat::factory()->make();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->sender()
    );

    $this->assertInstanceOf(
        User::class,
        $data->sender
    );
});

it('belongs to a receiver', function () {
    $data = Chat::factory()->make();

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
        $data->receiver()
    );

    $this->assertInstanceOf(
        User::class,
        $data->receiver
    );
});

it('can scope read messages', function () {
    $data = Chat::factory()->create([
        'read_at' => now()
    ]);

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Builder::class,
        $data->newQuery()->read()
    );

    $this->assertTrue(
        Chat::read()->where('id', $data->id)->exists()
    );
});

it('can scope unread messages', function () {
    $data = Chat::factory()->create([
        'read_at' => null
    ]);

    $this->assertInstanceOf(
        \Illuminate\Database\Eloquent\Builder::class,
        $data->newQuery()->unread()
    );

    $this->assertTrue(
        Chat::unread()->where('id', $data->id)->exists()
    );
});

it('updates sent_at when message is sent', function () {
    $data = Chat::factory()->create([
        'sent_at' => null
    ]);

    $this->assertDatabaseHas(Chat::class, [
        'id' => $data->id,
        'sent_at' => now()
    ]);
});
