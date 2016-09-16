<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Period;
use App\Http\Requests;
use App\Project;
use App\Postulation;
use App\Document;
use App\Http\Requests\ProjectRequest;
use App\Http\Requests\ProjectEditRequest;
use App\User;
use App\Task;

class ProjectsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        
        $this->middleware('postulation');
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
    public function store(ProjectRequest $request)
    {   


        if ($request->hasFile('attach')) 
        {
        if ($request->file('attach')->isValid()) 
        {
        $file = $request->file('attach');
        $name = 'doc_' .uniqid(). '.' .$file->getClientOriginalExtension();
        $type = $file->getMimeType();
        $size = $file->getSize();
        $path = public_path() . '/files/projects/documents/';
        $file->move($path, $name); 



        $postulation = Postulation::where('uid' , '=', $request->postulation_id)->where('state' , '=', 0)->first();

        $project = new Project();
        $project->fill($request->all());
        $project->postulation()->associate($postulation);
        $project->uid = uniqid();
        $project->save();

        $attach = new Document();
        $attach->name = $name;
        $attach->type = $type;
        $attach->size = $size;
        $attach->project()->associate($project); 
        $attach->save();



        foreach ($request->task as $task) {
            if($task!=null){


        $taskProject = new Task();
        $taskProject->name = $task;
        $taskProject->project()->associate($project); 
        $taskProject->save();
                    }
        }
        


        return redirect()->route('postulations.projects', $postulation->uid);
        }
        }


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
        //

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($uid, $idp)
    {
        //
        $postulation = Postulation::where('uid', '=', $uid)->where('user_id', '=', \Auth::user()->id)->first();
                //dd($postulation->projects);

        $project = $postulation->projects->filter(function($value) use ($idp) {
                    if ($value->uid == $idp) {
                        return true;
                    }
            })->first();

        return view('postulations.project-edit')->with('project', $project)->with('uid', $uid);


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectEditRequest $request, $id)
    {
        $user = User::find(\Auth::user()->id);
  
        $postulation = Postulation::where('uid', '=', $request->postulation_id)->where('user_id', '=', \Auth::user()->id)->first();


        $project = Project::find($id);
        $project->fill($request->all());
        $project->postulation()->associate($postulation);
        $project->save();

        if ($request->hasFile('attach_update')) {
    
        if ($request->file('attach_update')->isValid()) {


        $old_document = Document::find($project->document->id);

        \File::delete(public_path() . '/files/projects/documents/'. $project->document->name);
        $old_document->delete();

        $file = $request->file('attach_update');
        $name = 'doc_' .uniqid(). '.' .$file->getClientOriginalExtension();
        $type = $file->getMimeType();
        $size = $file->getSize();
        $path = public_path() . '/files/projects/documents/';
        $file->move($path, $name); 

        $document = new Document();
        $document->name = $name;
        $document->type = $type;
        $document->size = $size;
        $document->project()->associate($project);
        $document->save();   
     }
        }
        if ($request->has('my_task')) {
        foreach ($request->my_task as $key => $task) {
        $taskProject = Task::find($key);
        $taskProject->name = $task;
        $taskProject->project()->associate($project); 
        $taskProject->save();
        }
        }



        if ($request->has('task')) {
        foreach ($request->task as $task) {
        $taskProject = new Task();
        $taskProject->name = $task;
        $taskProject->project()->associate($project); 
        $taskProject->save();
        }
        }
        return redirect()->route('postulations.projects', $postulation->uid);
        //return redirect()->route('postulations.projectEdit', [ $postulation->uid, $project->uid ]);

    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyTask(Request $request)
    {
 
        if($request->ajax()){

            $user = User::find(\Auth::user()->id);
            $id = $request->id;

            $period = Period::where('state', 1)->first();
            $postulation = Postulation::where('user_id', '=', \Auth::user()->id)->where('period_id', '=', $period->id)->first();


            $task = $postulation->projects->where('uid', $request->idp)->first()->tasks->filter(function($value) use ($id) {
                 if ($value->id == $id) {
                   return true;
                 }

             })->first();
            $task->delete();
            return response()->json([
            'status' => 'success',
            ]);

            //print_r($id);die;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $idp)
    {
        //
        $user = User::find(\Auth::user()->id);

        $project = $user->postulation->where('state',0)->first()->projects->filter(function($value) use ($idp) {
                if ($value->uid === $idp) {
                    return true;
                }

            })->first();
   
        \File::delete(public_path() . '/files/projects/documents/'. $project->document->name);


        $project->delete();


        return redirect()->route('postulations.projects', $user->postulation->where('state',0)->first()->uid);
 

    }
}
