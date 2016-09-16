<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\PostulationRequest;
use App\Http\Requests\PostulationEnterpriseRequest;
use App\Region;
use App\Province;
use App\Commune;
use App\Tag;
use App\Period;
use App\Certificate;
use App\User;
use App\Resume;
use App\Postulation;
use App\Licence;
use App\Task;
use App\Letter;
use App\Enterprise;

class PostulationsController extends Controller
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
    public function index(Request $request)
    {   

        //verifico si hay periodo activo
        $period = Period::where('state', 1)->first();

        if($period!=null){
        return view('postulations.index');
        }


        return view('postulations.sinperiodo');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {         

        //dd($request);
        $user = User::find(\Auth::user()->id);
        //verifico si hay periodo activo
        $period = Period::where('state', 1)->get()->last();
        $postulation = Postulation::where('user_id', '=', \Auth::user()->id)->where('period_id', '=', $period->id)->first();
        $licences = array();
        $enterprise = null; 
        $provincesEnterprise = null;
        $communesEnterprise = null;
        //valido que la postulacion sea del perido actual
        if($postulation!=null){
        if($postulation->period_id===$period->id){

            //si hay postulacion en el perido actual traigo los certificados de esta postulacion
            $licences = $postulation->licences->lists('id', 'certificate_id');
           // dd($licences);
              
              if($postulation->type===2){
              
                            $enterprise = $postulation->enterprise;
                     
                            $provincesEnterprise = Province::where('region_id', $enterprise->region_id)->lists('name', 'id');
                            $communesEnterprise = Commune::where('province_id', $enterprise->province_id)->lists('name', 'id');
              }


        }
        }
        
        $certificates = Certificate::all()->lists('name', 'id');

        //dd($user->postulation->get()->where('period_id', $period->id)->last()->licences->where('certificate_id', 12)->first()->certificate->name );

        $diff = $certificates->diffKeys($licences);

        //dd($user->postulation->get()->last()->licences->where('certificate_id', 12)->first()->certificate->name );


        if($period!=null){
        $type = $request->type;

        $regions = Region::all()->lists('name', 'id');
        $provinces = Province::where('region_id', $user->region_id)->lists('name', 'id');
        $communes = Commune::where('province_id', $user->province_id)->lists('name', 'id');
        $tags = Tag::all()->lists('name', 'id');
        return view('postulations.create')
        ->with('type', $type)
        ->with('regions', $regions)
        ->with('provinces', $provinces)
        ->with('communes', $communes)
        ->with('provincesEnterprise', $provincesEnterprise)
        ->with('communesEnterprise', $communesEnterprise)
        ->with('enterprise', $enterprise)
        ->with('licences', $licences)
        ->with('postulation', $postulation)
        ->with('diff', $diff)
        ->with('user', $user);
        }
        return view('postulations.sinperiodo');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeEmpresa(PostulationEnterpriseRequest $request)
    {   
      //dd($request);
      $user = User::find(\Auth::user()->id);

        //dd($request->file('certificates.*'));

        if ($request->hasFile('proxy_letter')) {
    
        if ($request->file('proxy_letter')->isValid()) {
        $file = $request->file('proxy_letter');
        $name = 'let_' .uniqid(). '.' .$file->getClientOriginalExtension();
        $type = $file->getMimeType();
        $size = $file->getSize();
        $path = public_path() . '/files/users/letters/';
        $file->move($path, $name); 


              }
        }

        $region = Region::find($request->region_id);
        $province = Province::find($request->province_id);
        $commune = Commune::find($request->commune_id);


        $user->fill($request->all());
        $user->save();




        $period = Period::where('state', 1)->first();
  
        $postulation = Postulation::where('user_id', '=', \Auth::user()->id)->where('period_id', '=', $period->id)->first();
   
        if($postulation===null){
        //Crear postulacion
        
        $postulation = new Postulation();
        $postulation->user()->associate($user);
        $postulation->period()->associate($period);
        $postulation->type = 2;
        $postulation->state = 0;
        $postulation->uid = uniqid();
        $postulation->save();
        }else{
        $postulation = Postulation::find($postulation->id);
        $postulation->user()->associate($user);
        $postulation->period()->associate($period);
        $postulation->type = 2;
        $postulation->state = 0;
        $postulation->uid = uniqid();
        $postulation->save();
        }

        if($postulation->enterprise===null){
        $enterprise = new Enterprise();
        $enterprise->fill($request->all());
        $enterprise->postulation()->associate($postulation);
        $enterprise->uid = uniqid();
        $enterprise->region()->associate($region);
        $enterprise->province()->associate($province);
        $enterprise->commune()->associate($commune);
        $enterprise->slug = str_slug($request->business_name, '-');
        $enterprise->save();

        $letter = new Letter();
        $letter->name = $name;
        $letter->type = $type;
        $letter->size = $size;
        $letter->enterprise()->associate($enterprise);
        $letter->save();

        }else{
        $enterprise = Enterprise::find($postulation->enterprise->id);
        $enterprise->fill($request->all());
        $enterprise->postulation()->associate($postulation);
        $enterprise->region()->associate($region);
        $enterprise->province()->associate($province);
        $enterprise->commune()->associate($commune);
        $enterprise->save();
        }



        //redirección a la lista de proyectos en esta postulación
          return redirect()->route('postulations.projects', $postulation->uid);
  
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostulationRequest $request)
    {   


        $user = User::find(\Auth::user()->id);

        //dd($request->file('certificates.*'));

        if ($request->hasFile('resume')) {
    
        if ($request->file('resume')->isValid()) {
        $file = $request->file('resume');
        $name = 'cv_' .uniqid(). '.' .$file->getClientOriginalExtension();
        $type = $file->getMimeType();
        $size = $file->getSize();
        $path = public_path() . '/files/users/resumes/';
        $file->move($path, $name); 

        $resume = new Resume();
        $resume->name = $name;
        $resume->type = $type;
        $resume->size = $size;
        $resume->user()->associate($user);
        $resume->save();

        }
        }
      if ($request->hasFile('resume_update')) {
    
        if ($request->file('resume_update')->isValid()) {


        $old_resume = Resume::find($user->resume->id);

        \File::delete(public_path() . '/files/users/resumes/'. $user->resume->name);
        $old_resume->delete();

        $file = $request->file('resume_update');
        $name = 'cv_' .uniqid(). '.' .$file->getClientOriginalExtension();
        $type = $file->getMimeType();
        $size = $file->getSize();
        $path = public_path() . '/files/users/resumes/';
        $file->move($path, $name); 

        $resume = new Resume();
        $resume->name = $name;
        $resume->type = $type;
        $resume->size = $size;
        $resume->user()->associate($user);
        $resume->save();
 

     }
        }

                //dd(\Auth::user()->id);

        //
        $region = Region::find($request->region_id);
        $province = Province::find($request->province_id);
        $commune = Commune::find($request->commune_id);


        $user->fill($request->all());
        $user->region()->associate($region);
        $user->province()->associate($province);
        $user->commune()->associate($commune);
        $user->slug = str_slug($request->name, '-');
        $user->save();

        $period = Period::where('state', 1)->first();
  
        $postulation = Postulation::where('user_id', '=', \Auth::user()->id)->where('period_id', '=', $period->id)->first();
   
        if($postulation===null){
        //Crear postulacion

        $postulation = new Postulation();
        $postulation->user()->associate($user);
        $postulation->period()->associate($period);
        $postulation->type = 1;
        $postulation->state = 0;
        $postulation->uid = uniqid();
        $postulation->save();
        }else{
        $postulation = Postulation::find($postulation->id);
        $postulation->user()->associate($user);
        $postulation->period()->associate($period);
        $postulation->type = 1;
        $postulation->state = 0;
        $postulation->uid = uniqid();
        $postulation->save();
        }

        $certificates = Certificate::all();

        $certificates->each(function ($item, $key) use ($request, $postulation) {

            if ($request->hasFile('certificates.'.$item->name)) {

                    if ($request->file('certificates.'.$item->name)->isValid()) {

                        $file = $request->file('certificates.'.$item->name);
                        $name = 'cert_' .uniqid(). '.' .$file->getClientOriginalExtension();
                        $type = $file->getMimeType();
                        $size = $file->getSize();
                        $path = public_path() . '/files/users/licences/';
                        $file->move($path, $name); 

                        $licence = new Licence();
                        $licence->name = $name;
                        $licence->type = $type;
                        $licence->size = $size;
                        $licence->postulation()->associate($postulation);
                        $licence->certificate()->associate($item);
                        $licence->save();

                    }
            }
      
        });

        //redirección a la lista de proyectos en esta postulación
          return redirect()->route('postulations.projects', $postulation->uid);


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      //me voy a proyectos
    $postulation = Postulation::where('user_id', '=', \Auth::user()->id)->where('uid', '=', $id)->first();

    if($postulation===null){
    return redirect('/home');
    }
    $projects = $postulation->projects;

    return view('postulations.projects', $postulation)->with('projects', $projects)->with('postulation', $postulation);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function view($id)
    {
        //
    $postulation = Postulation::where('uid', '=', $id)->where('user_id', '=', \Auth::user()->id)->first();
    //dd($postulation->projects);

        // $project = $postulation->projects->filter(function($value) use ($id) {
        //             if ($value->uid == $id) {
        //                 return true;
        //             }
        //     })->first();


    $user = User::find(\Auth::user()->id);
    //dd($user->postulation->last());


    return view('postulations.view', $postulation)->with('user', $user)->with('postulation', $postulation);
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
     * Update la postulacion para enviar a revisión.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $postulation = Postulation::where('user_id', '=', \Auth::user()->id)->where('id', '=', $id)->first();
        $postulation->state = 1;
        $postulation->save();

        return view('postulations.send');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyLicence(Request $request)
    {
 
        if($request->ajax()){

            $user = User::find(\Auth::user()->id);
            $id = $request->id;

            $period = Period::where('state', 1)->first();
            $postulation = Postulation::where('user_id', '=', \Auth::user()->id)->where('period_id', '=', $period->id)->first();

            $licence = $postulation->licences->filter(function($value) use ($id) {
                 if ($value->id == $id) {
                   return true;
                 }

             })->first();
             
            $certificate = Certificate::find($licence->certificate_id);
            \File::delete(public_path() . '/files/users/licences/'. $licence->name);
            $licence->delete();


            return response()->json([
            'status'    =>  'success',
            'name'      =>  $certificate->name
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
    public function destroy($id)
    {
        //
    }
}
