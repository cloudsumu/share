<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Scopes\Searchable;

class PeakHour extends Model
{
    use HasFactory, Searchable;

    protected $guarded = [];
}
