<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;
use App\Models\UserPreference;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar novo usuário",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"full_name","email","password","password_confirmation"},
     *             @OA\Property(property="full_name", type="string", example="Maria Silva"),
     *             @OA\Property(property="professional_title", type="string", example="Dra.", nullable=true),
     *             @OA\Property(property="email", type="string", example="maria@email.com"),
     *             @OA\Property(property="password", type="string", example="senha123"),
     *             @OA\Property(property="password_confirmation", type="string", example="senha123"),
     *             @OA\Property(property="phone", type="string", example="+5511999999999"),
     *             @OA\Property(property="specialty", type="string", example="Psicóloga Clínica"),
     *             @OA\Property(property="professional_license", type="string", example="CRP 06/123456"),
     *             @OA\Property(property="cpf_nif", type="string", example="123.456.789-00"),
     *             @OA\Property(property="office_address", type="string", example="Rua Exemplo, 123 - São Paulo/SP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string", example="1|abcdef123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dados inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Os dados fornecidos são inválidos."),
     *             @OA\Property(property="errors", type="object", example={"email": {"O email já está em uso."}})
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'professional_title' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'specialty' => 'nullable|string|max:100',
            'professional_license' => 'nullable|string|max:50',
            'cpf_nif' => 'nullable|string|max:20',
            'office_address' => 'nullable|string'
        ]);

        $user = User::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'professional_title' => $validated['professional_title'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'specialty' => $validated['specialty'] ?? null,
            'professional_license' => $validated['professional_license'] ?? null,
            'cpf_nif' => $validated['cpf_nif'] ?? null,
            'office_address' => $validated['office_address'] ?? null
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Autenticar usuário e obter token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="maria@email.com"),
     *             @OA\Property(property="password", type="string", example="senha123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string", example="1|abcdef123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="As credenciais fornecidas estão incorretas.")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais estão incorretas.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Desconectar usuário (revogar token)",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Não autenticado.")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso']);
    }

    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Obter informações do usuário autenticado",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informações do usuário",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado"
     *     )
     * )
     */
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * @OA\Patch(
     *     path="/api/me",
     *     summary="Atualizar dados do usuário autenticado",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="full_name", type="string", example="Maria Silva"),
     *             @OA\Property(property="email", type="string", example="maria@email.com"),
     *             @OA\Property(property="professional_title", type="string", example="Dra."),
     *             @OA\Property(property="phone", type="string", example="+5511999999999"),
     *             @OA\Property(property="specialty", type="string", example="Psicóloga Clínica"),
     *             @OA\Property(property="professional_license", type="string", example="CRP 06/123456"),
     *             @OA\Property(property="cpf_nif", type="string", example="123.456.789-00"),
     *             @OA\Property(property="office_address", type="string", example="Rua Exemplo, 123 - São Paulo/SP"),
     *             @OA\Property(property="image_url", type="string", example="https://sessio-files.s3.us-east-2.amazonaws.com/images/foto.png")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'professional_title' => 'sometimes|nullable|string|max:20',
            'phone' => 'sometimes|nullable|string|max:20',
            'specialty' => 'sometimes|nullable|string|max:100',
            'professional_license' => 'sometimes|nullable|string|max:50',
            'cpf_nif' => 'sometimes|nullable|string|max:20',
            'office_address' => 'sometimes|nullable|string',
            'image_url' => 'sometimes|nullable|url',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Usuário atualizado com sucesso!',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/me/preferences",
     *     summary="Obter preferências do usuário autenticado",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Preferências do usuário",
     *         @OA\JsonContent(
     *             @OA\Property(property="preferences", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function getPreferences(Request $request)
    {
        $user = $request->user();
        $prefs = $user->preference;
        if (!$prefs) {
            $prefs = UserPreference::create(['user_id' => $user->id]);
        }
        return response()->json(['preferences' => $prefs]);
    }

    /**
     * @OA\Patch(
     *     path="/api/me/preferences",
     *     summary="Atualizar preferências do usuário autenticado",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="client_terminology", type="string", example="Cliente"),
     *             @OA\Property(property="default_calendar_view", type="string", example="Weekly"),
     *             @OA\Property(property="visible_calendar_days", type="integer", example=5),
     *             @OA\Property(property="show_canceled_sessions", type="boolean", example=false),
     *             @OA\Property(property="interface_theme", type="string", example="Light"),
     *             @OA\Property(property="language", type="string", example="pt-BR")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preferências atualizadas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="preferences", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=422, description="Dados inválidos")
     * )
     */
    public function updatePreferences(Request $request)
    {
        $user = $request->user();
        $prefs = $user->preference;
        if (!$prefs) {
            $prefs = UserPreference::create(['user_id' => $user->id]);
        }
        $validated = $request->validate([
            'client_terminology' => 'sometimes|string|in:Cliente,Paciente',
            'default_calendar_view' => 'sometimes|string|in:Weekly,Daily,Monthly',
            'visible_calendar_days' => 'sometimes|integer|min:1|max:7',
            'show_canceled_sessions' => 'sometimes|boolean',
            'interface_theme' => 'sometimes|string|in:Light,Dark',
            'language' => 'sometimes|string|max:10',
        ]);
        $prefs->fill($validated);
        $prefs->updated_at = now();
        $prefs->save();
        return response()->json([
            'message' => 'Preferências atualizadas com sucesso!',
            'preferences' => $prefs,
        ]);
    }
}
