<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledRepayments extends Model
{
    use HasFactory;

    protected $table = 'scheduled_repayments';
    protected $primaryKey = 'id';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];
/*
    public function user()
    {
        return $this->belongsToMany(User::class, 'lead_users','lead_id','user_id' )
            //->withPivot('is_admin')
            ->withTimestamps();
    }
    public function media()
    {
        return $this->belongsToMany(Media::class, 'lead_attachment', 'lead_id','media_id' )
            ->withTimestamps();
    }
*/
}
