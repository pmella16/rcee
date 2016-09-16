<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Enterprise;
use App\Region;
use App\Province;
use App\Commune;
use App\Administrator;
use App\Http\Requests\EditProfileEnterpriseRequest;
use App\Logo;
use Intervention\Image\ImageManagerStatic as Image;

class EnterprisesController extends Controller
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
        $is_send = null;

        $enterprise = Enterprise::where('uid',$id)->first();
        $administrators = $enterprise->administrators->where('state', 1)->pluck('user_id')->toArray();
        $administrators = array_prepend($administrators, $enterprise->where('uid', $id)->first()->postulation->where('id', $enterprise->postulation_id)->first()->user->id);
        $is_send = Administrator::where('enterprise_id', $enterprise->id)->where('state',0)->get()->pluck('user_id')->toArray();;
        //dd($is_send);
        return view('enterprise')->with('enterprise', $enterprise)->with('administrators', $administrators)->with('is_send', $is_send);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($uid)
    {
        //
        $enterprise = Enterprise::where('uid', $uid)->get()->first();
        $provincesEnterprise = Province::where('region_id', $enterprise->region_id)->lists('name', 'id');
        $communesEnterprise = Commune::where('province_id', $enterprise->province_id)->lists('name', 'id');
        $regions = Region::all()->lists('name', 'id');

        return view('enterprise-edit')
        ->with('enterprise', $enterprise)
        ->with('regions', $regions)
        ->with('provincesEnterprise', $provincesEnterprise)
        ->with('communesEnterprise', $communesEnterprise);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
        public function update(EditProfileEnterpriseRequest $request)
    {

        $enterprise = Enterprise::where('uid',$request->uid)->first();
        $region = Region::find($request->region_id);
        $province = Province::find($request->province_id);
        $commune = Commune::find($request->commune_id);
        //dd($enterprise);
        $enterprise->business_address = $request->business_address;
        $enterprise->business_website = $request->business_website;
        $enterprise->business_email = $request->business_email;
        $enterprise->business_phone = $request->business_phone;
        $enterprise->business_bio = $request->business_bio;
        $enterprise->region()->associate($region);
        $enterprise->province()->associate($province);
        $enterprise->commune()->associate($commune);
   


        if ($request->hasFile('img_profile')) {
    
        if ($request->file('img_profile')->isValid()) {
            $file = $request->file('img_profile');
            $type = $file->getMimeType();
            $size = $file->getSize();
            $resizedImage  =   $this->resize($file, 300);
            if($enterprise->logo===null)
            {
            $logo = new Logo();
            $logo->name = $resizedImage;
            $logo->type = $type;
            $logo->size = $size;
            $logo->enterprise()->associate($enterprise);
            $logo->save();
            }else
            {
            $old_logo = Logo::find($enterprise->logo->id);
            \File::delete(public_path() . '/files/enterprises/logos/'. $enterprise->logo->name);
            $old_logo->delete();
            $logo = new Logo();
            $logo->name = $resizedImage;
            $logo->type = $type;
            $logo->size = $size;
            $logo->enterprise()->associate($enterprise);
            $logo->save();
            }
            
              }
        }

        $enterprise->save();

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
            
            
            $img->save(public_path() . '/files/enterprises/logos/' . $thumbName);

            return $thumbName;
        }
        catch(Exception $e)
        {
            return false;
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
