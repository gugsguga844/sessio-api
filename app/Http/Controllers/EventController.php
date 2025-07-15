<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TimeBlock;
use App\Http\Resources\EventResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\EventRequest;
use App\Http\Requests\CalendarItemsRequest;
use Illuminate\Support\Facades\DB;
use App\Models\EventParticipant;

class EventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="Listar todas as sessões do usuário autenticado",
     *     tags={"Sessions"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de sessões",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Event")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $events = Event::where('user_id', Auth::id())
            ->with(['client', 'user'])
            ->orderBy('start_time', 'desc')
            ->get();

        return EventResource::collection($events);
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
     *     path="/api/events",
     *     summary="Criar nova sessão",
     *     tags={"Sessions"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/EventRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sessão criada com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(response=409, description="Conflito de horário"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function store(EventRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        // Calcular end_time baseado em start_time + duration_min
        $startTime = new \DateTime($validated['start_time']);
        $endTime = (clone $startTime)->modify("+{$validated['duration_min']} minutes");

        // Lógica de conflito de horário
        if ($this->hasTimeConflict($validated['start_time'], $endTime->format('Y-m-d H:i:s'), Auth::id())) {
            return response()->json([
                'message' => 'Conflito de horário detectado. Este horário já está ocupado por outra sessão ou bloqueio.'
            ], 409);
        }

        // Criação do evento
        $event = Event::create($validated);

        // Participantes: múltiplos ou único
        $clientIds = [];
        if (isset($validated['client_ids'])) {
            $clientIds = $validated['client_ids'];
        } elseif (isset($validated['client_id'])) {
            $clientIds = [$validated['client_id']];
        }
        if ($clientIds) {
            $event->participants()->sync($clientIds);
        }

        return new EventResource($event->load(['participants', 'user']));
    }

    /**
     * @OA\Get(
     *     path="/api/events/{id}",
     *     summary="Exibir uma sessão específica",
     *     tags={"Sessions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da sessão",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dados da sessão",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Sessão não encontrada")
     * )
     */
    public function show(Event $event)
    {
        $this->authorizeEvent($event);
        return new EventResource($event->load(['client', 'user']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/events/{id}",
     *     summary="Atualizar uma sessão",
     *     tags={"Sessions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da sessão",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/EventRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sessão atualizada com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Sessão não encontrada"),
     *     @OA\Response(response=409, description="Conflito de horário"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function update(EventRequest $request, Event $event)
    {
        $this->authorizeEvent($event);
        $validated = $request->validated();

        // Calcular end_time baseado em start_time + duration_min
        $startTime = new \DateTime($validated['start_time']);
        $endTime = (clone $startTime)->modify("+{$validated['duration_min']} minutes");

        // Lógica de conflito de horário (ignora o próprio evento)
        if ($this->hasTimeConflict($validated['start_time'], $endTime->format('Y-m-d H:i:s'), Auth::id(), $event->id)) {
            return response()->json([
                'message' => 'Conflito de horário detectado. Este horário já está ocupado por outra sessão ou bloqueio.'
            ], 409);
        }

        $event->update($validated);

        // Participantes: múltiplos ou único
        $clientIds = [];
        if (isset($validated['client_ids'])) {
            $clientIds = $validated['client_ids'];
        } elseif (isset($validated['client_id'])) {
            $clientIds = [$validated['client_id']];
        }
        if ($clientIds) {
            $event->participants()->sync($clientIds);
        }

        return new EventResource($event->load(['participants', 'user']));
    }

    /**
     * @OA\Delete(
     *     path="/api/events/{id}",
     *     summary="Excluir uma sessão",
     *     tags={"Sessions"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da sessão",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sessão excluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sessão excluída com sucesso.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Sessão não encontrada")
     * )
     */
    public function destroy(Event $event)
    {
        $this->authorizeEvent($event);
        $event->delete();
        return response()->json(['message' => 'Sessão excluída com sucesso.']);
    }

    // Função auxiliar para garantir que o evento pertence ao usuário autenticado
    private function authorizeEvent(Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            throw new AuthorizationException('Acesso negado.');
        }
    }

    // Função auxiliar para verificar conflito de horário
    /**
     * Verifica se há conflito de horário para um determinado período
     *
     * @param string $start Data/hora de início
     * @param string $end Data/hora de fim
     * @param int $userId ID do usuário (terapeuta)
     * @param int|null $ignoreEventId ID do evento a ser ignorado (para atualizações)
     * @return bool
     */
    private function hasTimeConflict($start, $end, $userId, $ignoreEventId = null)
    {
        // Verifica conflito com outros eventos
        $eventQuery = Event::where('user_id', $userId)
            ->where(function($q) use ($start, $end) {
                // start_time entre o novo intervalo
                $q->whereBetween('start_time', [$start, $end])
                  // fim do evento existente entre o novo intervalo
                  ->orWhereRaw('(start_time + INTERVAL duration_min MINUTE) BETWEEN ? AND ?', [$start, $end])
                  // evento existente envolve todo o novo intervalo
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_time', '<', $start)
                         ->whereRaw('(start_time + INTERVAL duration_min MINUTE) > ?', [$end]);
                  });
            });

        if ($ignoreEventId) {
            $eventQuery->where('id', '!=', $ignoreEventId);
        }

        if ($eventQuery->exists()) {
            return true;
        }

        // Verifica conflito com time blocks
        $blockQuery = DB::table('time_blocks')
            ->where('user_id', $userId)
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_time', '<', $start)
                         ->where('end_time', '>', $end);
                  });
            });

        return $blockQuery->exists();
    }

    /**
     * @OA\Get(
     *     path="/api/calendar-items",
     *     summary="Listar todos os itens do calendário (sessões e bloqueios) em um período",
     *     tags={"Calendar"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         required=true,
     *         description="Data de início do período (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end",
     *         in="query",
     *         required=true,
     *         description="Data de término do período (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de itens do calendário",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 oneOf={
     *                     @OA\Schema(ref="#/components/schemas/Event"),
     *                     @OA\Schema(ref="#/components/schemas/TimeBlock")
     *                 },
     *                 @OA\Property(property="item_type", type="string", enum={"session", "block"})
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function calendarItems(CalendarItemsRequest $request)
    {
        $validated = $request->validated();
        $userId = Auth::id();
        $start = $validated['start'] . ' 00:00:00';
        $end = $validated['end'] . ' 23:59:59';

        // Buscar sessões no período
        $events = Event::with('participants')
            ->where('user_id', $userId)
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_time', '<', $start)
                         ->where('end_time', '>', $end);
                  });
            })
            ->get()
            ->map(function($event) {
                $eventArray = $event->toArray();
                $eventArray['item_type'] = 'session';
                return $eventArray;
            });

        // Buscar bloqueios de tempo no período
        $blocks = TimeBlock::where('user_id', $userId)
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_time', '<', $start)
                         ->where('end_time', '>', $end);
                  });
            })
            ->get()
            ->map(function($block) {
                $block->item_type = 'block';
                return $block;
            });

        // Combinar e ordenar por data de início
        $items = $events->concat($blocks)->sortBy('start_time')->values();

        return response()->json($items);
    }
}
