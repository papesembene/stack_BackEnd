<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionRequest;
use App\Models\Question;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;


class QuestionController extends Controller
{

    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        if (Gate::denies('viewAny', Question::class)) {
//            return 'Vous n\'avez pas la permission de voir les questions';
            return response()->json([
                'message' => 'Vous n\'avez pas la permission de voir les questions',
                'status' => 403
            ], 403);
        }
        try {
            // recuperer les questions avec les tags et les reponses associees
            $questions = Question::with(['tags', 'answers.user', 'user'])
            ->withCount('answers')
            ->get();
            return response()->json([
                'questions' => $questions,
                'message' => 'Donnees recuperees avec succes',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recuperation des donnees',
                'status' => 500
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuestionRequest $request)
    {
        try {
//            return $request->validated();
            $question = Question::create([
                'title' => $request->validated('title'),
                'body' => $request->validated('body'),
                'user_id' => $request->validated('user_id')

            ]);
            $question->tags()->sync($request->validated('tags'));
            // formater la question et les tags dans une meme variable
            $question = Question::with('tags')->find($question->id);
            return response()->json([
                'question' => $question,
                'message' => 'Question creee avec succes',
                'status' => 201
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la creation de la question',
                'status' => 500
            ], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $question = Question::with(['tags', 'answers.user', 'user'])->find($id);
            if (!$question) {
                return response()->json([
                    'message' => 'Question non trouvee',
                    'status' => 404
                ], 404);
            }
            if (Gate::denies('view', $question)) {
                return response()->json([
                    'message' => 'Vous n\'avez pas la permission de voir cette question',
                    'status' => 403
                ], 403);
            }
            return response()->json([
                'question' => $question,
                'message' => 'Donnees recuperees avec succes',
                'status' => 200
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recuperation des donnees',
                'status' => 500
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $question = Question::find($id);
            if (!$question) {
                return response()->json([
                    'message' => 'Question non trouvee',
                    'status' => 404
                ], 404);
            }
            if (Gate::denies('update', $question)) {
                return response()->json([
                    'message' => 'Vous n\'avez pas la permission de modifier cette question',
                    'status' => 403
                ], 403);
            }
            return response()->json([
                'question' => $question,
                'message' => 'Donnees recuperees avec succes',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recuperation des donnees',
                'status' => 500
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
   /*  public function update(QuestionRequest $request, string $id)
    {
        try {
            $question = Question::with('tags')->find($id);
            if (!$question) {
                return response()->json([
                    'message' => 'Question non trouvee',
                    'status' => 404
                ], 404);
            }
            if (Gate::denies('update', $question)) {
                return response()->json([
                    'message' => 'Vous n\'avez pas la permission de modifier cette question',
                    'status' => 403
                ], 403);
            }
            $question->update([
                'title' => $request->validated('title'),
                'body' => $request->validated('body'),
                'user_id' => $request->validated('user_id')
            ]);
            $question->tags()->sync($request->validated('tags'));
            return response()->json([
                'question' => $question,
                'message' => 'Question modifiee avec succes',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la modification de la question',
                'status' => 500
            ], 500);
        }
    } */
    public function update(Request $request, Question $question)
    {
        // Vérifie si l'utilisateur est autorisé à mettre à jour la question
        if (Gate::denies('update', $question)) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à modifier cette question.',
                'status' => 403
            ], 403);
        }

        // Validation des données
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        // Met à jour la question avec les données validées
        $question->update($validatedData);

        // Charge les relations au besoin
        $question->load('tags');

        // Retourne la réponse JSON avec la question mise à jour
        return response()->json(['question' => $question]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $question = Question::find($id);
            if (!$question) {
                return response()->json([
                    'message' => 'Question non trouvee',
                    'status' => 404
                ], 404);
            }
            if (Gate::denies('delete', $question)) {
                return response()->json([
                    'message' => 'Vous n\'avez pas la permission de supprimer cette question',
                    'status' => 403
                ], 403);
            }
            $question->delete();
            return response()->json([
                'message' => 'Question supprimee avec succes',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la question',
                'status' => 500
            ], 500);
        }
    }

}
