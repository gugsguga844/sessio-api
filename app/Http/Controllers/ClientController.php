<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * @OA\Info(
 *     title="Sessio API",
 *     version="1.0.0",
 *     description="API para gerenciamento de sessões, clientes e bloqueios de tempo."
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

use App\Models\Client;
use App\Http\Resources\ClientResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ClientRequest;

class ClientController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/clients",
     *     summary="Listar todos os clientes do usuário autenticado",
     *     tags={"Clients"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Lista de clientes")
     * )
     */
    public function index()
    {
        $clients = Client::where('user_id', Auth::id())->get();
        return ClientResource::collection($clients);
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
     *     path="/api/clients",
     *     summary="Criar novo cliente",
     *     tags={"Clients"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"full_name"},
     *             @OA\Property(property="full_name", type="string", example="João da Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@email.com"),
     *             @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="cpf_nif", type="string", example="123.456.789-00"),
     *             @OA\Property(property="emergency_contact", type="string", example="Maria Silva - (11) 99999-8888"),
     *             @OA\Property(property="case_summary", type="string", example="Paciente em acompanhamento desde 2024, apresentando melhora progressiva."),
     *             @OA\Property(property="status", type="string", enum={"Active","Inactive"}, example="Active")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Cliente criado com sucesso"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function store(ClientRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        $client = Client::create($validated);
        return new ClientResource($client);
    }

    /**
     * @OA\Get(
     *     path="/api/clients/{id}",
     *     summary="Exibir um cliente específico",
     *     tags={"Clients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Dados do cliente"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Cliente não encontrado")
     * )
     */
    public function show(Client $client)
    {
        $this->authorizeClient($client);
        return new ClientResource($client);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/clients/{id}",
     *     summary="Atualizar um cliente",
     *     tags={"Clients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"full_name"},
     *             @OA\Property(property="full_name", type="string", example="João da Silva"),
     *             @OA\Property(property="email", type="string", example="joao@email.com"),
     *             @OA\Property(property="phone", type="string", example="(11) 99999-9999"),
     *             @OA\Property(property="status", type="string", enum={"active","inactive"}, example="active")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Cliente atualizado com sucesso"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Cliente não encontrado"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function update(ClientRequest $request, Client $client)
    {
        $this->authorizeClient($client);
        $client->update($request->validated());
        return new ClientResource($client);
    }

    /**
     * @OA\Delete(
     *     path="/api/clients/{id}",
     *     summary="Deletar um cliente",
     *     tags={"Clients"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Cliente deletado com sucesso"),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Cliente não encontrado")
     * )
     */
    public function destroy(Client $client)
    {
        $this->authorizeClient($client);
        $client->delete();
        return response()->json(['message' => 'Cliente deletado com sucesso']);
    }

    // Função auxiliar para garantir que o cliente pertence ao usuário autenticado
    private function authorizeClient(Client $client)
    {
        if ($client->user_id !== Auth::id()) {
            throw new AuthorizationException('Acesso negado.');
        }
    }
}
