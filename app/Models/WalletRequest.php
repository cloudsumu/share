<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletRequest extends Model
{
    use HasFactory, Searchable;

    protected $guarded = [];

    public function walletRequester()
    {
        return $this->morphTo(__FUNCTION__, 'request_from', 'from_id');
    }
}