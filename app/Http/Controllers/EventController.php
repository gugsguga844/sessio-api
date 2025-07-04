<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\EventRequest;
use App\Http\Requests\CalendarItemsRequest;

class EventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="Listar todos os eventos do usuário autenticado",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Lista de eventos")
     * )
     */
    public function index()
    {
        $events = Event::where('user_id', Auth::id())->get();
        return response()->json($events);
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
     *     summary="Criar novo evento",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id","start_time","end_time","type","payment_status"},
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Sessão de terapia"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2024-07-04T10:00:00"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2024-07-04T11:00:00"),
     *             @OA\Property(property="notes", type="string", example="Paciente ansioso"),
     *             @OA\Property(property="type", type="string", enum={"presencial","online"}, example="presencial"),
     *             @OA\Property(property="payment_status", type="string", enum={"pago","pendente"}, example="pendente")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Evento criado com sucesso"),
     *     @OA\Response(response=409, description="Conflito de horário"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function store(EventRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        // Lógica de conflito de horário
        if ($this->hasTimeConflict($validated['start_time'], $validated['end_time'], Auth::id())) {
            return response()->json([
                'message' => 'Conflito de horário detectado. Este horário já está ocupado por outra sessão ou bloqueio.'
            ], 409);
        }

        $event = Event::create($validated);
        return response()->json($event, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/events/{id}",
     *     summary="Exibir um evento específico",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Dados do evento"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Evento não encontrado")
     * )
     */
    public function show(Event $event)
    {
        $this->authorizeEvent($event);
        return response()->json($event);
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
     *     summary="Atualizar um evento",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id","start_time","end_time","type","payment_status"},
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Sessão de terapia"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2024-07-04T10:00:00"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2024-07-04T11:00:00"),
     *             @OA\Property(property="notes", type="string", example="Paciente ansioso"),
     *             @OA\Property(property="type", type="string", enum={"presencial","online"}, example="presencial"),
     *             @OA\Property(property="payment_status", type="string", enum={"pago","pendente"}, example="pendente")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Evento atualizado com sucesso"),
     *     @OA\Response(response=409, description="Conflito de horário"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Evento não encontrado"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function update(EventRequest $request, Event $event)
    {
        $this->authorizeEvent($event);
        $validated = $request->validated();

        // Lógica de conflito de horário (ignora o próprio evento)
        if ($this->hasTimeConflict($validated['start_time'], $validated['end_time'], Auth::id(), $event->id)) {
            return response()->json([
                'message' => 'Conflito de horário detectado. Este horário já está ocupado por outra sessão ou bloqueio.'
            ], 409);
        }

        $event->update($validated);
        return response()->json($event);
    }

    /**
     * @OA\Delete(
     *     path="/api/events/{id}",
     *     summary="Deletar um evento",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Evento deletado com sucesso"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Evento não encontrado")
     * )
     */
    public function destroy(Event $event)
    {
        $this->authorizeEvent($event);
        $event->delete();
        return response()->json(['message' => 'Evento deletado com sucesso.']);
    }

    // Função auxiliar para garantir que o evento pertence ao usuário autenticado
    private function authorizeEvent(Event $event)
    {
        if ($event->user_id !== Auth::id()) {
            throw new AuthorizationException('Acesso negado.');
        }
    }

    // Função auxiliar para verificar conflito de horário
    private function hasTimeConflict($start, $end, $userId, $ignoreEventId = null)
    {
        // Verifica conflito com outros eventos
        $eventQuery = Event::where('user_id', $userId)
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_time', '<', $start)
                         ->where('end_time', '>', $end);
                  });
            });
        if ($ignoreEventId) {
            $eventQuery->where('id', '!=', $ignoreEventId);
        }
        if ($eventQuery->exists()) {
            return true;
        }
        // Verifica conflito com time blocks
        $blockQuery = \App\Models\TimeBlock::where('user_id', $userId)
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
     *     summary="Listar todos os itens do calendário (eventos e bloqueios) em um período",
     *     tags={"Events"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="start", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="end", in="query", required=true, @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Lista de itens do calendário"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function calendarItems(CalendarItemsRequest $request)
    {
        $validated = $request->validated();
        $userId = Auth::id();
        $start = $validated['start'];
        $end = $validated['end'];

        $events = Event::where('user_id', $userId)
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
                $event->item_type = 'session';
                return $event;
            });

        $blocks = \App\Models\TimeBlock::where('user_id', $userId)
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

        $items = $events->concat($blocks)->sortBy('start_time')->values();
        return response()->json($items);
    }
}
