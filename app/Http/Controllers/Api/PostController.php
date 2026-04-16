<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;

class PostController extends BaseController
{
    public function index()
    {
        $posts = Post::with('user')->latest()->get();

        return $this->success($posts);
    }

    public function store(StorePostRequest $request)
    {
        $post = $request->user()->posts()->create(
            $request->validated()
        );

        return $this->success($post, 'Post creado', 201);
    }

    public function show($id)
    {
        $post = \App\Models\Post::with('user')->find($id);
        if (!$post) {
            return $this->error('Post no encontrado', null, 404);
        }

        return $this->success($post);
    }

    public function update(UpdatePostRequest $request, $id)
    {
        if ($post = \App\Models\Post::find($id)) {
            if ($post->user_id != $request->user()->id) {
                return $this->error('No autorizado', null, 403);
            }
            $post->update($request->validated());

            return $this->success($post, 'Post actualizado');
        } else {
            return $this->error('Post no encontrado', null, 404);
        }
    }

    public function destroy(Request $request, $id)
    {

        $post = \App\Models\Post::findOrFail($id);
        if ($request->user()->id !== $post->user_id) {
            return $this->error('No autorizado', null, 403);
        }

        $post->delete();

        return $this->success(null, 'Post eliminado');
    }

    public function filterByTitle(Request $request)
    {
        $title = $request->input('title');
        $perPage = $request->input('per_page', 10);
        $posts = $request->user()
        ->posts()
        ->where('title', 'like', '%' . $title . '%')
        ->paginate($perPage);
        if ($posts->isEmpty()) {
            return $this->error('No se encontraron resultados para el titulo: ' . $title, null, 404);
        }
        return $this->success($posts);
    }
}
