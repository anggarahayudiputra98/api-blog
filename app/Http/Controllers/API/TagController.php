<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Tag;
use App\Http\Resources\TagResource;

class TagController extends Controller
{
    public function tags(Request $request){

        $validator = Validator::make($request->all(),[
            'search' => 'nullable|string',
            'limit' => 'nullable|numeric'
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->all()], 400);
        }

        $search = $request->search ?? null;
        $limit = $request->limit ?? null;

        $tags = Tag::when($search, function($query) use($search){
            return $query->whereLike('title', '%'.$search.'%');
        })
        ->when($limit, function($query) use ($limit){
            return $query->limit($limit);
        })
        ->orderBy('title', 'asc')
        ->get();

        return response()->json(TagResource::collection($tags));
    }
}
