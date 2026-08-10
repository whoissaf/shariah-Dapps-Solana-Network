<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DocumentService
{
    public function upload(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            $file = $data['file'];

            $path = $file->store('documents/' . $user->id, 'local');

            $document = Document::create([
                'user_id' => $user->id,
                'claim_id' => $data['claim_id'],
                'document_type' => $data['document_type'],
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'status' => 'uploaded',
            ]);

            return [
                'message' => 'Document uploaded.',
                'document' => $this->format($document),
            ];
        });
    }

    public function list(User $user): array
    {
        $documents = Document::where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        return [
            'message' => 'Documents list.',
            'documents' => $documents->map(function (Document $document) {
                return $this->format($document);
            })->all(),
        ];
    }

    private function format(Document $document): array
    {
        return [
            'id' => $document->id,
            'claim_id' => $document->claim_id,
            'document_type' => $document->document_type,
            'original_name' => $document->original_name,
            'file_path' => $document->file_path,
            'mime_type' => $document->mime_type,
            'size_bytes' => $document->size_bytes,
            'status' => $document->status,
            'created_at' => $document->created_at?->toIso8601String(),
        ];
    }
}
