<?php

namespace App\Observers;

use App\Models\ScamLead;
use App\Services\ScamLeadService;

class ScamLeadObserver
{
    private ScamLeadService $service;

    public function __construct(ScamLeadService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle the ScamLead "creating" event.
     */
    public function creating(ScamLead $scamLead): bool
    {
        $this->service->fixCountryCodeForIndia($scamLead);

        if ($this->service->isBypassedNumber($scamLead->phone_number)) {
            return false;
        }

        if (null === $scamLead->country_code) {
            $scamLead->country_code = 'in';
        }

        $this->service->sanitizeName($scamLead);
        $this->service->setDialCodeFromCountryCode($scamLead);

        return true;
    }


    /**
     * Handle the ScamLead "saving" event.
     */
    public function saving(ScamLead $scamLead): void
    {
        if ($scamLead->isDirty('country_code')) {
            $this->service->setDialCodeFromCountryCode($scamLead);
        }
    }

    /**
     * Handle the ScamLead "saved" event.
     */
    public function saved(ScamLead $scamLead): void
    {
        $this->service->syncIsDuplicateCallback($scamLead, event: 'update');
        $this->service->syncExistingCustomerCallback($scamLead);
        $this->service->syncErrorsCallback($scamLead);
    }

    /**
     * Handle the ScamLead "deleted" event.
     */
    public function deleted(ScamLead $scamLead): void
    {
        $this->service->syncIsDuplicateCallback($scamLead, event: 'delete');
    }
}
