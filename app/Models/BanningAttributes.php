<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BanningAttributes extends Model
{
    use HasFactory;

    protected $table = "banning_attributes";

    protected $fillable = [
        'id', 
        'name',
        'username', 
        'email',
        'dob',
        'ip_address',
        'home_address',
        'credit_card_blocking',
        'reason'
    ];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'dob' => 'datetime:Y-m-d',
        'created_at' => 'datetime:m-d-Y'
    ];
}
