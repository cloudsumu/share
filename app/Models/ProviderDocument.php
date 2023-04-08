<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProviderDocument extends Model
{
    use HasFactory, Searchable;
    use SoftDeletes;

    protected $guarded = [];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
