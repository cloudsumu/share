<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserRequestRating extends Model
{
    use HasFactory, Searchable;

    protected $guarded = [];

    /**
     * The user who created the request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The provider assigned to the request.
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function request()
    {
        return $this->belongsTo(UserRequest::class);
    }
}
