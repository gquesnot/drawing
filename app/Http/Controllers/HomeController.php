<?php


namespace App\Http\Controllers;


use App\Models\Drawing;
use App\Models\Drawing_lien;
use App\Models\User;
use Illuminate\Http\Request;


class HomeController extends Controller
{

    public function index(Request $request){

        $user= User::with("drawings", "drawings.data")->find(1);

        return response()->json($user);

    }

    public function getDraw(Request $request, $id){
        $tmp = Drawing_lien::where("drawing_id", $id)->orderBy("created_at", "DESC")->first();
        return response()->json($tmp);
    }

    public function addData(Request $request, $id){
        $tmp = Drawing_lien::create(["drawing_id" => $id, "data" => $request->data]);
        $tmp->save();
    }
}
