<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispute extends Model
{
    use HasFactory,Searchable, SoftDeletes;

    protected $guarded = [];
    /**
     * Get the Type of Dispute.
     */
    public function disputeType()
    {
        return $this->hasOne(DisputeType::class, 'id', 'dispute_type_id');
    }

    public function user()
    {
        return $this->morphTo(__FUNCTION__, 'from', 'user_id');
    }
}
