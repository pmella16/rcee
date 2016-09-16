<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Requests\EditProfileRequest;
use Illuminate\Http\Request;
use App\Postulation;
use App\User;
use App\Period;
use App\Friendship;
use App\Notification;
use App\Enterprise;
use App\Administrator;
use App\Region;
use App\Province;
use App\Commune;
use App\Avatar;
use Intervention\Image\ImageManagerStatic as Image;
use Barryvdh\DomPDF\Facade as PDF;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth',['except' => ['index']]);
    }

    public function index()
    {
        //
      
        $period = Period::where('state', 1)->get()->last();

        
         return view('welcome')->with('period', $period);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {   

        $user = User::find(\Auth::user()->id);
        //verifico si hay periodo activo
        $period = Period::where('state', 1)->get()->last();
        $postulation = null;
        $enterprise = null;
        $projects = null;
        $tags_count = array();
        $licences = array();
        $vals = null;


        $postulation = Postulation::where('user_id', '=', \Auth::user()->id)->where('state', 2)->get()->last();
     
        

        if($postulation!=null && $postulation->type==2){
 
            //$enterprise = $user->postulation->where('type','=',2)->get()->last()->enterprise;
            $enterprise = Postulation::where('user_id', \Auth::user()->id)->get()->last()->enterprise; 
                //dd($user->postulation->enterprise);
     
        //proyectos de la postulación
        $projects = Postulation::where('user_id', '=', \Auth::user()->id)->get()->last()->projects;
        foreach ($projects as $project) 
        {
            foreach ($project->tags as $tag) 
            {
             $tags_count = array_prepend($tags_count, $tag->name);
            }
        }

        //proyectos de cada miembro
        foreach ($enterprise->friendships as $friendship)
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
                            $vals = array_count_values($tags_count);

                            $postulation = Postulation::where('user_id', '=', $friendship->user->id)->where('state', 2)->get()->last();

                            foreach ($postulation->licences as $licence)
                         {
                            $licences = array_prepend($licences, $licence->certificate->name );
                         }

                         $licences = array_unique($licences);

                }
        }


               arsort( $vals );

        }elseif($postulation!=null && $postulation->type==1){
   
                if($user->friendship!=null){
                if($user->friendship->where('user_id', \Auth::user()->id)->get()->last()!=null && $user->friendship->where('user_id', \Auth::user()->id)->get()->last()->state==1)
                    $enterprise = Enterprise::find($user->friendship->enterprise_id);
                //dd($user->postulation->enterprise);
                }
        

                         $projects = Postulation::where('user_id', '=', \Auth::user()->id)->get()->last()->projects;
                    
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


                                 arsort( $vals );
        }


        return view('home')
        ->with('period', $period)
        ->with('enterprise', $enterprise)
        ->with('user', $user)
        ->with('projects', $projects)
        ->with('vals', $vals)
        ->with('licences', $licences);
    }

    public function notifications()
    {   

        $result_array = array();
        $all_notifications = array();
        $user = User::find(\Auth::user()->id);

        if($user->notifications!=null)
        $all_notifications = Notification::where('user_id', \Auth::user()->id)->orderBy('id', 'desc')->get();
       


         return view('notifications')->with('all_notifications', $all_notifications);

    }

    public function show($id, $uid)
    { 
        $friendship = Friendship::where('uid', $uid)->first();
        $administrator = Administrator::where('uid', $uid)->first();

        $notification = Notification::where('url', $uid)->where('id', $id)->first();
        $notification->read = 1;
        $notification->save();

        return view('notification-show')->with('notification', $notification)->with('friendship', $friendship)->with('administrator', $administrator);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = User::find(\Auth::user()->id);
        $regions = Region::all()->lists('name', 'id');
        $provinces = Province::where('region_id', $user->region_id)->lists('name', 'id');
        $communes = Commune::where('province_id', $user->province_id)->lists('name', 'id');


        return view('profile-edit')
        ->with('regions', $regions)
        ->with('provinces', $provinces)
        ->with('communes', $communes)
        ->with('user', $user);
    }

     public function pdf()
    {   

        $postulation = Postulation::where('user_id', '=', \Auth::user()->id)->where('state', 2)->get()->last();
     
        

        if($postulation!=null && $postulation->type==2){

        $enterprise = Postulation::where('user_id', \Auth::user()->id)->get()->last()->enterprise->toArray();

        $pdf = PDF::loadView('pdf.empresa', $enterprise);
        return $pdf->download('certificado-'.$enterprise['slug'].'.pdf');

        }elseif($postulation!=null && $postulation->type==1){

        $user = User::find(\Auth::user()->id)->toArray();

        $pdf = PDF::loadView('pdf.consultor', $user);
        return $pdf->download('certificado-'.$user['slug'].'.pdf');
        }
        
        

    }
    public function update(EditProfileRequest $request)
    {
        $user = User::find(\Auth::user()->id);
        $region = Region::find($request->region_id);
        $province = Province::find($request->province_id);
        $commune = Commune::find($request->commune_id);

        $user->email = $request->email;
        $user->profession = $request->profession;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->bio = $request->bio;
        $user->region()->associate($region);
        $user->province()->associate($province);
        $user->commune()->associate($commune);
   


        if ($request->hasFile('img_profile')) {
    
        if ($request->file('img_profile')->isValid()) {
            $file = $request->file('img_profile');
            $type = $file->getMimeType();
            $size = $file->getSize();
            $resizedImage  =   $this->resize($file, 300);
            if($user->avatar===null)
            {
            $avatar = new Avatar();
            $avatar->name = $resizedImage;
            $avatar->type = $type;
            $avatar->size = $size;
            $avatar->user()->associate($user);
            $avatar->save();
            }else
            {
            $old_avatar = Avatar::find($user->avatar->id);
            \File::delete(public_path() . '/files/users/avatars/'. $user->avatar->name);
            $old_avatar->delete();
            $avatar = new Avatar();
            $avatar->name = $resizedImage;
            $avatar->type = $type;
            $avatar->size = $size;
            $avatar->user()->associate($user);
            $avatar->save();
            }
            
              }
        }

        $user->save();

        return redirect('/home');
    }

    private function resize($image, $size)
    {
        try 
        {
            $extension      =   $image->getClientOriginalExtension();
            $imageRealPath  =   $image->getRealPath();
            $thumbName      =   'thumb_'.uniqid().$image->getClientOriginalName();

            //$imageManager = new ImageManager(); // use this if you don't want facade style code
            //$img = $imageManager->make($imageRealPath);

            $img = Image::make($imageRealPath); // use this if you want facade style code
            //$img->resize(intval($size), null, function($constraint) {
            //     $constraint->aspectRatio();
            //});
            $img->resize($size, $size);

            
            $img->save(public_path() . '/files/users/avatars/' . $thumbName);

            return $thumbName;
        }
        catch(Exception $e)
        {
            return false;
        }
    }

}
