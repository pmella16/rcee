<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\User;
use App\Enterprise;
use App\Type;
use App\Administrator;
use App\Notification;

class AdministratorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $user = User::find($request->id);
        $enterprise = Enterprise::where('uid', $request->uid)->first();

        $type = Type::find(4);


        $administrator = New Administrator();
        $administrator->user()->associate($user);
        $administrator->enterprise()->associate($enterprise);
        $administrator->state = 0;
        $administrator->uid = uniqid();
        $administrator->save();

        $notification = New Notification();
        $notification->user()->associate($user);
        $notification->from_id = $enterprise->id;
        $notification->type()->associate($type);
        $notification->url = $administrator->uid;
        $notification->from_name = $enterprise->fantasy_name;
        $notification->save();

        return view('notification-send')->with('user', $user)->with('type', 'consultor');
    }

    public function accept(Request $request)
    {
        //
 
        $administrator = Administrator::find($request->id);
        $enterprise = Enterprise::find($administrator->enterprise_id);
        $user = User::find($administrator->user_id);
        $type = Type::find(5);

        $user_enterprise = $enterprise->postulation->user;
        $notification = New Notification();
        $notification->user()->associate($user_enterprise);
        $notification->from_id = $user->id;
        $notification->type()->associate($type);
        $notification->url = $administrator->uid;
        $notification->from_name = $user->name;
        $notification->save();

        $administrators = $enterprise->administrators->where('state', 1);

        $administrator->state = 1;
        $administrator->save();

        foreach ($administrators as $administrator) {
        $user_admin = User::find($administrator->user_id);
        $notification = New Notification();
        $notification->user()->associate($user_admin);
        $notification->from_id = $user->id;
        $notification->type()->associate($type);
        $notification->url = $administrator->uid;
        $notification->from_name = $user->name;
        $notification->save();
        }


        


        return redirect()->route('home.home');
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
        dd($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $administrator = Administrator::find($request->id);
        $administrator->state = 2;
        $administrator->save();

        

        $enterprise = Enterprise::find($administrator->enterprise_id);
        $user = User::find($administrator->user_id);
        $user_enterprise = $enterprise->postulation->user;
        
        $type = Type::find(10);
        //notifico al administrador que será quitado
        $notification = New Notification();
        $notification->user()->associate($user);
        $notification->from_id = $enterprise->id;
        $notification->type()->associate($type);
        $notification->url = $administrator->uid;
        $notification->from_name = $enterprise->fantasy_name;
        $notification->save();

        $type = Type::find(6);
        //notifico al creador de la empresa
        $notification = New Notification();
        $notification->user()->associate($user_enterprise);
        $notification->from_id = $user->id;
        $notification->type()->associate($type);
        $notification->url = $administrator->uid;
        $notification->from_name = $user->name;
        $notification->save();



        $administrators = $enterprise->administrators->where('state', 1);

        foreach ($administrators as $administrator) {
        $user_admin = User::find($administrator->user_id);
        $notification = New Notification();
        $notification->user()->associate($user_admin);
        $notification->from_id = $user->id;
        $notification->type()->associate($type);
        $notification->url = $administrator->uid;
        $notification->from_name = $user->name;
        $notification->save();
        }


        return redirect()->route('home.home');


    }
}
