<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as CommandAlias;

class FixBookingInvitations extends Command
{
    protected $signature = 'booking:fix-invitations';
    protected $description = 'Set prepaid_paid=1 and prepaid_paid_status=paid for all booking invitations';

    public function handle(): int
    {
        $updated = DB::table('bc_booking_hunter_invitations')
            ->update([
                'prepayment_paid' => 1,
                'prepayment_paid_status' => 'paid',
            ]);

        $this->info("Updated {$updated} records.");

        return CommandAlias::SUCCESS;
    }
}
