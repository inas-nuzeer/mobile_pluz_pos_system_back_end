<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhoneModel;
use Illuminate\Http\Request;

class PhoneModelController extends Controller
{
    public function index()
    {
        return response()->json(PhoneModel::with('brand')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
        ]);

        $model = PhoneModel::create($validated);

        return response()->json($model, 201);
    }

    public function show(PhoneModel $model)
    {
        return response()->json($model->load('brand'));
    }

    public function update(Request $request, PhoneModel $model)
    {
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
        ]);

        $model->update($validated);

        return response()->json($model);
    }

    public function destroy(PhoneModel $model)
    {
        $model->delete();

        return response()->json(null, 204);
    }
}
