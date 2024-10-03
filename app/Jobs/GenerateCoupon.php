<?php

namespace App\Jobs;

use Str;
use Log;
use Throwable;
use RuntimeException;
use App\Models\Coupon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateCoupon implements ShouldQueue {
    use Queueable;

    protected array $codes = [];

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string  $type,
        protected int     $count,
        protected int     $quantity,
        protected int     $value,
        protected ?string $description = null,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        // Start memory usage measure
        $startMemory = memory_get_usage(true);

        $this->codes = Coupon::pluck('code')->toArray();
        $coupons = [];
        $time = now();

        for ($i = 1; $i <= $this->count; $i++) {
            $coupons[] = $this->generateCoupon($time);
        }

        if (!empty($coupons)) {
            static::storeCoupons($coupons);
        }

        // End memory usage measure
        $endMemory = memory_get_peak_usage(true);
        $memoryUsedBytes = $endMemory - $startMemory;

        // Convert to KB and MB
        $memoryUsedKB = $memoryUsedBytes / 1024;
        $memoryUsedMB = $memoryUsedKB / 1024;

        logger("Memory used for generating {$this->count} coupons: {$memoryUsedKB} KB ({$memoryUsedMB} MB)");
    }

    private function generateCoupon($time): array {
        return [
            'code' => $this->generateCouponCode(),
            'type' => $this->type,
            'quantity' => $this->quantity,
            'value' => $this->value,
            'description' => $this->description,
            'created_at' => $time,
        ];
    }

    private static function storeCoupons(array $coupons): void {
        $chunks = array_chunk($coupons, config('coupon.chunk_size'));
        foreach ($chunks as $chunk) {
            try {
                Coupon::insert($chunk);
            } catch (Throwable $e) {
                Log::error($e->getMessage());
            }
        }
    }

    private function generateCouponCode(int $maxAttempts = 3): string {
        $code = static::generateCode();

        $attempts = 0;
        while ($attempts < $maxAttempts && $this->codeExist($code)) {
            $attempts++;
            $code = static::generateCode();
        }

        if ($attempts === $maxAttempts) {
            throw new RuntimeException('Failed to generate a unique coupon code (max attempts reached!)');
        }

        $this->codes[] = $code;
        return $code;
    }

    private static function generateCode(): string {
        return Str::upper(Str::random(6));
    }

    private function codeExist(string $code): bool {
        return in_array($code, $this->codes, true);
    }
}
