<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Friendship;
use App\Notification;
use App\User;
use App\Enterprise;
use App\Type;
use App\Level;
use App\Postulation;

class FriendshipsController extends Controller
{
     public function __construct()
    {

        $this->middleware('auth');

    }

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
        $id     =   $request->id;

         //solicitud para consultor
        if($request->type==='consultor'){
        $type = Type::find(1);
        $user = User::find($id);

        if(User::find(\Auth::user()->id)->administrator!=null){

        $administrator = User::find(\Auth::user()->id)->administrator->where('user_id', \Auth::user()->id)->get()->last();
        if($administrator->state==1){
        $enterprise = Enterprise::find($administrator->enterprise_id);
        }
        }else{
        $enterprise = Postulation::where('user_id', \Auth::user()->id)->get()->last()->enterprise; 
        //dd($enterprise);  
        }
        
        
        $friendship = New Friendship();
        $friendship->user()->associate($user);
        $friendship->enterprise()->associate($enterprise);
        $friendship->state = 0;
        $friendship->uid = uniqid();
        $friendship->save();

        $notification = New Notification();
        $notification->user()->associate($user);
        $notification->from_id = $enterprise->id;
        $notification->type()->associate($type);
        $notification->url = $friendship->uid;
        $notification->from_name = $enterprise->fantasy_name;
        $notification->save();

               return view('notification-send')->with('user', $user)->with('type', $type);

        }
        //solicitud para empresa
        if($request->type==='empresa'){

        $type = Type::find(2);
        $user = User::find(\Auth::user()->id);
        $enterprise = Enterprise::find($id);
        $user_enterprise = $enterprise->postulation->user;

        $friendship = New Friendship();
        $friendship->user()->associate($user);
        $friendship->enterprise()->associate($enterprise);
        $friendship->state = 0;
        $friendship->uid = uniqid();
        $friendship->save();

        $notification = New Notification();
        $notification->user()->associate($user_enterprise);
        $notification->from_id = $user->id;
        $notification->type()->associate($type);
        $notification->url = $friendship->uid;
        $notification->from_name = $user->name;
        $notification->save();


        $administrators = $enterprise->administrators->where('state', 1);

        foreach ($administrators as $administrator) {

        $user_admin = User::find($administrator->user_id);
        $notification = New Notification();
        $notification->user()->associate($user_admin);
        $notification->from_id = $user->id;
        $notification->type()->associate($type);
        $notification->url = $friendship->uid;
        $notification->from_name = $user->name;
        $notification->save();

        }


               return view('notification-send')->with('enterprise', $enterprise)->with('type', $request->type);

        }






     
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request)
    {
        //

        $friendship = Friendship::find($request->id);
        $friendship->state = 1;
        $friendship->save();

        $type = Type::find(3);
        $level = Level::find(5);

        if($request->type==2){
        //tipo 2 solicitud de consultor a empresa
        $user = User::find($friendship->user_id);

        $enterprise = Enterprise::find($friendship->enterprise_id);

        $user->level()->associate($level);
        $user->save();

        $notification = New Notification();
        $notification->user()->associate($user);
        $notification->from_id = $enterprise->id;
        $notification->type()->associate($type);
        $notification->url = $friendship->uid;
        $notification->from_name = $enterprise->fantasy_name;
        $notification->save();

        }else{

        //tipo 1 solicitud de empresa a consultor
        $user = User::find(\Auth::user()->id);
        $enterprise = Enterprise::find($friendship->enterprise_id);

        $user_enterprise = $enterprise->postulation->user;

        $user->level()->associate($level);
        $user->save();

        $notification = New Notification();
        $notification->user()->associate($user_enterprise);
        $notification->from_id = $user->id;
        $notification->type()->associate($type);
        $notification->url = $friendship->uid;
        $notification->from_name = $user->name;
        $notification->save();


        $administrators = $enterprise->administrators->where('state', 1);

        foreach ($administrators as $administrator) {

        $user_admin = User::find($administrator->user_id);
        $notification = New Notification();
        $notification->user()->associate($user_admin);
        $notification->from_id = $user->id;
        $notification->type()->associate($type);
        $notification->url = $friendship->uid;
        $notification->from_name = $user->name;
        $notification->save();

        }

        }

        return redirect()->route('home.home');
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

        $friendship = Friendship::find($request->id);
        $enterprise = Enterprise::where('uid', $request->uid)->first();
        $user_enterprise = $enterprise->postulation->user;

        $friendship->state = 2;
        $friendship->save();

        if($friendship->user->id==\Auth::user()->id){
        $user = User::find(\Auth::user()->id);

        $type = Type::find(7);
        $notification = New Notification();
        $notification->user()->associate($user_enterprise);
        $notification->from_id = \Auth::user()->id;
        $notification->type()->associate($type);
        $notification->url = $request->uid;
        $notification->from_name = \Auth::user()->name;
        $notification->save();

        $administrators = $enterprise->administrators->where('state', 1);

        foreach ($administrators as $administrator) {
        $user_admin = User::find($administrator->user_id);
        $notification = New Notification();
        $notification->user()->associate($user_admin);
        $notification->from_id = \Auth::user()->id;
        $notification->type()->associate($type);
        $notification->url = $request->uid;
        $notification->from_name = \Auth::user()->name;
        $notification->save();
        }

        }else{
        $user = User::find($friendship->user_id);
        $type = Type::find(8);
        $notification = New Notification();
        $notification->user()->associate($user);
        $notification->from_id = $enterprise->id;
        $notification->type()->associate($type);
        $notification->url = $request->uid;
        $notification->from_name = $enterprise->fantasy_name;
        $notification->save();

        $type = Type::find(7);
        $notification = New Notification();
        $notification->user()->associate($user_enterprise);
        $notification->from_id = $user->id;
        $notification->type()->associate($type);
        $notification->url = $request->uid;
        $notification->from_name = $user->name;
        $notification->save();

        $administrators = $enterprise->administrators->where('state', 1);

        foreach ($administrators as $administrator) {
        $user_admin = User::find($administrator->user_id);
        $notification = New Notification();
        $notification->user()->associate($user_admin);
        $notification->from_id = $user->id;
        $notification->type()->associate($type);
        $notification->url = $request->uid;
        $notification->from_name = $user->name;
        $notification->save();
        }

        }   
        $level = Level::find(4);
        $user->level()->associate($level);
        $user->save();

        if($user->administrator!=null){
        $administrator = $user->administrator->where('user_id', $user->id)->get()->last();
        $administrator->state = 2;
        $administrator->save();
        }

       

                return redirect()->route('home.home');
    }
}
