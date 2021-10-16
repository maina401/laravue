<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
  //  use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'pages','date','time','amount','instructions','attachment','category','level','user_id'
    ];
    protected $casts=[
        'attachment'=>'array'
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
