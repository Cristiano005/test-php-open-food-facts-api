<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
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
            // gzseek($handle, 580295);
            $count = 1;

            while (!gzeof($handle) and $count <= 100) {

                $line = json_decode(gzgets($handle));

                $validator = Validator::make(collect($line)->toArray(), [
                    'code' => ['required', 'string', 'max:25', 'unique:products'],
                ]);

                if ($validator->fails()) {
                    continue;
                }

                try {

                    Product::create([
                        'code' => $line->code,
                        'product_name' => $line->product_name,
                        'quantity' => Str::of($line->quantity)->trim()->isEmpty() ? null : $line->quantity,
                        'url' => $line->url,
                        'creator' => $line->creator,
                        'created_t' => Carbon::createFromTimestamp($line->created_t)->format('Y-m-d H:i:s'),
                        'created_datetime' => Carbon::parse($line->created_datetime)->format('Y-m-d H:i:s'),
                        'imported_t' => Carbon::createFromTimestamp(now())->format('Y-m-d H:i:s'),
                    ]);
                } catch (Throwable $e) {

                    Log::error('Error to import product', [
                        'product_code' => $line->code,
                        'file' => $e->getFile(),
                        'exception' => $e->getMessage(),
                    ]);

                    continue;
                }

                $count++;
            }

            // $finalLineInBytes = gztell($handle); // 580295

            gzclose($handle);
        });
    }
}
