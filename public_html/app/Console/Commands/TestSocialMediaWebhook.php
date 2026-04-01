<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestSocialMediaWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'social-media:test {source : whatsapp, instagram, etc} {identifier : phone number or ID} {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the social media webhook logic';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $source = $this->argument('source');
        $identifier = $this->argument('identifier');
        $message = $this->argument('message');

        $request = \Illuminate\Http\Request::create('/webhook/social-media', 'POST', [
            'source' => $source,
            'identifier' => $identifier,
            'message' => $message,
        ]);

        $this->info("Testing webhook for $source with identifier $identifier...");

        $response = app(\App\Http\Controllers\Webhooks\SocialMediaWebhookController::class)->handle($request);

        $this->line("Response: " . $response->getContent());

        return 0;
    }
}
