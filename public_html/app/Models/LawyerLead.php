<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LawyerLead extends ScamLead
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lawyer_leads';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'country_code',
        'dial_code',
        'phone_number',
        'scam_amount',
        'problem_type_id',
        'lawyer_id',
        'customer_description',
        'source',
        'is_duplicate',
        'existing_customer_id',
        'scam_source_id',
        'count',
    ];

    /**
     * Get the problem type associated with the lawyer lead.
     */
    public function scamType(): BelongsTo
    {
        return $this->belongsTo(ProblemType::class, 'problem_type_id');
    }

    /**
     * Get the lawyer assigned to this lawyer lead.
     */
    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class, 'lawyer_id');
    }
}
