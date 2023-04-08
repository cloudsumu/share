<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\Searchable;

class DisputeType extends Model
{
    use HasFactory, Searchable;

    protected $guarded = [];
    
    /**
     * Get the Disputes of this type.
     */
    public function dispute()
    {
        return $this->hasMany(Dispute::class);
    }
}
