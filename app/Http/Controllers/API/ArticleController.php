<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Article;
use Illuminate\Support\Facades\DB;
use File;

class ArticleController extends Controller
{

    private function uploadImage($image){

        $extension = strtolower($image->getClientOriginalExtension());
        $IMAGE_NAME = $image->getClientOriginalName();

        $path = env("ARTICLES_FILE_PATH");

        if (!File::exists($path . '/' .$IMAGE_NAME)) {
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            $IMAGE_NAME = time() . "_" . uniqid() . "." . $extension;
            $image->move($path, $IMAGE_NAME);
        }

        return $IMAGE_NAME;
    }

    public function articles(Request $request){

        $search = $request->search ?? null;
        $tags = $request->tags ?? null;

        $articles = Article::with('tags')
        ->when($search, function($query) use($search){
            return $query->where('article.title','like','%'.$search.'%');
        })
        ->when($tags, function($query) use($tags){
            return $query->whereHas('tags', function($q) use($tags){
                $q->whereIn('tag.id', $tags);
            });
        })
        ->orderBy('id','desc')
        ->paginate(10);

        return response()->json([
            'data' => $articles,
        ]);
    }

    public function article($id){
        $article = Article::with('tags')->where('id', $id)->first();
        if(!$article){
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json([
            'data' => $article
        ]);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'required|string',
            'image' => 'required|mimes:jpeg,png,jpg,webp',
            'tags' => 'required|array|min:1',
            'tags.*' => 'required|int',
            'status' => ['required',Rule::in([0,1])]
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->all()]);
        }

        DB::beginTransaction();
        try{
            $IMAGE_NAME = $this->uploadImage($request->image);

            $articles = Article::create([
                'title' => $request->title,
                'content' => $request->content,
                'status' => $request->status,
                'image' => $IMAGE_NAME
            ]);

            $articles->tags()->attach($request->tags);
            DB::commit();
        }catch(Throwable $e){
            DB::rollback();
            return response()->json(['message' => json_encode($e->getMessage())], 500);
        }

        return response()->json(['message' => 'The article was successfully stored']);
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(),[
            'id' => 'required:int',
            'title' => 'required|string',
            'content' => 'required|string',
            'image' => 'required|mimes:jpeg,png,jpg,webp',
            'tags' => 'required|array|min:1',
            'tags.*' => 'required|int',
            'status' => ['required',Rule::in([0,1])]
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->all()]);
        }

        $article = Article::where('id', $request->id)->first();
        if(!$article){
            return response()->json(['message' => 'Data not found'], 404);
        }

        DB::beginTransaction();
        try{
            $IMAGE_NAME = $this->uploadImage($request->image);

            $article->update([
                'title' => $request->title,
                'content' => $request->content,
                'status' => $request->status,
                'image' => $IMAGE_NAME
            ]);
            $article->tags()->sync($request->tags);
            DB::commit();
        }catch(Throwable $e){
            DB::rollback();
            return response()->json(['message' => json_encode($e->getMessage())], 500);
        }

        return response()->json(['message' => 'The article was successfully updated']);

    }

    public function destroy($id){
        $article = Article::where('id', $id)->first();
        if(!$article){
            return response()->json(['message' => 'Data not found'], 404);
        }

        $article->delete();
        return response()->json(['message' => 'The article was successfully deleted']);
    }
}
