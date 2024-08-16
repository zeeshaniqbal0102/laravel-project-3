<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BirthdateBan extends Model
{
    use HasFactory;

    protected $table = 'birthdate_bans';

    protected $fillable = [
        'id',
        'birthdate',
        'gender'
    ];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:m-d-Y',
        'updated_at' => 'datetime:m-d-Y',
        'deleted_at' => 'datetime:m-d-Y',
    ];
}
