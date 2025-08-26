<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Models\InteracstsWithModelCaching;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasRoles;
    use InteracstsWithModelCaching;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
        // return $this->hasAnyRole(['super_admin', 'Super Admin', 'super admin']) ?
        //     true :
        //     str_ends_with($this->email, '@ecco.com.do') || str_ends_with($this->email, '@eccocorpbpo.do')
        //     ;
    }

    public function receivedChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    public function unreadReceivedChats(): HasMany
    {
        return $this->receivedChats()->whereNull('read_at');
    }

    public function sentChats(): HasMany
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function unreadSentChats(): HasMany
    {
        return $this->sentChats()->whereNull('read_at');
    }

    public function unreadChatsCountForUser(User $receiver): int
    {
        return $this->unreadSentChats()
            ->where('receiver_id', $receiver->id)
            ->count();
    }
}
