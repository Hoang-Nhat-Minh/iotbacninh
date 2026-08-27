<?php

namespace App\Http\Controllers\Labeling;

use App\Http\Controllers\Controller;
use App\Models\Labeling\LabelKnowledgeBase;
use App\Models\Labeling\LabelKnowledgeChunk;
use App\Models\Labeling\LabelKnowledgeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $query = LabelKnowledgeDocument::with(['knowledgeBase', 'chunks']);

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhereHas('chunks', function ($cq) use ($search) {
                        $cq->where('chunk_text', 'like', "%{$search}%");
                    });
            });
        }

        // Knowledge Base Filter
        if ($request->filled('knowledge_base_id')) {
            $query->where('knowledge_base_id', $request->input('knowledge_base_id'));
        }

        $documents = $query->latest()->get();

        $documents->transform(function ($doc) {
            $doc->chunks_count = $doc->chunks->count();
            $doc->tokens_count = $doc->chunks->sum('token_count');
            return $doc;
        });

        $knowledgeBases = LabelKnowledgeBase::with('documents.chunks')->get();

        $knowledgeBases->transform(function ($kb) {
            $kb->documents_count = $kb->documents->count();
            $kb->chunks_count = $kb->documents->sum(fn($d) => $d->chunks->count());
            $kb->tokens_count = $kb->documents->sum(fn($d) => $d->chunks->sum('token_count'));
            return $kb;
        });

        return view('labeling.knowledge.index', compact('documents', 'knowledgeBases'));
    }

    public function storeBase(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $kb = LabelKnowledgeBase::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', "Đã tạo Kho tri thức RAG '{$kb->name}' thành công!");
    }

    public function storeDocument(Request $request)
    {
        $request->validate([
            'knowledge_base_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'source_type' => 'nullable|string',
        ]);

        $doc = LabelKnowledgeDocument::create([
            'knowledge_base_id' => $request->input('knowledge_base_id'),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'source_type' => $request->input('source_type', 'manual_entry'),
            'status' => 'active',
        ]);

        // Auto-Chunking
        $this->generateChunksForDocument($doc);

        return redirect()->back()->with('success', "Đã thêm tài liệu tri thức '{$doc->title}' và tự động chia Chunks thành công!");
    }

    public function updateDocument(Request $request, $id)
    {
        $request->validate([
            'knowledge_base_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|string',
        ]);

        $doc = LabelKnowledgeDocument::findOrFail($id);
        $doc->update([
            'knowledge_base_id' => $request->input('knowledge_base_id'),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'status' => $request->input('status'),
        ]);

        // Re-generate Chunks
        LabelKnowledgeChunk::where('document_id', $doc->id)->delete();
        $this->generateChunksForDocument($doc);

        return redirect()->back()->with('success', "Đã cập nhật tài liệu '{$doc->title}' và chia lại Chunks!");
    }

    public function destroyDocument($id)
    {
        $doc = LabelKnowledgeDocument::findOrFail($id);
        $title = $doc->title;

        LabelKnowledgeChunk::where('document_id', $doc->id)->delete();
        $doc->delete();

        return redirect()->back()->with('success', "Đã xóa tài liệu tri thức '{$title}'!");
    }

    public function destroyBase($id)
    {
        $kb = LabelKnowledgeBase::findOrFail($id);
        $name = $kb->name;

        $docIds = LabelKnowledgeDocument::where('knowledge_base_id', $kb->id)->pluck('id');
        LabelKnowledgeChunk::whereIn('document_id', $docIds)->delete();
        LabelKnowledgeDocument::whereIn('id', $docIds)->delete();
        $kb->delete();

        return redirect()->back()->with('success', "Đã xóa Kho tri thức '{$name}' và dữ liệu liên quan!");
    }

    public function getChunks($documentId)
    {
        $doc = LabelKnowledgeDocument::with('chunks')->findOrFail($documentId);

        return response()->json([
            'success' => true,
            'document' => $doc,
            'chunks' => $doc->chunks,
        ]);
    }

    private function generateChunksForDocument(LabelKnowledgeDocument $doc)
    {
        $text = $doc->content;
        $sentences = explode('. ', $text);

        foreach ($sentences as $idx => $s) {
            $trimmed = trim($s);
            if (!empty($trimmed)) {
                LabelKnowledgeChunk::create([
                    'document_id' => $doc->id,
                    'content' => $trimmed,
                    'chunk_text' => $trimmed,
                    'token_count' => str_word_count($trimmed),
                    'vector_id' => 'vec_' . $doc->id . '_' . ($idx + 1),
                    'status' => 'indexed',
                ]);
            }
        }
    }
}
