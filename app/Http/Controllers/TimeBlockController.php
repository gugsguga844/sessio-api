<?php

namespace App\Http\Controllers;

use App\Models\TimeBlock;
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
     *     summary="Listar todos os bloqueios de tempo do usuário autenticado",
     *     tags={"TimeBlocks"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Lista de bloqueios de tempo")
     * )
     */
    public function index()
    {
        $blocks = TimeBlock::where('user_id', Auth::id())->get();
        return response()->json($blocks);
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
     *     summary="Criar novo bloqueio de tempo",
     *     tags={"TimeBlocks"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","start_time","end_time"},
     *             @OA\Property(property="title", type="string", example="Almoço"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2024-07-04T12:00:00"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2024-07-04T13:00:00"),
     *             @OA\Property(property="color", type="string", example="#FF0000")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Bloqueio criado com sucesso"),
     *     @OA\Response(response=409, description="Conflito de horário"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function store(TimeBlockRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        // Lógica de conflito de horário
        if ($this->hasTimeConflict($validated['start_time'], $validated['end_time'], Auth::id())) {
            return response()->json([
                'message' => 'Conflito de horário detectado. Este horário já está ocupado por outra sessão ou bloqueio.'
            ], 409);
        }

        $block = TimeBlock::create($validated);
        return response()->json($block, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/time-blocks/{id}",
     *     summary="Exibir um bloqueio de tempo específico",
     *     tags={"TimeBlocks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Dados do bloqueio de tempo"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Bloqueio não encontrado")
     * )
     */
    public function show(TimeBlock $timeBlock)
    {
        $this->authorizeBlock($timeBlock);
        return response()->json($timeBlock);
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
     *     summary="Atualizar um bloqueio de tempo",
     *     tags={"TimeBlocks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","start_time","end_time"},
     *             @OA\Property(property="title", type="string", example="Almoço"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2024-07-04T12:00:00"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2024-07-04T13:00:00"),
     *             @OA\Property(property="color", type="string", example="#FF0000")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Bloqueio atualizado com sucesso"),
     *     @OA\Response(response=409, description="Conflito de horário"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Bloqueio não encontrado"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function update(TimeBlockRequest $request, TimeBlock $timeBlock)
    {
        $this->authorizeBlock($timeBlock);
        $validated = $request->validated();

        // Lógica de conflito de horário (ignora o próprio time block)
        if ($this->hasTimeConflict($validated['start_time'], $validated['end_time'], Auth::id(), $timeBlock->id)) {
            return response()->json([
                'message' => 'Conflito de horário detectado. Este horário já está ocupado por outra sessão ou bloqueio.'
            ], 409);
        }

        $timeBlock->update($validated);
        return response()->json($timeBlock);
    }

    /**
     * @OA\Delete(
     *     path="/api/time-blocks/{id}",
     *     summary="Deletar um bloqueio de tempo",
     *     tags={"TimeBlocks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Bloqueio deletado com sucesso"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Bloqueio não encontrado")
     * )
     */
    public function destroy(TimeBlock $timeBlock)
    {
        $this->authorizeBlock($timeBlock);
        $timeBlock->delete();
        return response()->json(['message' => 'Time block deletado com sucesso.']);
    }

    // Função auxiliar para garantir que o time block pertence ao usuário autenticado
    private function authorizeBlock(TimeBlock $block)
    {
        if ($block->user_id !== Auth::id()) {
            throw new AuthorizationException('Acesso negado.');
        }
    }

    // Função auxiliar para verificar conflito de horário
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
