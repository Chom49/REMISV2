<?php

namespace App\Console\Commands;

use App\Models\Lease;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class GenerateLeasePdf extends Command
{
    protected $signature = 'remis:generate-pdf {leaseId} {outputPath}';
    protected $description = 'Generate a lease PDF using CLI PHP (which has working GD for image rendering)';

    public function handle(): int
    {
        $leaseId    = (int) $this->argument('leaseId');
        $outputPath = $this->argument('outputPath');

        $lease = Lease::with('property', 'unit', 'tenant', 'landlord')->find($leaseId);

        if (! $lease) {
            $this->error("Lease {$leaseId} not found.");
            return Command::FAILURE;
        }

        $pdf = Pdf::loadView('landlord.leases.pdf', compact('lease'))
                  ->setPaper('a4', 'portrait')
                  ->setOption('defaultFont', 'serif')
                  ->setOption('isRemoteEnabled', false)
                  ->setOption('isHtml5ParserEnabled', true)
                  ->setOption('isFontSubsettingEnabled', true);

        $pdf->save($outputPath);

        return Command::SUCCESS;
    }
}
