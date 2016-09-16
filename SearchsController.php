<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\User;
use App\Region;
use App\Province;
use App\Commune;
use App\Enterprise;
use App\Postulation;
use App\Friendship;
use App\Tag;
use App\Project;
use App\Certificate;
use App\Http\Requests\SearchRequest;

class SearchsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SearchRequest $request)
    {
        //
        $result_array = array();
        //búsqueda en consultores
        $consultants = User::name($request->consultor)->consultant()->active()->orderBy('created_at')->get()->pluck('id');
        foreach ($consultants as $value) {
            $user = User::find($value);
            $result_array = array_prepend($result_array, [ 'id' => $user->id ,  'name' => $user->name , 'address' => $user->address, 'region' => $user->region->name , 'bio' => $user->bio , 'email' => $user->email , 'type' => 1] );
        }

        //búsqueda en empresa
        $enterprises = Enterprise::name($request->consultor)->active()->orderBy('created_at')->get()->pluck('id');
        foreach ($enterprises as $value) {
            $enterprise = Enterprise::find($value);
  
            if($enterprise->postulation->user->level->id===5){
            $result_array = array_prepend($result_array, [ 'id' => $enterprise->id ,  'name' => $enterprise->business_name , 'address' => $enterprise->business_address, 'region' => $enterprise->region->name , 'bio' => $enterprise->business_bio, 'email' => $enterprise->business_email , 'type' => 2] );
            }
        }
        //búsqueda en tags
        $tags = Tag::name($request->consultor)->orderBy('created_at')->get()->pluck('id');

        foreach ($tags as $value) {
            $tag = Tag::find($value);
            foreach ($tag->projects as $rel) {
                //projectos con el tag buscado
                $project = Project::find($rel->pivot->project_id);
                //echo $project->postulation->user->name.' - '.$project->postulation->user->level_id;
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
        //búsqueda en proyectos
        $projects = Project::name($request->consultor)->orderBy('created_at')->get()->pluck('id');
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

        //búsqueda en certificados
        $certificates = Certificate::name($request->consultor)->orderBy('created_at')->get()->pluck('id');
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

        //búsqueda en regiones
        $regions = Region::name($request->consultor)->orderBy('created_at')->get()->pluck('id');
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

        //búsqueda en comunas
        $communes = Commune::name($request->consultor)->orderBy('created_at')->get()->pluck('id');
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

        return view('search')->with('result_array', $collection);
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
    public function show($type, $id)
    {
        //
        $result = null;
        $projects = null;
        $members = null;
        $is_member = null;
        $administrators = null;
        $is_send = null;
        $tags_count = array();
        $licences = array();
        $vals = null;

        if($type==='consultor'){
            $result = User::find($id);
            $projects = Postulation::where('user_id', '=', $id)->get()->last()->projects;
            $is_member = Friendship::where('user_id', '=', $id)->where('state', 1)->get()->last();
            $postulation = Postulation::where('user_id', '=', $id)->where('state', 2)->get()->last();

                if($is_member!=null){
                $enterprise = Enterprise::find($is_member->enterprise_id);
                $administrators = $enterprise->administrators->where('state', 1)->pluck('user_id')->toArray();
                $administrators = array_prepend($administrators, $enterprise->postulation->where('id', $enterprise->postulation_id)->first()->user->id);
                }
                    //proyectos del consultor
                         foreach ($projects as $project) 
                         {
                             foreach ($project->tags as $tag) 
                             {
                              $tags_count = array_prepend($tags_count, $tag->name);
                             }
                         }
                         $vals = array_count_values($tags_count);

                         foreach ($postulation->licences as $licence)
                         {
                            $licences = array_prepend($licences, $licence->certificate->name );
                         }

        }elseif($type==='empresa'){
            $result = Enterprise::find($id);
         
            $projects = Postulation::where('user_id', $result->postulation->user->id)->get()->last()->projects;
            $members = $result->friendships->where('state',1);
            $is_member = Friendship::where('enterprise_id', '=', $result->id)->where('state', 1)->get()->pluck('user_id')->toArray();
            $is_send = Friendship::where('enterprise_id', '=', $result->id)->where('state', 0)->get()->pluck('user_id')->toArray();
               //dd($projects);
            //Proyectos de la empresa
                foreach ($projects as $project) 
                {
                    foreach ($project->tags as $tag) 
                    {
                     $tags_count = array_prepend($tags_count, $tag->name);
                    }
                }

             //proyectos de cada miembro
        foreach ($result->friendships as $friendship)
        {
               if($friendship->state==1)
                {
                        //dd($friendship->user);
                            $projects_users = Postulation::where('user_id', '=', $friendship->user->id)->get()->last()->projects;
                            foreach ($projects_users as $project) 
                            {
                                foreach ($project->tags as $tag) 
                                {
                                 $tags_count = array_prepend($tags_count, $tag->name);
                                }
                            }
                            
                            $postulation = Postulation::where('user_id', '=', $friendship->user->id)->where('state', 2)->get()->last();
                            foreach ($postulation->licences as $licence)
                         {
                            $licences = array_prepend($licences, $licence->certificate->name );
                         }
                         $licences = array_unique($licences);
                }
        }
         $vals = array_count_values($tags_count);

        }


        arsort( $vals );
        $vals = array_slice($vals, 0, 6);
       //dd($is_member);
        return view('profile')
        ->with('result', $result)
        ->with('type', $type)
        ->with('projects', $projects)
        ->with('members', $members)
        ->with('is_member', $is_member)
        ->with('is_send', $is_send)
        ->with('licences', $licences)
        ->with('vals', $vals)
        ->with('administrators', $administrators);
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
