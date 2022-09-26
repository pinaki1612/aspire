<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $table = 'loans';
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class,'loan_approved_by','id');
    }

    public function scheduleRepayments(): HasMany
    {
        return $this->hasMany(ScheduledRepayments::class,'loan_id','id')->orderBy('schedule_date');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class,'loan_id','id')->orderBy('id');
    }
}
