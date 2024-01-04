<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Article;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use File;
use App\Http\Resources\ArticleResource;

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
        $tag = $request->tag ?? null;

        $validator = Validator::make($request->all(),[
            'search' => 'nullable|string',
            'tag' => 'nullable|string',
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $articles = Article::with('tags','writter')
        ->when($search, function($query) use($search){
            return $query->where('article.title','like','%'.$search.'%');
        })
        ->when($tag, function($query) use($tag){
            $tag = Tag::where('title', $tag)->select('id')->first()->id;

            return $query->whereHas('tags', function($q) use($tag){
                $q->where('tag.id', $tag);
            });
        })
        ->where('status', 1)
        ->orderBy('id','desc')
        ->paginate(4);

        return response()->json(ArticleResource::collection($articles)->resource->toArray());
    }

    public function writterArticles(Request $request){
        $validator = Validator::make($request->all(),[
            'status' => 'required|int',
            'writter_id' => 'required|int'
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $articles = Article::with('tags','writter')
        ->where('created_by', $request->writter_id)
        ->where('status', $request->status)
        ->orderBy('id','desc')
        ->paginate(4);

        return response()->json(ArticleResource::collection($articles)->resource->toArray());
    }

    public function article(Request $request){
        $validator = Validator::make($request->all(),[
            'title' => 'required_if:id,!null',
            'id' => 'required_if:title,!null',
        ]);

        if($validator->fails()){
            return response(['message' => $validator->errors()->first()], 400);
        }

        $article = Article::with('tags')
        ->when($request->title, function($q) use ($request){
            return $q->where('title', $request->title);
        })
        ->when($request->id, function($q) use ($request){
            return $q->where('id', $request->id);
        })
        ->first();
        if(!$article){
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json([
            'data' => new ArticleResource($article)
        ], 200);
    }

    public function store(Request $request){

        // dd(auth('sanctum')->user()->id);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'required|string',
            'image' => 'required|mimes:jpeg,png,jpg,webp',
            'tags' => 'required|array|min:1',
            'tags.*' => 'required|int',
            'status' => ['required',Rule::in([0,1])]
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        DB::beginTransaction();
        try{
            $IMAGE_NAME = $this->uploadImage($request->image);

            $articles = Article::create([
                'title' => $request->title,
                'content' => $request->content,
                'status' => $request->status,
                'image' => $IMAGE_NAME,
                'created_by' => auth('sanctum')->user()->id
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
            'id' => 'required|int',
            'title' => 'required|string',
            'content' => 'required|string',
            'image' => 'required|mimes:jpeg,png,jpg,webp',
            'tags' => 'required|array|min:1',
            'tags.*' => 'required|int',
            'status' => ['required',Rule::in([0,1])]
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->first()], 400);
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

    public function updateStatus(Request $request){
        $validator = Validator::make($request->all(),[
            'id' => ['required','array','min:1'],
            'id.*' => ['required','int'],
            'status' => ['required',Rule::in([0,1])]
        ]);

        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        DB::beginTransaction();
        try{
            $article = Article::whereIn('id', $request->id)->update([
                'status' => $request->status,
            ]);

            DB::commit();

        }catch(Throwable $e){
            DB::rollback();
            return response()->json(['message' => json_encode($e->getMessage())], 500);
        }

        return response()->json([
            'message' => 'The articles was successfully updated'
        ]);
    }

    public function destroy(Request $request){
        $validator = Validator::make($request->all(),[
            'id' => 'required|array|min:1',
            'id.*' => 'required|int',
        ]);

        DB::beginTransaction();
        try{
            $article = Article::whereIn('id', $request->id)->delete();
            DB::commit();
        }catch(Throwable $e){
            DB::rollback();
            return response()->json(['message' => json_encode($e->getMessage())], 500);
        }

        return response()->json(['message' => 'The articles was successfully deleted']);
    }
}
