<?php


namespace App\Http\Controllers;


use App\Models\Drawing;
use App\Models\User;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class HomeController extends Controller
{

    public function index(Request $request){
        if ($request->withData == true){
            $user= User::with("drawings", "drawings.data")->find(1);
        }
        else{
            $user= User::with("drawings")->find(1);

        }

        return response()->json($user);

    }

    public function getDraw(Request $request, $id){
        $tmp = Drawing::find($id);
        return response()->json($tmp);
    }

    public function getImageList(Request $request){
        $images= Image::all();
        return response()->json(["images"=>$images, "imagePath" => public_path("public")]);

    }

    public function uploadImages(Request $request){
        $i = 0;
        $result = [];
        foreach ($request->files->getIterator()['fields']['assetsFieldHandle'] as $image){
            $filename = time() + $i .'.'. $image->getClientOriginalExtension();
            $image-> move(public_path("public"), $filename);

            $tmp = Image::create(["name"=>$image->getClientOriginalName(), "filePath" => $filename]);
            $tmp->save();
            $result [] = $tmp;
            $i+=1;
        }
        return response()->json($result);
    }

    public function changeTitle(Request  $request, $id){
        $tmp = Drawing::find($id);
        $tmp->title = $request->title;
        $tmp->save();
        return response()->json(["code" => "ok"]);
    }

    public function newDraw(Request $request){
        $user = Auth::user();
        $tmp = Drawing::create(["title"=> $request->title, "description" => "desccription", "user_id" => $user->id, "height" => $request->height, "width" => $request->width]);
        return response()->json($tmp);
    }

    public function deleteDraw(Request $request, $id){
        Drawing::where("id", $id)->delete();
    }

    public function saveData(Request $request, $id){
        $tmp = Drawing::find($id);
        $tmp->data = $request->data;
        $tmp->save();
    }
}
