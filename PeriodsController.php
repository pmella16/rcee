<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Period;
use App\Notification;
use App\Administrator;
use App\Friendship;
use App\User;
use App\Enterprise;

class PeriodsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $periods = Period::all();

        return view('admin.periods.index')->with('periods', $periods);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        return view('admin.periods.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $period = new Period($request->all());
        $period->state = true;
        
        //desactivo lo otros periodos
        Period::where('state', '=', 1)->update(['state' => 0]);
        $period->save();
        
        User::where('state', '=', 1)->update(['state' => 0]);

        Enterprise::where('state', '=', 1)->update(['state' => 0]);

        Notification::truncate();

        Friendship::truncate();

        Administrator::truncate();


       return redirect()->route('admin.periods.index');
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
        $period = Period::find($id);

        return view('admin.periods.edit')->with('period', $period);
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
        $period = Period::find($id);
        $period->fill($request->all());
        $period->save();
        return redirect()->route('admin.periods.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $period = Period::find($id);
        $period->delete();

        return redirect()->route('admin.periods.index');
    }
}
