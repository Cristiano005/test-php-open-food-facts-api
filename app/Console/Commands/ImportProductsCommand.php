<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ImportProductsCommand extends Command
{
    protected $signature = 'app:import-products-command';
    protected $description = 'Import Products it"s a command for get x products per day';

    const string filesURL = 'https://challenges.coode.sh/food/data/json/index.txt';
    const string fileURL = 'https://challenges.coode.sh/food/data/json/';

    public function handle()
    {
        $productsFiles = $this->extractFiles();

        if ($productsFiles->isEmpty()) return;

        try {
            $this->insertProductsByFile($productsFiles);
        } catch (Throwable $e) {
            Log::error('Error to import product', [
                'file' => $e->getFile(),
                'exception' => $e->getMessage(),
            ]);
        }

        Cache::forever('last-cron-updated', now()->format('Y-m-d H:i:s'));
    }

    private function extractFiles(): Collection
    {
        $productsFiles = explode("\n", Http::get(self::filesURL)->body());

        $productsFiles = collect($productsFiles)->filter(function (string $value) {
            return !empty($value);
        });

        return $productsFiles;
    }

    private function insertProductsByFile(Collection $productsFiles): void
    {
        $productsFiles->each(function (string $file) {

            $stream = gzopen(self::fileURL . $file, 'r');

            $lastOffset = Cache::get("last-offset-{$file}", 0);

            gzseek($stream, $lastOffset);

            $products = $this->prepareProducts($stream);

            if ($products->isNotEmpty()) {

                Product::upsert($products->toArray(), ['code'], ['product_name', 'quantity', 'url', 'imported_t']);

                $offset = gzeof($stream) ? 0 : gztell($stream);
                Cache::forever(Str::lower("last-offset-{$file}"), $offset);
            }

            gzclose($stream);
        });
    }

    private function prepareProducts($stream): Collection
    {
        $products = collect([]);

        for ($count = 1; !gzeof($stream) and $count <= 100; $count++) {

            $line = json_decode(gzgets($stream), true);

            if (!$line || !$line['code']) continue;

            $products->push([
                'code' => Str::of($line['code'])->trim()->replace('"', ""),
                'product_name' => $line['product_name'],
                'quantity' => Str::of($line['quantity'])->trim()->isEmpty() ? null : $line['quantity'],
                'url' => $line['url'],
                'creator' => $line['creator'],
                'created_t' => Carbon::createFromTimestamp($line['created_t'])->format('Y-m-d H:i:s'),
                'created_datetime' => Carbon::parse($line['created_datetime'])->format('Y-m-d H:i:s'),
                'imported_t' => now()->format('Y-m-d H:i:s'),
            ]);
        }

        return $products;
    }
}
