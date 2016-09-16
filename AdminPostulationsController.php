<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Postulation;
use App\Project;
use App\Tag;
use App\User;
use App\Level;
use App\Notification;
use App\Type;

class AdminPostulationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $postulations = Postulation::where('state', '!=', 2)->get();

        return view('admin.postulations.index')->with('postulations', $postulations);
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
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    $postulation = Postulation::where('id', '=', $id)->first();

    if($postulation===null){
    return redirect('/home');
    }
    //$projects = $postulation->projects;

    return view('admin.postulations.show')->with('postulation', $postulation);

    }

    /**
     * Se editan los proyectos de la postulación.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    $project = Project::where('id', '=', $id)->first();
    $tags = Tag::orderBy('name', 'ASC')->lists('name', 'id');

    $my_tags = $project->tags->lists('id')->toArray();

    if($project===null){
    return redirect('/home');
    }
    //$projects = $postulation->projects;

    return view('admin.postulations.edit')->with('project', $project)->with('tags', $tags)->with('my_tags', $my_tags);
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

        $project = Project::find($id);

        $project->fill($request->all());
        $project->save();
        //agrego array vacio por defecto
        $project->tags()->sync($request->get('tags', []));

        return redirect()->route('admin.postulations.show', $project->postulation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, $id)
    {
        //
        $postulation = Postulation::find($id);
        $postulation->state = 2;
        $postulation->save();

        $user = User::find($postulation->user->id);
        if($postulation->type===1){
        $role = Level::find(4);
        $user->level()->associate($role);
        }elseif ($postulation->type===2) {
        $role = Level::find(5);
        $user->level()->associate($role);
        $enterprise = $user->postulation->where('id', $id)->get()->first()->enterprise;
        $enterprise->state = 1;
        $enterprise->save();
        }
        $user->state = 1;
        $user->save();

        $type = Type::find(9);
        $notification = New Notification();
        $notification->user()->associate($user);
        $notification->from_id = 0;
        $notification->type()->associate($type);
        $notification->url = $postulation->uid;
        $notification->from_name = '(RCEE)';
        $notification->save();

        return redirect()->route('admin.postulations.index');
    }

    public function refuse($id)
    {
        $postulation = Postulation::find($id);
        $postulation->state = 3;
        $postulation->save();

        return redirect()->route('admin.postulations.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
