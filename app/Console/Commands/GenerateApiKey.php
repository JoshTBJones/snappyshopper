<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organisation;
use App\Models\ApiKey;
use Illuminate\Support\Facades\DB;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-api-key {organisation_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an API key for an organisation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Generate a random 32 byte key
            $apiKey = bin2hex(random_bytes(32));

            //Validate the organisation ID
            $organisationId = $this->argument('organisation_id');
            if (!Organisation::find($organisationId)) {
                $this->error('Organisation not found');
                return;
            }

            // Start a transaction
            DB::beginTransaction();

            // Delete old API keys for this organisation
            ApiKey::where('organisation_id', $organisationId)->delete();

            // Create API key record with hashed key
            ApiKey::create([
                'key' => hash('sha256', $apiKey),
                'organisation_id' => $organisationId
            ]);

            $this->info('API Key generated successfully!');
            $this->info('Please copy this key and store it securely - it cannot be recovered:');
            $this->line($apiKey);
            $this->warn('This key will not be shown again.');

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();
            $this->error('Failed to generate API key: ' . $e->getMessage());
            return 1;
        }
    }
}
