<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Commune;
use App\Region;
use App\Province;

class CommunesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $communes = Commune::all();

        return view('admin.communes.index')->with('communes', $communes);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $provinces = Province::all()->lists('name', 'id');

        return view('admin.communes.create')->with('provinces', $provinces);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $commune = new Commune($request->all());
        $province = Province::find($commune->province_id);
        $commune->province()->associate($province);
        $commune->save();

       return redirect()->route('admin.communes.index');
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
        $provinces = Province::all()->lists('name', 'id');

        $commune = Commune::find($id);



        return view('admin.communes.edit')->with('commune', $commune)->with('provinces', $provinces)->with('regions', $regions);
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
        $comuna = Commune::find($id);
        $comuna->fill($request->all());
        $comuna->save();
        return redirect()->route('admin.communes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $comuna = Commune::find($id);
        $comuna->delete();

        return redirect()->route('admin.communes.index');
    }
}
