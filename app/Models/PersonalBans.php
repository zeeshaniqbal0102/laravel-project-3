<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalBans extends Model
{
    use HasFactory;

    protected $table = 'personal_bans';

    protected $fillable = [
        'id',
        'reader_id',
        'reader_handle',
        'user_id'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
    ];
     
    public function user()
    {
        return $this->belongsto('App\Models\User', 'user_id', 'id');
    }
}
