<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

class FixCustomerUnixIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:fix-unix-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix customer unix_id yang masih berformat urut (TIX000001) menjadi format random (TIX835770912526)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mencari customers dengan unix_id berformat urut (TIX00000...)...');
        
        // Cari customers yang unix_id-nya masih berformat urut (TIX00000x atau kosong)
        $customers = Customer::where(function($query) {
            $query->where('unix_id', 'LIKE', 'TIX00000%')
                  ->orWhereNull('unix_id')
                  ->orWhere('unix_id', '');
        })->get();
        
        if ($customers->isEmpty()) {
            $this->info('Tidak ada customer yang perlu diperbaiki.');
            return 0;
        }
        
        $this->info("Ditemukan {$customers->count()} customer yang perlu diperbaiki.");
        
        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();
        
        $fixed = 0;
        foreach ($customers as $customer) {
            $oldId = $customer->unix_id;
            $newId = $this->generateUnixId();
            
            $customer->unix_id = $newId;
            $customer->save();
            
            $fixed++;
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("Berhasil memperbaiki {$fixed} customer unix_id.");
        
        return 0;
    }
    
    /**
     * Generate unique unix_id format: TIX + 12 digit random
     */
    private function generateUnixId(): string
    {
        do {
            // Format: TIX + 12 digit random (kombinasi timestamp + random)
            $timestamp = substr((string) round(microtime(true) * 1000), -9);
            $random = str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
            $unixId = 'TIX' . $timestamp . $random;
        } while (Customer::where('unix_id', $unixId)->exists());

        return $unixId;
    }
}
