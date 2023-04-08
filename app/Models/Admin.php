<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Scopes\Searchable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, Searchable, HasRoles;
    
    /**
     * Contains the role of this Model.
     * @var string
     */
    protected $role = "admin";

    /**
     * Contains the elements that should be not be Mass assignable.
     * @var array
     */
    protected $guarded = [];

    /**
     * Returns the user for this Model.
     * 
     * @return String
     */
    public function getRoleAttribute()
    {
        return $this->role;
    }
}
