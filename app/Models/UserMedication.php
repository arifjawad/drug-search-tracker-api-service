<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMedication extends Model
{
    protected $fillable = [
        'user_id',
        'rxcui',
        'name',
    ];

    protected $dates = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
