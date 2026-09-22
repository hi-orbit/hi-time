<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:key:generate {email : The email address of the user to generate an API key for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate (or regenerate) a user\'s API key. The key is shown once.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("No user found with email: {$this->argument('email')}");

            return self::FAILURE;
        }

        $key = $user->generateApiKey();

        $this->info("API key generated for {$user->name} ({$user->email}):");
        $this->line($key);
        $this->warn('Store this key securely - it will not be shown again.');

        return self::SUCCESS;
    }
}
