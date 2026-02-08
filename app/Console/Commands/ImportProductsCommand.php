<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class ImportProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-products-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Products it"s a command for get x products per day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $arrayOfproductsFiles = Explode("\n", Http::get('https://challenges.coode.sh/food/data/json/index.txt')->body());
        $arrayOfproductsFiles = collect($arrayOfproductsFiles)->reject(function (string $value) {
            return empty($value);
        });

        if ($arrayOfproductsFiles->isEmpty()) {
            return;
        }

        $baseUrl = 'https://challenges.coode.sh/food/data/json/';

        $arrayOfproductsFiles->each(function (string $file) use ($baseUrl) {

            $handle = gzopen("{$baseUrl}{$file}", 'r');
            $lastOffset = Cache::get("last-offset-{$file}", 0);
            gzseek($handle, $lastOffset);
            $products = collect([]);

            for ($count = 1; !gzeof($handle) and $count <= 100; $count++) {

                $line = json_decode(gzgets($handle), true);

                if (!$line['code']) continue;

                $products->push([
                    'code' => $line['code'],
                    'product_name' => $line['product_name'],
                    'quantity' => Str::of($line['quantity'])->trim()->isEmpty() ? null : $line['quantity'],
                    'url' => $line['url'],
                    'creator' => $line['creator'],
                    'created_t' => Carbon::createFromTimestamp($line['created_t'])->format('Y-m-d H:i:s'),
                    'created_datetime' => Carbon::parse($line['created_datetime'])->format('Y-m-d H:i:s'),
                    'imported_t' => now()->format('Y-m-d H:i:s'),
                ]);
            }

            if ($products->isNotEmpty()) {

                try {
                    Product::upsert($products->toArray(), ['code'], ['product_name', 'quantity', 'url', 'imported_t']);
                } catch (Throwable $e) {
                    Log::error('Error to import product', [
                        'file' => $e->getFile(),
                        'exception' => $e->getMessage(),
                    ]);
                }

                $offset = gzeof($handle) ? 0 : gztell($handle);
                Cache::forever(Str::lower("last-offset-{$file}"), $offset);
            }

            gzclose($handle);
        });

        Cache::forever('last-cron-updated', now()->format('Y-m-d H:i:s'));
    }
}
