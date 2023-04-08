<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\SoftDeletes;

class CancelReason extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $guarded = [];
}
