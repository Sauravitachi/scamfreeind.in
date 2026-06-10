<?php

namespace App\Models;

class VltradingLead extends ScamLead
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vltrading_leads';

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
        'scam_type_id',
        'customer_description',
        'source',
        'is_duplicate',
        'existing_customer_id',
        'scam_source_id',
        'count',
    ];
}
