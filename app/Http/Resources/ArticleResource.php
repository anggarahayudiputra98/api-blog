<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\UserResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'image' => env('APP_URL').'/articles/'.$this->image,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'tags' => TagResource::collection($this->Tags),
            'writter' => new UserResource($this->writter)
        ];
    }
}
