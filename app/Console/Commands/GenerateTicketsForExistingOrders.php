<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateTicketsForExistingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:generate-existing {--dry-run : Simulate without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate individual tickets for existing paid orders that do not have tickets yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 Running in DRY-RUN mode (no changes will be made)');
        } else {
            $this->warn('⚠️  This will create individual ticket records in the database.');
            if (!$this->confirm('Do you wish to continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Find all digital/seminar order items from paid orders that don't have tickets yet
        $orderItems = OrderItem::with(['order', 'product', 'tickets'])
            ->whereHas('product', function($q) {
                $q->whereIn('tipe', ['Digital', 'Seminar']);
            })
            ->whereHas('order', function($q) {
                $q->where('status', 'Paid');
            })
            ->get()
            ->filter(function($item) {
                // Only items without tickets
                return $item->tickets->count() === 0;
            });

        if ($orderItems->isEmpty()) {
            $this->info('✅ No order items without tickets found. Nothing to generate.');
            return 0;
        }

        $totalItems = $orderItems->count();
        $totalTickets = $orderItems->sum('kuantitas');

        $this->info("Found {$totalItems} order items without tickets.");
        $this->info("Total tickets to generate: {$totalTickets}");
        $this->newLine();

        $generatedCount = 0;
        $errorCount = 0;

        $this->output->progressStart($totalItems);

        foreach ($orderItems as $orderItem) {
            try {
                if (!$isDryRun) {
                    // Generate tickets for this order item
                    for ($i = 1; $i <= $orderItem->kuantitas; $i++) {
                        Ticket::create([
                            'ticket_code' => Ticket::generateCode($orderItem->item_id, $i),
                            'order_item_id' => $orderItem->item_id,
                            'sequence' => $i,
                        ]);
                        $generatedCount++;
                    }
                } else {
                    $generatedCount += $orderItem->kuantitas;
                    
                    // Show preview
                    $productName = $orderItem->product->nama_produk ?? 'Unknown';
                    $orderId = $orderItem->order->order_id ?? 'Unknown';
                    for ($i = 1; $i <= min(3, $orderItem->kuantitas); $i++) {
                        $code = Ticket::generateCode($orderItem->item_id, $i);
                        $this->line("  - Would create: {$code} (Order: #{$orderId}, Product: {$productName})");
                    }
                    if ($orderItem->kuantitas > 3) {
                        $this->line("  ... and " . ($orderItem->kuantitas - 3) . " more tickets");
                    }
                }

            } catch (\Exception $e) {
                $this->error("Error for item #{$orderItem->item_id}: " . $e->getMessage());
                Log::error("GenerateTickets Error: " . $e->getMessage());
                $errorCount++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->newLine();
        if ($isDryRun) {
            $this->info("🔍 DRY-RUN Summary:");
            $this->info("   Would generate {$generatedCount} tickets for {$totalItems} order items");
            $this->info("   Errors: {$errorCount}");
            $this->newLine();
            $this->comment("Run without --dry-run to apply changes.");
        } else {
            $this->info("✅ Generated {$generatedCount} tickets successfully!");
            if ($errorCount > 0) {
                $this->warn("⚠️  {$errorCount} errors occurred. Check logs for details.");
            }
        }

        return 0;
    }
}
