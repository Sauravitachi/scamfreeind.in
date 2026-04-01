<?php

namespace App\Jobs\Whatsapp;

use App\Enums\ScamAssigneeType;
use App\Models\Scam;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAssignMessageToCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    protected int $scamId;

    protected string $assigneeType;

    public function __construct(int $scamId, string $assingeeType)
    {
        $this->scamId = $scamId;
        $this->assigneeType = $assingeeType;
    }

    public function handle(): void
    {
        $scam = Scam::with([
            'customer',
            'draftingAssignee:id,name,dial_code,phone_number',
        ])->find($this->scamId, ['id', 'customer_id', 'sales_assignee_id', 'drafting_assignee_id']);

        $assigneeType = ScamAssigneeType::tryFrom($this->assigneeType);
        $templateName = $assigneeType->assignWhatsappTemplateName();
        $assignee = $scam->{$assigneeType->value.'Assignee'};

        if (! $scam || ! $assignee || ! $templateName) {
            return;
        }

        $customer = $scam->customer;

        $customer->sendWhatsappMessage($templateName, [
            'name' => $customer->whatsappName,
            "{$assigneeType->value}_case_manager_name" => $assignee->name,
            "{$assigneeType->value}_case_manager_mobile_number" => $assignee->whatsappPhoneNumber,
        ]);
    }
}
