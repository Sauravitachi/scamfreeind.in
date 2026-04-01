<?php

namespace App\Notifications;

use App\Models\CustomerEnquiry;
use App\Services\CustomerEnquiryService;
use App\Utilities\Structure;
use Illuminate\Notifications\Notification;

class CustomerEnquiryNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public CustomerEnquiry $customerEnquiry
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = 'Customer Enquiry Received!';
        $message = CustomerEnquiryService::getInstance()->getEnquiryTitle($this->customerEnquiry);

        return Structure::notificationData(
            title: $title,
            message: $this->customerEnquiry->remark ? "$message | ".$this->customerEnquiry->remark : $message,
            link: route('admin.customer-enquiries.show', $this->customerEnquiry)
        );
    }
}
