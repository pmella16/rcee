<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\AdvancedSearchRequest;
use App\User;
use App\Region;
use App\Commune;
use App\Certificate;
use App\Project;
use App\Enterprise;
use App\Postulation;

class AdvancedSearchsController extends Controller
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

        return view('advanced-search');
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
    public function show(AdvancedSearchRequest $request)
    {
        //
        $result_array = array();
       if($request->consultor!=null ){

             
        $var=explode(',',$request->consultor);
        foreach($var as $row)
         {
                //búsqueda en consultores
                $consultants = User::name($row)->consultant()->active()->orderBy('created_at')->get()->pluck('id');
                foreach ($consultants as $value) {
                    $user = User::find($value);
                    $result_array = array_prepend($result_array, [ 'id' => $user->id ,  'name' => $user->name , 'address' => $user->address, 'region' => $user->region->name , 'bio' => $user->bio , 'email' => $user->email , 'type' => 1] );
                }

                //búsqueda en empresa
        $enterprises = Enterprise::name($row)->active()->orderBy('created_at')->get()->pluck('id');
        foreach ($enterprises as $value) {
            $enterprise = Enterprise::find($value);
  
            if($enterprise->postulation->user->level->id===5){
            $result_array = array_prepend($result_array, [ 'id' => $enterprise->id ,  'name' => $enterprise->business_name , 'address' => $enterprise->business_address, 'region' => $enterprise->region->name , 'bio' => $enterprise->business_bio, 'email' => $enterprise->business_email , 'type' => 2] );
            }
        }
         }

       }
       if($request->region!=null ){
        $var=explode(',',$request->region);
        foreach($var as $row)
            {
                //búsqueda en regiones
        $regions = Region::name($row)->orderBy('created_at')->get()->pluck('id');
        foreach ($regions as $value) {
            $region = Region::find($value);
         
            foreach ($region->users as $user) {

                        if($user->level_id===4){
                        $user = User::active()->where('id', $user->id)->first();
                        if($user!=null)
                        $result_array = array_prepend($result_array, [ 'id' => $user->id ,  'name' => $user->name , 'address' => $user->address, 'region' => $user->region->name , 'bio' => $user->bio , 'email' => $user->email , 'type' => 1] );

                        }
                }
            foreach ($region->enterprises as $enterprise) {

                        $enterprise = Enterprise::active()->where('id', $enterprise->id)->first();
                        if($enterprise!=null)
                        $result_array = array_prepend($result_array, [ 'id' => $enterprise->id ,  'name' => $enterprise->business_name , 'address' => $enterprise->business_address, 'region' => $enterprise->region->name , 'bio' => $enterprise->business_bio, 'email' => $enterprise->business_email , 'type' => 2] );
                 
                }
            

        }
            }
       }
       if($request->commune!=null ){
        $var=explode(',',$request->commune);
        foreach($var as $row)
            {
                //búsqueda en comunas
        $communes = Commune::name($row)->orderBy('created_at')->get()->pluck('id');
        foreach ($communes as $value) {
            $commune = Commune::find($value);
         
            foreach ($commune->users as $user) {

                        if($user->level_id===4){
                        $user = User::active()->where('id', $user->id)->first();
                        if($user!=null)
                        $result_array = array_prepend($result_array, [ 'id' => $user->id ,  'name' => $user->name , 'address' => $user->address, 'region' => $user->region->name , 'bio' => $user->bio , 'email' => $user->email , 'type' => 1] );

                        }
                }
            foreach ($commune->enterprises as $enterprise) {

                        $enterprise = Enterprise::active()->where('id', $enterprise->id)->first();
                        if($enterprise!=null)
                        $result_array = array_prepend($result_array, [ 'id' => $enterprise->id ,  'name' => $enterprise->business_name , 'address' => $enterprise->business_address, 'region' => $enterprise->region->name , 'bio' => $enterprise->business_bio, 'email' => $enterprise->business_email , 'type' => 2] );
                 
                }
            

        }

            }

       }
       if($request->experience!=null ){
        $var=explode(',',$request->experience);
        foreach($var as $row)
            {
                //búsqueda en proyectos
        $projects = Project::name($row)->orderBy('created_at')->get()->pluck('id');
        foreach ($projects as $value) {
            $project = Project::find($value);

            if ($project->postulation->type === 2) {
                    
                    $enterprise = $project->postulation->where('user_id', $project->postulation->user->id)->get()->last()->enterprise->active()->first();
                    if($enterprise!=null)
                    $result_array = array_prepend($result_array, [ 'id' => $enterprise->id ,  'name' => $enterprise->business_name , 'address' => $enterprise->business_address, 'region' => $enterprise->region->name , 'bio' => $enterprise->business_bio, 'email' => $enterprise->business_email , 'type' => 2] );
                
                }elseif($project->postulation->type === 1){

                        if($project->postulation->user->level_id===5){

                        $enterprise = $project->postulation->user->friendship->where('user_id', $project->postulation->user->id)->get()->last()->enterprise->active()->first();
                    if($enterprise!=null)
                        $result_array = array_prepend($result_array, [ 'id' => $enterprise->id ,  'name' => $enterprise->business_name , 'address' => $enterprise->business_address, 'region' => $enterprise->region->name , 'bio' => $enterprise->business_bio, 'email' => $enterprise->business_email , 'type' => 2] );

                        }elseif($project->postulation->user->level_id===4){
                        $user = User::active()->where('id', $project->postulation->user->id)->first();
                        if($user!=null)
                        $result_array = array_prepend($result_array, [ 'id' => $user->id ,  'name' => $user->name , 'address' => $user->address, 'region' => $user->region->name , 'bio' => $user->bio , 'email' => $user->email , 'type' => 1] );

                        }
                }

        }
            }

       }
       if($request->licence!=null ){
        $var=explode(',',$request->licence);
        foreach($var as $row)
            {
                //búsqueda en certificados
        $certificates = Certificate::name($row)->orderBy('created_at')->get()->pluck('id');
        foreach ($certificates as $value) {
            $certificate = Certificate::find($value);

            foreach ($certificate->licences as $licence) {

                if ($licence->postulation->type === 2) {
                    
                    $enterprise = $licence->postulation->where('user_id', $licence->postulation->user->id)->get()->last()->enterprise->active()->first();
                    if($enterprise!=null)
                    $result_array = array_prepend($result_array, [ 'id' => $enterprise->id ,  'name' => $enterprise->business_name , 'address' => $enterprise->business_address, 'region' => $enterprise->region->name , 'bio' => $enterprise->business_bio, 'email' => $enterprise->business_email , 'type' => 2] );
                
                }elseif($licence->postulation->type === 1){

                        if($licence->postulation->user->level_id===5){

                        $enterprise = $licence->postulation->user->friendship->where('user_id', $licence->postulation->user->id)->get()->last()->enterprise->active()->first();
                        if($enterprise!=null)
                        $result_array = array_prepend($result_array, [ 'id' => $enterprise->id ,  'name' => $enterprise->business_name , 'address' => $enterprise->business_address, 'region' => $enterprise->region->name , 'bio' => $enterprise->business_bio, 'email' => $enterprise->business_email , 'type' => 2] );

                        }elseif($licence->postulation->user->level_id===4){
                        $user = User::active()->where('id', $licence->postulation->user->id)->first();
                        if($user!=null)
                        $result_array = array_prepend($result_array, [ 'id' => $user->id ,  'name' => $user->name , 'address' => $user->address, 'region' => $user->region->name , 'bio' => $user->bio , 'email' => $user->email , 'type' => 1] );

                        }
                }
            }
         
        }
            }

       }
       $result_array = array_map('unserialize', array_unique(array_map('serialize', $result_array)));
        //recorro el resultado y agrego la avatar/logo si tiene
        foreach ($result_array as $key => $value) {

            if ($value['type']===1){
                if(User::find($value['id'])->avatar!=null){
                   $result_array[$key] = array_add($value, 'photo', User::find($value['id'])->avatar->name); 
               }else{
                    $result_array[$key] = array_add($value, 'photo', '300x300.jpg');
               }
        
            }elseif($value['type']===2){
                if(Enterprise::find($value['id'])->logo!=null){
                   $result_array[$key] = array_add($value, 'photo', Enterprise::find($value['id'])->logo->name); 
               }else{
                    $result_array[$key] = array_add($value, 'photo', '300x300.jpg');
               }
            }
        }
        //recorro el resultado y agrego las certificaciones
        foreach ($result_array as $key => $value) {
          $licences = '';
            if ($value['type']===1){
                $postulation = Postulation::where('user_id', $value['id'])->where('state', 2)->get()->last();
                          foreach ($postulation->licences as $licence)
                         {
                            $licences .= $licence->certificate->name.' / ';
                         }
          $result_array[$key] = array_add($value, 'licences', substr($licences, 0, -3));
            
            }elseif($value['type']===2){
                $result = Enterprise::find($value['id']);
                //proyectos de cada miembro
                foreach ($result->friendships as $friendship)
                {
                  $licences_sum = '';
                  $licences = array();
                              if($friendship->state==1)
                              {
                                                 
                                  $postulation = Postulation::where('user_id', '=', $friendship->user->id)->where('state', 2)->get()->last();
                                      foreach ($postulation->licences as $licence)
                                        {
                                         $licences = array_prepend($licences, $licence->certificate->name );
                                        }
                                   
                                   $licences = array_unique($licences);

                                      foreach ($licences as $licence)
                                        {  
                                        $licences_sum .= $licence.' / ';
                                        }

                                  $result_array[$key] = array_add($value, 'licences', substr($licences_sum, 0, -3));
                              }
              }
            }
        }
       //dd($result_array);
       $collection = collect($result_array);
       return view('advanced-search')->with('result_array', $collection);
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
    public function destroy($id)
    {
        //
    }
}
