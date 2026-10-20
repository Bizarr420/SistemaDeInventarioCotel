<?php

namespace App\Console\Commands;

use App\Services\AuditEventService;
use Illuminate\Console\Command;

class VerifyAuditChainCommand extends Command
{
    protected $signature = 'audit:verify';

    protected $description = 'Verify the database audit event hash chain';

    public function handle(AuditEventService $audit): int
    {
        $result = $audit->verifyChain();
        $this->info("Verified {$result['checked']} audit events.");
        if ($result['invalid_ids'] !== []) {
            $this->error('Invalid chain at event IDs: '.implode(', ', $result['invalid_ids']));

            return self::FAILURE;
        }

        $this->info('No hash-chain inconsistencies detected.');

        return self::SUCCESS;
    }
}
