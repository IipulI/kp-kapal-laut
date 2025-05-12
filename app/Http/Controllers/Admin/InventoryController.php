<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Utils\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventries = Inventory::query()
            ->with('category')
            ->get();

        return Response::status('success')->code(200)->result($inventries);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'category_id' => 'required',
            'name' => 'required',
            'sku' => 'required',
            'description' => 'required',
        ]);

        if ($validation->fails()) {
            return Response::status('failed')->code(400)->result($validation->errors());
        }

        $inventory = new Inventory();

        DB::beginTransaction();
        try {
            $inventory->category_id = $request->input('category_id');
            $inventory->sku = $request->input('sku');
            $inventory->name = $request->input('name');
            $inventory->description = $request->input('description');
            $inventory->save();

            DB::commit();

            return Response::status('success')->code(201)->result($inventory);
        } catch (\Exception $e){
            DB::rollBack();
            return Response::status('error')->code(500)->result($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $inventory = Inventory::query()
            ->with('category')
            ->where('id', $id)
            ->first();

        if (!$inventory) {
            return Response::status('error')->code(404)->result('Inventory not found');
        }

        return Response::status('success')->code(200)->result($inventory);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventory $inventory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id, Request $request)
    {
        $inventory = Inventory::query()
            ->where('id', $id)
            ->first();

        if (!$inventory) {
            return Response::status('error')->code(404)->result('Inventory not found');
        }

        DB::beginTransaction();

        try {
            $inventory->sku = $request->get('sku');
            $inventory->name = $request->get('name');
            $inventory->category_id = $request->get('category_id');
            $inventory->description = $request->get('description');
            $inventory->save();

            DB::commit();
            return Response::status('success')->code(200)->result($inventory);
        } catch (\Exception $e){
            DB::rollBack();
            return Response::status('error')->code(500)->result($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $inventory = Inventory::query()
            ->where('id', $id)
            ->first();

        if (!$inventory) {
            return Response::status('error')->code(404)->result($inventory);
        }

        DB::beginTransaction();

        try {
            $inventory->delete();

            DB::commit();

            return Response::status('success')->code(200)->result();
        } catch (\Exception $e){
            DB::rollBack();
            return Response::status('error')->code(500)->result($e->getMessage());
        }
    }
}
