<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FoodRequest;
use App\Models\Food;
use Illuminate\Support\Facades\Storage;

class FoodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $food = Food::paginate(10);

        return view('food.index', [
            'food' => $food,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('food.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FoodRequest $request)
    {
        $data = $request->all();

        // store photopath
        if ($request->file('picture_path')) {
            $photoPath = $request->file('picture_path')->store('assets/food', 'public');
            $data['picture_path'] = $photoPath;
        }

        Food::create($data);

        return redirect()->route('food.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Food $food)
    {
        return view('food.edit', [
            'item' => $food
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(FoodRequest $request, Food $food)
    {
        $data = $request->all();

        // apakah user memasukkan photo?
        if ($request->file('picture_path')) {

            // delete old photo
            Storage::disk('public')->delete($food->picture_path);

            // store new photopath
            $photoPath = $request->file('picture_path')->store('assets/food', 'public');
            $data['picture_path'] = $photoPath;
        }

        $food->update($data);

        return redirect()->route('food.index');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Food $food)
    {
        $food->delete();

        return redirect()->route('food.index');
    }
}
