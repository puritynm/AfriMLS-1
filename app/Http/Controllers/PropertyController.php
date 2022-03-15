<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\property;
use App\Models\Category;
use App\Models\option;
use App\Models\city;
use App\Models\community_feauture;
use App\Models\feauture_community;
use App\Models\exterior_feauture;
use App\Models\exterior_property_feauture;
use App\Models\utility_data;
use App\Models\utility_property_feature;
use DB;
use Image;

class PropertyController extends Controller
{

// shows all propertis with the propertyType id in variable
    public function show( $propertyTypeId){
        // dd('hi');
        $data = [];

        $propertyData = category::findorFail($propertyTypeId);
        $properties = property::where('category_id',$propertyTypeId)->simplePaginate(8);

        array_push($data,['propertyData'=>$propertyData,'properties' => $properties]);
        if(empty($data)){
            dd('empty');
        }else{
            // dd($data);

         return view('property.show',['data'=>$data]);
        }
    }
    //   shows a single property
    public function showoneproperty($id){
        $property = property::findorFail($id);
        return view('property.showsingleproperty',['property'=>$property]);
        // return $property;
    }
    //displays form to add property
    public function create($propertyTypeId){
        $data = [];
        $options = option::get();
        $cities = city::orderBy('name', 'asc')->get();
        $propertyType = category::findorFail($propertyTypeId);
        // $community_feautures = DB::table('community_feautures')->select('id','community_feauture')->get();
         $f_communities = feauture_community::get();
         $exterior_feautures = exterior_feauture::get();
         $utilities_data_feautures = utility_data::get();
        //  dd($utilities_data_feautures);

        array_push($data,
        [
        'options'=>$options,
        'cities'=>$cities,
        'propertyType'=>$propertyType,
        'f_communities'=>$f_communities,
        'exterior_feautures'=>$exterior_feautures,
        'utilities_data_feautures'=>$utilities_data_feautures
        ]);
        // dd($data[0]['community_feautures']);

        return view('property.createproperty',['data'=>$data]);

    }
    //stores property
    public function store(Request $request){
        // dd($request->all());




        $image = [];
        if($request->hasFile('property_images')){
            $files = $request->property_images;
            // dd($files);
            foreach($files as $file){

                $image_name = md5(rand(1000,10000));
                $ext = strtolower($file->getClientOriginalExtension());
                $image_full_name = $image_name.'.'.$ext;
               //  $upload_path = 'public/multiple-images/';
                $upload_path = 'images/propertyImages/';
                $file->move(public_path($upload_path), $image_full_name);
                $image_url = $upload_path.$image_full_name;
                // $size = Image::make($file)->resize(500,500)->save($upload_path.$image_full_name);
                array_push($image,$image_url);
            }
            $newProperty = new property;
            $newProperty->name = $request->pname;
            $newProperty->category_id = $request->propertyId;
            $newProperty->option_id = $request->option_id;
            $newProperty->description = $request->description;
            $newProperty->category_id = $request->propertyTypeId;
            $newProperty->price = $request->property_price;
            $newProperty->city_id = $request->city_id;
            $newProperty->image = implode('|',$image);

            $newProperty->save();
           if($newProperty){

                // dd($request->exeterior_fs);
                $c_fs = $request->community_fs;
                foreach($c_fs as $cf){
                   $community_feauture = new community_feauture;
                   $community_feauture->community_feauture_id = $cf;
                   $community_feauture->property_id =  $newProperty->id;
                   $community_feauture->save();
                }
                $ext_fs = $request->exeterior_fs;
                foreach($ext_fs as $ext_f){
                    $exterior_feauture = new exterior_property_feauture;
                    $exterior_feauture->property_id = $newProperty->id;
                    $exterior_feauture->exterior_feauture_id = $ext_f;
                    $exterior_feauture->save();
                }
                $utilities_data_feautures = $request->utilities_feauture;
                foreach($utilities_data_feautures as $ut_data_f){
                    $utilities_data_feauture = new utility_property_feature;
                    $utilities_data_feauture->property_id = $newProperty->id;
                    $utilities_data_feauture->utility_data_id = $ut_data_f;
                    $utilities_data_feauture->save();

                }



           }else{
               return back()->with('error','from empty');
           }

            return back();
            session()->flash('success','upload done successfully');


        }else{
            dd('image does not exist');
        }




    }


    public function myproperties(){
        return view('property.myproperties');
    }

    public function viewPageshow($id){

       $data = [];
       $property = property::findorFail($id);
       //community feautures fetch
       $cfs = $property->community_feautures;
       $feauture_communities = [];
       foreach($cfs as $c_f){
          $f_commuities = feauture_community::where('id',$c_f->community_feauture_id)->get();
         array_push($feauture_communities,['real_c_feautures'=>$f_commuities]);
       }
      //exterior feautes fetch
       $exterior_feautures = [];
       $ext_fs = $property->exterior_property_feautures;
       foreach($ext_fs as $ext_f){
        $f_commuities = exterior_feauture::where('id',$ext_f->exterior_feauture_id)->get();
       array_push($exterior_feautures,['real_ext_feautures'=>$f_commuities]);
     }
     //utilities data fetch
     $utilities_data = [];
     $utilities_data_fs = $property->utility_property_feautures;
     foreach($utilities_data_fs as $utility_data_f){
        $f_commuities = utility_data::where('id',$utility_data_f->utility_data_id)->get();
       array_push($utilities_data,['real_utilities_data_feautures'=>$f_commuities]);
     }


       $propertyOption = $property->option;
       $city = $property->city;
       $location = $property->location;
       $country = $city->country;

       array_push($data,
              [
              'property'=>$property,
              'location'=>$location,
              'city'=>$city,
              'country'=>$country,
              'propertyOption'=>$propertyOption,
              'feauture_communities' =>$feauture_communities,
              'exterior_feautures' =>$exterior_feautures,
              'utilities_data' =>$utilities_data,

            ]);
    //   dd($data[0]['feauture_communities']);

         if(empty($data)){
             dd('empty');
         }else{

            return view('property.PageViewshow',['data'=>$data]);


            // return view('property.viewPageshow',['data'=>$data]);

         }






    }








}
