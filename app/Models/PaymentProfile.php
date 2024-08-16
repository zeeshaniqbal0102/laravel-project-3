<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentProfile extends Model
{
    protected $table = 'payment_profiles';

    protected $fillable = 	[
        'user_id', 
        'profile_id', 
        'address', 
        'city',
        'state',
        'zip',
        'country',
        'card_brand',
        'card_last_four',
        'card_expiration_year',
        'card_expiration_month',
    ];
}
