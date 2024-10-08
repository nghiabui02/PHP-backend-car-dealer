<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'username',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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

    public static function register($data)
    {
        $data = Arr::except($data, ['images', 'birthday', 'department_id', 'hire_date', 'salary', 'position']);
        $user = DB::table('users')->insertGetId([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'created_at' => now(),
            'username' => $data['username'],
            'phone_number' => $data['phone_number'],
        ]);
        return DB::table('users')->where('id', $user)->first();
    }

    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
        // TODO: Implement getJWTIdentifier() method.
    }

    public function getJWTCustomClaims(): array
    {
        // TODO: Implement getJWTCustomClaims() method.
        return [];
    }
}
