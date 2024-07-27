<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnswerRequest;
use App\Http\Requests\AnswerVoteRequest;
use App\Http\Requests\ValidateAnswerRequest;
use App\Models\Answer;
use App\Models\AnswerValidation;
use App\Models\AnswerVote;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;


class AnswerController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function validateAnswer(Request $request, Answer $answer)
{
    // Vérifiez si l'utilisateur est un superviseur ou un administrateur
    $user = auth()->user();

    if ($user->role !== 'supervisor' && $user->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Enregistrez la validation de la réponse
    AnswerValidation::create([
        'supervisor_id' => $user->id,
        'answer_id' => $answer->id,
    ]);

    // Incrémenter le compteur de validations pour l'utilisateur qui a fourni la réponse
    $answeringUser = $answer->user;
    $answeringUser->validated_questions_count += 1;

    // Promouvoir l'utilisateur au rôle de superviseur s'il a validé 10 questions
    if ($answeringUser->validated_questions_count >= 10) {
        $answeringUser->role = 'supervisor';
    }

    $answeringUser->save();

    return response()->json(['message' => 'Answer validated successfully']);
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
    /* public function store(AnswerRequest $request)
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = auth()->user();

            // Récupérer la question à laquelle on veut répondre
            $question = Question::find($request->question_id);

            // Vérifier si la question existe
            if (!$question) {
                return response()->json([
                    'message' => 'Question introuvable',
                    'status' => 404
                ], 404);
            }

            // Vérifier si la question a été posée par un superviseur
            if ($question->user->role === 'supervisor') {
                // Vérifier si l'utilisateur connecté est un superviseur
                if ($user->role !== 'supervisor') {
                    return response()->json([
                        'message' => 'Seuls les superviseurs peuvent répondre aux questions posées par les superviseurs',
                        'status' => 403
                    ], 403);
                }
            }

            if (Gate::denies('create', Answer::class)) {
                return response()->json([
                    'message' => 'Vous n\'avez pas la permission de creer une reponse',
                    'status' => 403
                ], 403);
            }
            $answer = Answer::create($request->validated());
            return response()->json([
                'answer' => $answer,
                'message' => 'Reponse ajoutee avec succes',
                'status' => 201
            ], 201);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'ajout de la reponse',
                'status' => 500
            ], 500);
        }
    } */
    public function store(Request $request, Question $question)
    {
        // Valider les données reçues du formulaire
        $validatedData = $request->validate([
            'body' => 'required|string|max:500',
        ]);

        // Créer une nouvelle réponse associée à la question
        $answer = new Answer();
        $answer->question_id = $question->id;
        $answer->user_id = auth()->id(); // Assurez-vous d'avoir l'utilisateur authentifié
        $answer->body = $validatedData['body'];
        $answer->save();

        // Optionnel : charger les relations nécessaires pour la réponse
        $answer->load('user'); // Charge l'utilisateur qui a répondu

        // Retourner la réponse nouvellement créée en tant que JSON
        return response()->json([
            'message' => 'Réponse ajoutée avec succès',
            'answer' => $answer,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        /*try {
            $answers = $question->answers;
            return response()->json([
                'answers' => $answers,
                'message' => 'Reponses recuperees avec succes',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la recuperation des reponses',
                'status' => 500
            ], 500);
        }*/
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
//        try {
//            $answer = Answer::find($id);
//            return response()->json([
//                'answer' => $answer,
//                'message' => 'Reponse recuperee avec succes',
//                'status' => 200
//            ], 200);
//        } catch (\Exception $e) {
//            return response()->json([
//                'message' => 'Erreur lors de la recuperation de la reponse',
//                'status' => 500
//            ], 500);
//        }
    }

    /**
     * Update the specified resource in storage.
     */
   /*  public function update(AnswerRequest $request, string $id)
    {
        try {
            $answer = Answer::find($id);
            if (!$answer) {
                return response()->json([
                    'message' => 'Reponse introuvable',
                    'status' => 404
                ], 404);
            }
            if (Gate::denies('update', $answer)) {
                return response()->json([
                    'message' => 'Vous n\'avez pas la permission de modifier cette reponse',
                    'status' => 403
                ], 403);
            }
            $answer->update($request->validated());
            return response()->json([
                'answer' => $answer,
                'message' => 'Reponse modifiee avec succes',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la modification de la reponse',
                'status' => 500
            ], 500);
        }
    } */
    public function update(Request $request, Answer $answer)
    {
        // Vérifie si l'utilisateur est autorisé à mettre à jour la réponse
        if (Gate::denies('update', $answer)) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à modifier cette réponse.',
                'status' => 403
            ], 403);
        }

        // Validation des données
        $validatedData = $request->validate([
            'body' => 'required|string',
        ]);

        // Met à jour la réponse avec les données validées
        $answer->update($validatedData);

        // Retourne la réponse JSON avec la réponse mise à jour
        return response()->json(['answer' => $answer]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $answer = Answer::find($id);
            if (!$answer) {
                return response()->json([
                    'message' => 'Reponse introuvable',
                    'status' => 404
                ], 404);
            }
            if (Gate::denies('delete', $answer)) {
                return response()->json([
                    'message' => 'Vous n\'avez pas la permission de supprimer cette reponse',
                    'status' => 403
                ], 403);
            }
            $answer->delete();
            return response()->json([
                'message' => 'Reponse supprimee avec succes',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la reponse',
                'status' => 500
            ], 500);
        }
    }

