<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator, File, DB, Exception, Auth;
use App\Models\Api\V1\Post;
use App\Models\User;
use App\Models\Api\V1\PostTag;
use App\Models\Api\V1\Tag;
use OpenApi\Annotations as OA;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
    * @OA\Post(
    *     path="/api/v1/post",
    *     summary="Guardar Post",    *      
    *      @OA\Parameter(
     *         name="titulo",
     *         in="query",
     *         description="titulo del post",
     *         required=true,
     *      ),
     *      @OA\Parameter(
     *         name="texto",
     *         in="query",
     *         description="texto del post",
     *         required=true,
     *      ),
     *       @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="lista de tags del post",
     *         required=true,
     *      ),
    *     @OA\Response(
    *         response=200,
    *         description="Guarda el post."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function post(Request $request)
    {
        if($request->titulo && $request->texto && count($request->tags) > 0){
            DB::beginTransaction();
            try{
                $postModel = DB::table('posts')
                                ->where('titulo', $request->titulo) 
                                ->where('estado', 1)
                                ->select('*')
                                ->first();
                if($postModel){
                    return response()->json(["estado" => "error", "mensaje" => "Se encontro un post con el mismo titulo.", "data" => null]);
                }

                $post = new Post();
                $post->user_id = auth()->user()->id;
                $post->titulo = $request->titulo;
                $post->texto = $request->texto;
                $post->estado = 1;
                $post->save();

                foreach($request->tags as $tag){
                    $tagModel = DB::table('tags')
                                ->where('titulo', $tag) 
                                ->where('estado', 1)
                                ->select('*')
                                ->first();
                    $tagId = 0;
                    if(empty($tagModel)){
                        $nuevoTag = new Tag();
                        $nuevoTag->titulo = $tag;
                        $nuevoTag->estado = 1;
                        $nuevoTag->save();
                        $tagId = $nuevoTag->id;
                    }else{
                        $tagId = $tagModel->id;
                    }
                    $postTag = new PostTag();
                    $postTag->post_id = $post->id;
                    $postTag->tag_id = $tagId;
                    $postTag->estado = 1;
                    $postTag->save();
                }
                DB::commit();
                return response()->json(["estado" => "success", "mensaje" => "Se registro con exito el post.", "data" => null]);
            }catch(\Exception $exception){
                return response()->json(["estado" => "error", "mensaje" => $exception->getMessage(), "data" => null]);
                DB::rollBack();
            }
        }else{
            return response()->json(["estado" => "error", "mensaje" => "Se encontraron campos vacios que son obligatorios.", "data" => null]);
        }
    }

     /**
    * @OA\Get(
    *     path="/api/v1/posts",
    *     summary="Lista de Posts",    *   
    *     security={
     *         {"bearer": {}}
     *     },
    *     @OA\Response(
    *         response=200,
    *         description="Genera la lista de posts."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function posts(Request $request)
    {
        $posts = Post::all();
        if(count($posts) > 0){
            foreach($posts as $post){
                $tags = DB::table('post_tags')
                            ->join('tags', 'tags.id', '=', 'post_tags.tag_id')
                            ->where('post_tags.post_id', $post->id) 
                            ->where('post_tags.estado', 1)
                            ->select('post_tags.*')
                            ->get();
                $post->tags = $tags;
            }
        }
        return response()->json(["estado" => "success", "mensaje" => "Se cargo con exito la lista.", "data" => $posts]);
    }
}
