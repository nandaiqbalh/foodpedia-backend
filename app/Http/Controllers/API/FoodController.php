<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function all(Request $request)
    {

        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $types = $request->input('types');

        // keperluan sort price
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        // keperluan sort rating
        $rate_from = $request->input('rate_from');
        $rate_to = $request->input('rate_to');

        // pengambilan data berdasarkan id
        if ($id) {
            $food = Food::find($id);

            if ($food) {
                return ResponseFormatter::success(
                    $food,
                    'Success to get the food data!'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Missing the food data!',
                    404
                );
            }
        }

        // query food data
        $food = Food::query();

        // get data by name
        if ($name) {

            $food->where('name', 'like', '%', $name, '%');
        }

        if ($types) {

            $food->where('types', 'like', '%', $types, '%');
        }

        if ($price_from) {

            $food->where('price', '>=', $price_from);
        }

        if ($price_to) {

            $food->where('price', '<=', $price_to);
        }

        if ($rate_from) {

            $food->where('rate', '>=', $rate_from);
        }

        if ($rate_to) {

            $food->where('rate', '<=', $rate_to);
        }

        return ResponseFormatter::success(
            $food->paginate($limit),
            'Success to get the food data!'
        );
    }
}
