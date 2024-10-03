<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\GenerateCoupon;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Models\Coupon;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCouponRequest $request): JsonResponse {
        $batchSize = config('coupon.batch_size');
        $numBatches = ceil($request->count / $batchSize);

        $type = $request->type;
        $value = $request->value;
        $description = $request->description;
        $quantity = $request->quantity;
        $total = $request->count;

        for ($i = 0; $i < $numBatches; $i++) {
            $count = (($i + 1) * $batchSize > $total) ? ($total - ($i * $batchSize)) : $batchSize;

            GenerateCoupon::dispatch($type, $count, $quantity, $value, $description);
        }

        return apiResponse('Generating your codes...');
    }

    /**
     * Display the specified resource.
     */
    public function show(Coupon $coupon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        //
    }
}
