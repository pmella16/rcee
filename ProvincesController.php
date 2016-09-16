<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Province;
use App\Region;

class ProvincesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $provinces = Province::all();

        return view('admin.provinces.index')->with('provinces', $provinces);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $regions = Region::all()->lists('name', 'id');

        return view('admin.provinces.create')->with('regions', $regions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $province = new Province($request->all());
        $region = Region::find($province->region_id);
        $province->region()->associate($region);
        $province->save();

       return redirect()->route('admin.provinces.index');
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
    public function edit($id)
    {
        $regions = Region::all()->lists('name', 'id');
        $province = Province::find($id);



        return view('admin.provinces.edit')->with('province', $province)->with('regions', $regions);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $province = Province::find($id);
        $province->fill($request->all());
        $province->save();
        return redirect()->route('admin.provinces.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $province = Province::find($id);
        $province->delete();

        return redirect()->route('admin.provinces.index');
    }
}
