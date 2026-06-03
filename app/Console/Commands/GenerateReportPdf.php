<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class GenerateReportPdf extends Command
{
    protected $signature = 'remis:generate-report-pdf {type} {landlordId} {outputPath}';
    protected $description = 'Generate a report PDF via CLI PHP (which has working GD for image rendering)';

    public function handle(): int
    {
        $type       = $this->argument('type');
        $landlordId = (int) $this->argument('landlordId');
        $outputPath = $this->argument('outputPath');

        $data = match ($type) {
            'rent-payments' => $this->rentPaymentsData($landlordId),
            'tenants'       => $this->tenantsData($landlordId),
            'overdue'       => $this->overdueData($landlordId),
            'properties'    => $this->propertiesData($landlordId),
            default         => null,
        };

        if ($data === null) {
            $this->error("Unknown report type: {$type}");
            return Command::FAILURE;
        }

        $view = "landlord.reports.pdf.{$type}";

        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');
        $pdf->save($outputPath);

        return Command::SUCCESS;
    }

    private function rentPaymentsData(int $landlordId): array
    {
        $payments = Payment::with(['lease.property', 'lease.unit', 'tenant'])
            ->whereHas('lease', fn($q) => $q->where('landlord_id', $landlordId))
            ->orderBy('due_date', 'desc')
            ->get();

        return compact('payments');
    }

    private function tenantsData(int $landlordId): array
    {
        $tenants = User::with(['leasesAsTenant' => fn($q) => $q->where('landlord_id', $landlordId)
                            ->with('property', 'unit')->latest()])
            ->where('role', 'tenant')
            ->where('landlord_id', $landlordId)
            ->orderBy('name')
            ->get();

        return compact('tenants');
    }

    private function overdueData(int $landlordId): array
    {
        $payments = Payment::with(['lease.property', 'lease.unit', 'tenant'])
            ->whereHas('lease', fn($q) => $q->where('landlord_id', $landlordId))
            ->where(fn($q) => $q->where('status', 'overdue')
                ->orWhere(fn($q2) => $q2->where('status', 'pending')->where('due_date', '<', now()->toDateString())))
            ->orderBy('due_date', 'asc')
            ->get();

        return compact('payments');
    }

    private function propertiesData(int $landlordId): array
    {
        $properties = Property::with('units')
            ->where('landlord_id', $landlordId)
            ->orderBy('name')
            ->get();

        return compact('properties');
    }
}
