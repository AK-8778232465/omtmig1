<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use App\Models\Client;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'oms_users';
    public $timestamps = false;

    protected $fillable = ['username', 'emp_id', 'email', 'password', 'user_type_id', 'is_active'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // 'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function usertypes()
    {
        return $this->belongsTo(UserType::class, 'user_type_id', 'id');
    }

    public static function getAllLowerLevelUserIds($parentId)
    {
        $userIds = collect([$parentId]);

        self::recursiveLowerLevelUserIds($parentId, $userIds);

        return $userIds->toArray();
    }

    protected static function recursiveLowerLevelUserIds($parentId, &$userIds)
    {
        $lowerLevelUserIds = self::where('reporting_to', $parentId)
            ->where('is_active', 1)
            ->pluck('id')
            ->toArray();

        foreach ($lowerLevelUserIds as $lowerLevelUserId) {
            $userIds[] = $lowerLevelUserId;
            self::recursiveLowerLevelUserIds($lowerLevelUserId, $userIds);
        }
    }
}
