<?php

namespace App\Http\Controllers;

use App\Models\TimeBlock;
use App\Http\Resources\TimeBlockResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\TimeBlockRequest;

class TimeBlockController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/time-blocks",
     *     summary="Listar todos os blocos de tempo do usuário autenticado",
     *     tags={"TimeBlocks"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de blocos de tempo",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TimeBlock")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function index(): JsonResponse
    {
        $blocks = TimeBlock::where('user_id', Auth::id())->get();
        return response()->json(TimeBlockResource::collection($blocks));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/time-blocks",
     *     summary="Criar um novo bloco de tempo",
     *     tags={"TimeBlocks"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "start_time", "end_time"},
     *             @OA\Property(property="title", type="string", maxLength=100, example="Almoço"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2023-01-01T12:00:00-03:00"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2023-01-01T13:00:00-03:00"),
     *             @OA\Property(property="color_hex", type="string", nullable=true, example="#FF5733"),
     *             @OA\Property(property="emoji", type="string", nullable=true, maxLength=5, example="🍽️")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bloco de tempo criado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/TimeBlock")
     *     ),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function store(TimeBlockRequest $request): JsonResponse
    {
        $timeBlock = TimeBlock::create(
            array_merge(
                $request->validated(),
                ['user_id' => Auth::id()]
            )
        );

        return response()->json(new TimeBlockResource($timeBlock), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/time-blocks/{id}",
     *     summary="Exibir um bloco de tempo específico",
     *     tags={"TimeBlocks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do bloco de tempo",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bloco de tempo encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/TimeBlock")
     *     ),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Bloco de tempo não encontrado")
     * )
     */
    public function show(TimeBlock $timeBlock): JsonResponse
    {
        $this->authorize('view', $timeBlock);
        return response()->json(new TimeBlockResource($timeBlock));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeBlock $timeBlock)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/time-blocks/{id}",
     *     summary="Atualizar um bloco de tempo existente",
     *     tags={"TimeBlocks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do bloco de tempo",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "start_time", "end_time"},
     *             @OA\Property(property="title", type="string", maxLength=100, example="Almoço Atualizado"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2023-01-01T12:30:00-03:00"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2023-01-01T13:30:00-03:00"),
     *             @OA\Property(property="color_hex", type="string", nullable=true, example="#FF5733"),
     *             @OA\Property(property="emoji", type="string", nullable=true, maxLength=5, example="🍽️")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bloco de tempo atualizado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/TimeBlock")
     *     ),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Bloco de tempo não encontrado"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function update(TimeBlockRequest $request, TimeBlock $timeBlock): JsonResponse
    {
        $this->authorize('update', $timeBlock);
        $timeBlock->update($request->validated());
        return response()->json(new TimeBlockResource($timeBlock));
    }

    /**
     * @OA\Delete(
     *     path="/api/time-blocks/{id}",
     *     summary="Excluir um bloco de tempo",
     *     tags={"TimeBlocks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do bloco de tempo",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=204, description="Bloco de tempo excluído com sucesso"),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Bloco de tempo não encontrado")
     * )
     */
    public function destroy(TimeBlock $timeBlock): JsonResponse
    {
        $this->authorize('delete', $timeBlock);
        $timeBlock->delete();
        return response()->json(null, 204);
    }

    /**
     * Verifica se há conflito de horário para o bloco de tempo
     * 
     * @param string $start Data/hora de início
     * @param string $end Data/hora de término
     * @param string $userId ID do usuário
     * @param string|null $ignoreBlockId ID do bloco a ser ignorado (para atualizações)
     * @return bool
     */
    private function hasTimeConflict($start, $end, $userId, $ignoreBlockId = null)
    {
        // Verifica conflito com outros time blocks
        $blockQuery = TimeBlock::where('user_id', $userId)
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_time', '<', $start)
                         ->where('end_time', '>', $end);
                  });
            });
            
        if ($ignoreBlockId) {
            $blockQuery->where('id', '!=', $ignoreBlockId);
        }
        
        if ($blockQuery->exists()) {
            return true;
        }
        
        // Verifica conflito com eventos
        $eventQuery = \App\Models\Event::where('user_id', $userId)
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_time', '<', $start)
                         ->where('end_time', '>', $end);
                  });
            });
            
        return $eventQuery->exists();
    }
}
