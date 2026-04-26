<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderItem;
use App\Models\Bundle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixBundleOrderSubtotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:fix-bundle-subtotals {--dry-run : Simulate without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix subtotal values for bundle order items that were saved with 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 Running in DRY-RUN mode (no changes will be made)');
        } else {
            $this->warn('⚠️  This will update order item subtotals in the database.');
            if (!$this->confirm('Do you wish to continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Find all order items that belong to a bundle and have subtotal = 0
        $bundleOrderItems = OrderItem::whereNotNull('bundle_id')
            ->where('subtotal', 0)
            ->with(['product', 'order'])
            ->get();

        if ($bundleOrderItems->isEmpty()) {
            $this->info('✅ No bundle order items with subtotal = 0 found. Nothing to fix.');
            return 0;
        }

        $this->info("Found {$bundleOrderItems->count()} bundle order items with subtotal = 0");
        
        // Group by order and bundle
        $grouped = $bundleOrderItems->groupBy(function ($item) {
            return $item->order_id . '_' . $item->bundle_id;
        });

        $fixedCount = 0;
        $errorCount = 0;

        $this->output->progressStart($grouped->count());

        foreach ($grouped as $key => $items) {
            [$orderId, $bundleId] = explode('_', $key);
            
            try {
                $bundle = Bundle::with('items.product')->find($bundleId);
                
                if (!$bundle) {
                    $this->warn("Bundle #{$bundleId} not found for order #{$orderId}. Skipping.");
                    $errorCount++;
                    $this->output->progressAdvance();
                    continue;
                }

                // Get the order to know the quantity ordered
                $order = $items->first()->order;
                
                // Calculate qty based on first item (all items in same bundle have same multiplier)
                $firstBundleItem = $bundle->items->first();
                if ($firstBundleItem) {
                    $firstOrderItem = $items->where('product_id', $firstBundleItem->product_id)->first();
                    if ($firstOrderItem && $firstBundleItem->quantity > 0) {
                        $qty = $firstOrderItem->kuantitas / $firstBundleItem->quantity;
                    } else {
                        $qty = 1;
                    }
                } else {
                    $qty = 1;
                }

                $bundleTotalPrice = $bundle->price * $qty;
                
                // Calculate total original price of all items in bundle
                $originalItemsTotal = 0;
                foreach ($bundle->items as $bundleItem) {
                    $originalItemsTotal += ($bundleItem->product->harga ?? 0) * $bundleItem->quantity;
                }

                // Update each order item with proportional subtotal
                foreach ($items as $orderItem) {
                    $bundleItem = $bundle->items->where('product_id', $orderItem->product_id)->first();
                    
                    if (!$bundleItem) {
                        continue;
                    }

                    // Calculate proportional subtotal
                    if ($originalItemsTotal > 0) {
                        $itemOriginalPrice = ($bundleItem->product->harga ?? 0) * $bundleItem->quantity;
                        $itemSubtotal = ($itemOriginalPrice / $originalItemsTotal) * $bundleTotalPrice;
                    } else {
                        // If all items are free, distribute evenly
                        $bundleItemsCount = $bundle->items->count();
                        $itemSubtotal = $bundleItemsCount > 0 ? ($bundleTotalPrice / $bundleItemsCount) : 0;
                    }

                    $newSubtotal = round($itemSubtotal);

                    if (!$isDryRun) {
                        $orderItem->update(['subtotal' => $newSubtotal]);
                    }

                    $productName = optional($orderItem->product)->nama_produk;
                    if (!$productName) $productName = 'Unknown';
                    $this->line("  - Order #{$orderId}, Product: {$productName} -> Rp " . number_format($newSubtotal, 0, ',', '.'));
                    $fixedCount++;
                }

            } catch (\Exception $e) {
                $this->error("Error processing bundle #{$bundleId} for order #{$orderId}: " . $e->getMessage());
                Log::error("FixBundleOrderSubtotals Error: " . $e->getMessage());
                $errorCount++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->newLine();
        if ($isDryRun) {
            $this->info("🔍 DRY-RUN Summary:");
            $this->info("   Would fix {$fixedCount} order items");
            $this->info("   Errors: {$errorCount}");
            $this->newLine();
            $this->comment("Run without --dry-run to apply changes.");
        } else {
            $this->info("✅ Fixed {$fixedCount} order items successfully!");
            if ($errorCount > 0) {
                $this->warn("⚠️  {$errorCount} errors occurred. Check logs for details.");
            }
        }

        return 0;
    }
}