    /*public function is_validated(ValidateAnswerRequest $request, Answer $answer)
    {
        try {
            if (User::where('id', $request->supervisor_id)->first()->role !== 'supervisor') {
                return response()->json([
                    'message' => 'Vous n\'etes pas un superviseur',
                    'status' => 403
                ], 403);
            }
            $answer->is_validated = true;
            $answer->save();
            $answerValidation = AnswerValidation::create([
                'answer_id' => $answer->id,
                'supervisor_id' => $request->validated('supervisor_id')
            ]);
            return response()->json([
                'answer' => $answer,
                'answer_validation' => $answerValidation,
                'message' => 'Reponse validee avec succes',
                'status' => 200
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la validation de la reponse',
                'status' => 500
            ], 500);
        }

    }*/

    public function incrementVote(Answer $answer)
    {
        try {
            // Recuperer l'utilisateur qui a vote
            $user = Auth()->user();
            // Verifier si l'utilisateur est connecte
            if (!$user) {
                return response()->json([
                    'message' => 'Vous devez etre connecte pour voter pour une reponse',
                    'status' => 403
                ], 403);
            }
            // Verifier si l'utilisateur est un superviseur ou un admin
            if ($user->role !== 'supervisor' && $user->role !== 'admin') {
                return response()->json([
                    'message' => 'Il faut avoir 10 de reputation pour voter pour une reponse',
                    'status' => 403
                ], 403);
            }
//            return $user;
            // Verifier si l'utilisateur a deja vote pour cette reponse
            if (AnswerVote::where('user_id', $user->id)->where('answer_id', $answer->id)->exists()) {
                $answerVote = AnswerVote::where('user_id', $user->id)->where('answer_id', $answer->id)->first();
//                return $answerVote;
                // Verifier si l'utilisateur a deja vote pour cette reponse en decrementant le vote
                if ($answerVote->decrement_vote) {
                    $answerVote->decrement_vote = false;
                    $answerVote->increment_vote = false;
                    $answerVote->save();
                    $answer->increment('votes');
                    return response()->json([
                        'answer' => $answer,
                        'message' => 'Vote ajoute avec succes',
                        'status' => 200
                    ], 200);
                } else if (!$answerVote->increment_vote) {
                    $answerVote->increment_vote = true;
                    $answerVote->save();
                    $answer->increment('votes');
                    return response()->json([
                        'answer' => $answer,
                        'message' => 'Vote ajoute avec succes',
                        'status' => 200
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Vous avez deja vote pour cette reponse',
                        'status' => 403
                    ], 403);
                }
            }
            // Ajouter l'utilisateur a la liste des votants pour cette reponse
            AnswerVote::create([
                'user_id' => $user->id,
                'answer_id' => $answer->id,
                'increment_vote' => true,
                'decrement_vote' => false
            ]);
            $answer->increment('votes');
            return response()->json([
                'answer' => $answer,
                'message' => 'Vote ajoute avec succes',
                'status' => 200
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'ajout du vote',
                'status' => 500
            ], 500);
        }
    }

    public function decrementVote(Answer $answer)
    {
        try {
            // Recuperer l'utilisateur qui a vote
            $user = Auth()->user();
            // Verifier si l'utilisateur est un superviseur
            if ($user->role !== 'supervisor' && $user->role !== 'admin') {
                return response()->json([
                    'message' => 'Il faut avoir 10 de reputation pour voter pour une reponse',
                    'status' => 403
                ], 403);
            }
            // Verifier si l'utilisateur a deja vote pour cette reponse
            if (AnswerVote::where('user_id', $user->id)->where('answer_id', $answer->id)->exists()) {
                $answerVote = AnswerVote::where('user_id', $user->id)->where('answer_id', $answer->id)->first();
                // Verifier si l'utilisateur a deja vote pour cette reponse en incrementant le vote
                if ($answerVote->increment_vote) {
                    $answerVote->increment_vote = false;
                    $answerVote->decrement_vote = false;
                    $answerVote->save();
                    $answer->decrement('votes');
                    return response()->json([
                        'answer' => $answer,
                        'message' => 'Vote retire avec succes',
                        'status' => 200
                    ], 200);
                } else if (!$answerVote->decrement_vote) {
                    $answerVote->decrement_vote = true;
                    $answerVote->save();
                    $answer->decrement('votes');
                    return response()->json([
                        'answer' => $answer,
                        'message' => 'Vote retire avec succes',
                        'status' => 200
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Vous avez deja vote pour cette reponse',
                        'status' => 403
                    ], 403);
                }
            }
            // Ajouter l'utilisateur a la liste des votants pour cette reponse
            $answerVote = AnswerVote::create([
                'user_id' => $user->id,
                'answer_id' => $answer->id,
                'increment_vote' => false,
                'decrement_vote' => true
            ]);
            $answer->decrement('votes');
            return response()->json([
                'answer' => $answer,
                'answer_vote' => $answerVote,
                'message' => 'Vote retire avec succes',
                'status' => 200
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du vote',
                'status' => 500
            ], 500);
        }
    }
}
