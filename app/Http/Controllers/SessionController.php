<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\TimeBlock;
use App\Http\Resources\SessionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\SessionRequest;
use App\Http\Requests\CalendarItemsRequest;
use Illuminate\Support\Facades\DB;
use App\Models\SessionParticipant;

class SessionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/sessions",
     *     summary="Listar todas as sessões do usuário autenticado",
     *     tags={"Sessions"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de sessões",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Session")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $sessions = Session::where('user_id', Auth::id())
            ->with(['client', 'user'])
            ->orderBy('start_time', 'desc')
            ->get();

        return SessionResource::collection($sessions);
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
     *     path="/api/sessions",
     *     summary="Criar nova sessão (individual ou em grupo)",
     *     tags={"Sessions"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SessionRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sessão criada com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Session")
     *     ),
     *     @OA\Response(response=409, description="Conflito de horário"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     *
     * O payload pode conter:
     * - client_id: para sessão individual
     * - client_ids: array de IDs para sessão em grupo
     */
    public function store(SessionRequest $request)
    {
        try {
            \Log::info('Iniciando criação de sessão', ['request' => $request->all()]);
            $validated = $request->validated();
            $validated['user_id'] = Auth::id();
            
            // Se tivermos client_ids, usamos o primeiro como client_id principal
            // para manter a compatibilidade com a estrutura do banco de dados
            if (isset($validated['client_ids']) && !empty($validated['client_ids'])) {
                $validated['client_id'] = $validated['client_ids'][0];
            }
            
            \Log::info('Dados validados', $validated);

            // Calcular end_time baseado em start_time + duration_min
            $startTime = new \DateTime($validated['start_time']);
            $endTime = (clone $startTime)->modify("+{$validated['duration_min']} minutes");

            // Lógica de conflito de horário
            if ($this->hasTimeConflict($validated['start_time'], $endTime->format('Y-m-d H:i:s'), Auth::id())) {
                return response()->json([
                    'message' => 'Conflito de horário detectado. Este horário já está ocupado por outra sessão ou bloqueio.'
                ], 409);
            }

            // Criação da sessão
            \Log::info('Criando sessão', $validated);
            $session = Session::create($validated);
            \Log::info('Sessão criada', ['session_id' => $session->id]);

            // Participantes: múltiplos ou único
            $clientIds = [];
            if (isset($validated['client_ids'])) {
                $clientIds = $validated['client_ids'];
            } elseif (isset($validated['client_id'])) {
                $clientIds = [$validated['client_id']];
            }
            
            \Log::info('IDs de clientes para adicionar', $clientIds);
            
            // Adicionar participantes à sessão
            if (!empty($clientIds)) {
                $participants = [];
                foreach ($clientIds as $clientId) {
                    $participants[] = [
                        'session_id' => $session->id, 
                        'client_id' => $clientId
                    ];
                }
                
                \Log::info('Preparando para inserir participantes', $participants);
                
                // Usar insert para evitar problemas com chave primária composta
                if (!empty($participants)) {
                    DB::table('session_participants')->insert($participants);
                    \Log::info('Participantes adicionados com sucesso');
                }
            }

            $session->load(['participants', 'user']);
            \Log::info('Sessão criada com sucesso', $session->toArray());
            return new SessionResource($session);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar sessão', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erro ao criar sessão',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/sessions/{id}",
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
     *         @OA\JsonContent(ref="#/components/schemas/Session")
     *     ),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Sessão não encontrada")
     * )
     */
    public function show(Session $session)
    {
        $this->authorizeSession($session);
        return new SessionResource($session->load(['participants', 'user']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Session $session)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/sessions/{id}",
     *     summary="Atualizar uma sessão (individual ou em grupo)",
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
     *         @OA\JsonContent(ref="#/components/schemas/SessionRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sessão atualizada com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/Session")
     *     ),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Sessão não encontrada"),
     *     @OA\Response(response=409, description="Conflito de horário"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     *
     * O payload pode conter:
     * - client_id: para sessão individual
     * - client_ids: array de IDs para sessão em grupo
     */
    public function update(SessionRequest $request, Session $session)
    {
        $this->authorizeSession($session);
        $validated = $request->validated();

        // Calcular end_time baseado em start_time + duration_min
        $startTime = new \DateTime($validated['start_time']);
        $endTime = (clone $startTime)->modify("+{$validated['duration_min']} minutes");

        // Lógica de conflito de horário (ignora a própria sessão)
        if ($this->hasTimeConflict($validated['start_time'], $endTime->format('Y-m-d H:i:s'), Auth::id(), $session->id)) {
            return response()->json([
                'message' => 'Conflito de horário detectado. Este horário já está ocupado por outra sessão ou bloqueio.'
            ], 409);
        }

        $session->update($validated);

        // Participantes: múltiplos ou único
        $clientIds = [];
        if (isset($validated['client_ids'])) {
            $clientIds = $validated['client_ids'];
        } elseif (isset($validated['client_id'])) {
            $clientIds = [$validated['client_id']];
        }
        if ($clientIds) {
            $session->participants()->sync($clientIds);
        }

        return new SessionResource($session->load(['participants', 'user']));
    }

    /**
     * @OA\Delete(
     *     path="/api/sessions/{id}",
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
    public function destroy(Session $session)
    {
        $this->authorizeSession($session);
        $session->delete();
        return response()->json(['message' => 'Sessão excluída com sucesso.']);
    }

    // Função auxiliar para garantir que a sessão pertence ao usuário autenticado
    private function authorizeSession(Session $session)
    {
        if ($session->user_id !== Auth::id()) {
            throw new AuthorizationException('Acesso negado.');
        }
    }

    // Função auxiliar para verificar conflito de horário
    private function hasTimeConflict($start, $end, $userId, $ignoreSessionId = null)
    {
        // Verifica conflito com outras sessões
        $sessionQuery = Session::where('user_id', $userId)
            ->where(function($q) use ($start, $end) {
                // start_time entre o novo intervalo
                $q->whereBetween('start_time', [$start, $end])
                  // fim da sessão existente entre o novo intervalo
                  ->orWhereRaw('(start_time + INTERVAL duration_min MINUTE) BETWEEN ? AND ?', [$start, $end])
                  // sessão existente envolve todo o novo intervalo
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_time', '<', $start)
                         ->whereRaw('(start_time + INTERVAL duration_min MINUTE) > ?', [$end]);
                  });
            });

        if ($ignoreSessionId) {
            $sessionQuery->where('id', '!=', $ignoreSessionId);
        }

        if ($sessionQuery->exists()) {
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
     *                     @OA\Schema(ref="#/components/schemas/Session"),
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
        $sessions = Session::with('participants')
            ->where('user_id', $userId)
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereRaw('(start_time + INTERVAL duration_min MINUTE) BETWEEN ? AND ?', [$start, $end])
                  ->orWhere(function($q2) use ($start, $end) {
                      $q2->where('start_time', '<', $start)
                         ->whereRaw('(start_time + INTERVAL duration_min MINUTE) > ?', [$end]);
                  });
            })
            ->get()
            ->map(function($session) {
                $sessionArray = $session->toArray();
                $sessionArray['item_type'] = 'session';
                return $sessionArray;
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
        $items = $sessions->concat($blocks)->sortBy('start_time')->values();

        return response()->json($items);
    }
}
