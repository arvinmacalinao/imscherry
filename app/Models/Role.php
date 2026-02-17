<?php

namespace App\Models;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;
    protected $table = 'roles';

	protected $fillable = [
		'name', 'slug'
	];

    public function user_roles()
    {
        return $this->hasMany(UserRole::class);
    }
    
    public function users()
    {
        return $this->hasManyThrough(User::class, UserRole::class);
    }
}


