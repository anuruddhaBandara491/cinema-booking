<?php

namespace App\Console\Commands;

use App\Services\SeatLockService;
use Illuminate\Console\Command;

/**
 * Artisan Command: Clean up expired seat locks
 *
 * Usage: php artisan seats:cleanup-expired-locks
 *
 * This command should be scheduled to run periodically (every 5-10 minutes)
 * to remove expired locks from the database.
 *
 * In app/Console/Kernel.php:
 * protected function schedule(Schedule $schedule)
 * {
 *     $schedule->command('seats:cleanup-expired-locks')->everyFiveMinutes();
 * }
 */
class CleanupExpiredSeatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seats:cleanup-expired-locks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired seat locks from the database';

    protected SeatLockService $seatLockService;

    public function __construct(SeatLockService $seatLockService)
    {
        parent::__construct();
        $this->seatLockService = $seatLockService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Starting cleanup of expired seat locks...');

            $deletedCount = $this->seatLockService->cleanupExpiredLocks();

            if ($deletedCount > 0) {
                $this->info("Successfully removed {$deletedCount} expired lock(s).");
            } else {
                $this->line('No expired locks found.');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Error during cleanup: ' . $e->getMessage());
            return 1;
        }
    }
}
