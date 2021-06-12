<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use App\Classes\Enums\UserRole;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'login',
        'email',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'role_id',
        'role',
        'email_verified_at',
        'current_team_id',
        'profile_photo_path'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    // protected $appends = [
    //     'profile_photo_url',
    // ];
    
    protected $with = ['role'];
    
    public function role()
	{
		return $this->belongsTo('App\Models\Role');
	}
    
    public function hasRole(string $role_name): bool
	{
		$role = $this->role;

        $role_name = strtolower($role_name);
		return $role->name === $role_name;
	}

	public function isSuperUser()
	{
		return $this->hasRole(UserRole::SUPER_USER);
	}

	public function isApiUser()
	{
		return $this->hasRole(UserRole::API_USER);
    }
    
    public function isWebAdmin()
	{
		return $this->hasRole(UserRole::WEB_ADMIN);
    }
    
    public function isWebDefault()
	{
		return $this->hasRole(UserRole::WEB_DEFAULT);
    }
    
    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }
}
