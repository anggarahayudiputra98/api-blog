<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Tag;

class TagController extends Controller
{
    public function tags(Request $request){

        $search = $request->search ?? null;

        $tags = Tag::when($search, function($query) use($search){
            return $query->whereLike('title', '%'.$search.'%');
        })
        ->orderBy('id', 'desc')
        ->paginate(10);

        return response()->json([
            'data' => $tags,
        ]);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'status' => ['required','int',Rule::in([0,1])]
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->all()]);
        }

        $tags = Tag::create($request->all());

        return response()->json(['message' => 'The tag was successfully stored']);
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|int',
            'title' => 'required|string',
            'status' => ['required','int',Rule::in([0, 1])]
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->all()]);
        }

        $tag = Tag::where('id',$request->id)->first();
        if(!$tag){
            return response()->json(['message' => 'Data not found'], 404);
        }
        $tag->update($request->all());

        return response()->json(['message' => 'The tag was successfully updated']);
    }

    public function destroy($id){
        $tag = Tag::where('id', $id)->first();
        if(!$tag){
            return response()->json(['message' => 'Data not found'], 404);
        }

        $tag->delete();
        return response()->json(['message' => 'The tag was successfully deleted']);
    }
}
