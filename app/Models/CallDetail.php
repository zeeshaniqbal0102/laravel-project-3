<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'caller_number',
        'destination_number',
        'client_id',
        'client_handle',
        'reader_id',
        'reader_handle',
        'cost', 
        'call_minutes', 
        'source',
        'site',
        'status',
        'is_admin_deleted',
        'is_reader_deleted',
        'is_client_deleted',
        'billable_minutes'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:m-d-Y H:i:s'
    ];

    public function user()
    {
        return $this->belongsto('App\Models\User', 'user_id', 'id');
    }

}
