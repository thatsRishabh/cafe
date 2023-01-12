<?php

namespace App\Http\Controllers\Api\Common;

use Illuminate\Http\Request;
use App\Models\ResturantData;


class MobileController extends Controller
{
    function addData(Request $req)
    {

        //    form validation querry
        $req->validate(
            [
                'name'=>'required',
                'price'=>'required|numeric',
                'calories'=>'required|numeric|',
            ]
        );
        // echo '<pre>';
        //    print_r($req->all());


        //insert data into DB querry 
        $info = new ResturantData;
        $info->name =$req['name'];
        $info->photo =$req['photo'];
        $info->description =$req['description'];
        $info->calories =$req['calories'];
        $info->category =$req['category'];
        $info->price =$req['price'];
        $result=$info-> save();
        if($result)
        {
            return ['Result'=>'Your data has been saved successfully'];
        }else
        {
            return ['Result'=>'Your data has not been saved'];
        }
        
    }

    function displayData($id=null)
    {
        // return ['Result'=>'Your id is '.$id];
        // above is to debug id number


        return $id?ResturantData::find($id):ResturantData::all();
        // above logic say, if $id is available than find with respect to $id else display all
    }
}
