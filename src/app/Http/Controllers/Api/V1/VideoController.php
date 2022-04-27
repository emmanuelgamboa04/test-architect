<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator, File, DB, Exception, Auth;
use App\Models\Api\V1\Post;
use App\Models\User;
use App\Models\Api\V1\Tag;
use App\Models\Api\V1\VideoTag;
use App\Models\Api\V1\Video;
use OpenApi\Annotations as OA;

class VideoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
    * @OA\Post(
    *     path="/api/v1/video",
    *     summary="Guardar video",    *      
    *      @OA\Parameter(
     *         name="titulo",
     *         in="query",
     *         description="titulo del video",
     *         required=true,
     *      ),
     *       @OA\Parameter(
     *         name="tags",
     *         in="query",
     *         description="lista de tags del video",
     *         required=true,
     *      ),
    *     @OA\Response(
    *         response=200,
    *         description="Guarda el video."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function video(Request $request)
    {
        if($request->titulo && count($request->tags) > 0){
            DB::beginTransaction();
            try{
                $videoModel = DB::table('videos')
                                ->where('titulo', $request->titulo) 
                                ->where('estado', 1)
                                ->select('*')
                                ->first();
                if($videoModel){
                    return response()->json(["estado" => "error", "mensaje" => "Se encontro un video con el mismo titulo.", "data" => null]);
                }

                $video = new Video();
                $video->user_id = auth()->user()->id;
                $video->titulo = $request->titulo;
                $video->url = $request->titulo;
                $video->estado = 1;
                $video->save();

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
                    $videoTag = new VideoTag();
                    $videoTag->video_id = $video->id;
                    $videoTag->tag_id = $tagId;
                    $videoTag->estado = 1;
                    $videoTag->save();
                }
                DB::commit();
                return response()->json(["estado" => "success", "mensaje" => "Se registro con exito el video.", "data" => null]);
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
    *     path="/api/v1/videos",
    *     summary="Lista de videos",    *   
    *     security={
     *         {"bearer": {}}
     *     },
    *     @OA\Response(
    *         response=200,
    *         description="Genera la lista de videos."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function videos(Request $request)
    {
        $videos = Video::all();
        if(count($videos) > 0){
            foreach($videos as $video){
                $tags = DB::table('video_tags')
                            ->join('tags', 'tags.id', '=', 'video_tags.tag_id')
                            ->where('video_tags.video_id', $video->id) 
                            ->where('video_tags.estado', 1)
                            ->select('video_tags.*')
                            ->get();
                $video->tags = $tags;
            }
        }
        return response()->json(["estado" => "success", "mensaje" => "Se cargo con exito la lista.", "data" => $videos]);
    }
}
