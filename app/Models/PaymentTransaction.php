<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon;
use DB;
class PaymentTransaction extends Model
{
    protected $table = 'payment_transactions';

    protected $fillable = 	[
        'user_id', 
        'order_number', 
        'currency', 
        'payment_provider',
        'payment_source',
        'payment_method',
        'site',
        'last4',
        'brand',
        'amount',
        'package',
        'credits',
        'status',
        'payment_status'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getMonthly()
    {
        return DB::table('payment_transactions')
                    ->whereYear('created_at','=', date("Y"))
                    ->whereMonth('created_at','=', date("m"))
                    ->sum('amount');
    }

    public function getYearly()
    {
        return DB::table('payment_transactions')
                    ->whereYear('created_at','=', date("Y"))
                    ->sum('amount');
    }

    public function getWeekly()
    {
        $fromDate = Carbon\Carbon::now()->subDay()->startOfWeek()->toDateString();
        $tillDate = Carbon\Carbon::now()->subDay()->toDateString();
        return DB::table('payment_transactions')
                    ->whereYear('created_at','=', date("Y"))
                    ->whereBetween( DB::raw('date(`created_at`)'), [$fromDate, $tillDate] )
                    ->sum('amount');
    }
}
