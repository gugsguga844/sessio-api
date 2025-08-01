<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Aws\S3\S3Client;

class ImageUploadController extends Controller
{
    /**
     * @OA\Post(
     *     path="/upload-image",
     *     summary="Faz upload de uma imagem para o S3 e retorna a URL pública",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Arquivo de imagem para upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Upload realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="url", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */
        public function upload(Request $request)
    {
        try {
            // Validação básica
            if (!$request->hasFile('image')) {
                return response()->json([
                    'message' => 'Nenhuma imagem foi enviada',
                    'error' => 'Arquivo não encontrado'
                ], 400);
            }

            $file = $request->file('image');

            // Validação do tipo de arquivo
            if (!$file->isValid()) {
                return response()->json([
                    'message' => 'Arquivo inválido',
                    'error' => 'Arquivo corrompido ou inválido'
                ], 400);
            }

            // Configuração do S3 para a região us-east-2
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => 'us-east-2', // Forçando a região correta
                'credentials' => [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ]);

            // Gera um nome único para o arquivo
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'images/' . $fileName;

            // Upload direto para S3
            $result = $s3Client->putObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key'    => $path,
                'Body'   => $file->get()
            ]);

            // Gera a URL pública usando o formato correto para a região us-east-2
            $url = "https://sessio-files.s3.us-east-2.amazonaws.com/" . $path;

            return response()->json([
                'message' => 'Upload realizado com sucesso!',
                'path' => $path,
                'url' => $url,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no upload de imagem', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|max:2048',
        ]);


    }


}
