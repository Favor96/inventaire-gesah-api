<?php

namespace App\Http\Controllers;

use App\Models\TypeImmobilisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class TypeImmobilisationController extends Controller
{
    public function index()
    {
        try {
            $types = TypeImmobilisation::with('immobilisations')->get();
            return response()->json(['types' => $types], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = TypeImmobilisation::create([
                'nom' => $request->nom,
            ]);
            $type = TypeImmobilisation::with('immobilisations')->find($type->id);

            return response()->json(['message' => 'Type créé avec succès', 'type' => $type], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) {
                return response()->json(['message' => 'ID invalide'], 400);
            }
            $id = $ids[0];

            $type = TypeImmobilisation::with('immobilisations')->find($id);
            if (!$type) {
                return response()->json(['message' => 'Type non trouvé'], 404);
            }
            return response()->json(['type' => $type], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) {
                return response()->json(['message' => 'ID invalide'], 400);
            }
            $id = $ids[0];

            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $type = TypeImmobilisation::find($id);
            if (!$type) {
                return response()->json(['message' => 'Type non trouvé'], 404);
            }

            $type->update($request->only(['nom']));
            $type = TypeImmobilisation::with('immobilisations')->find($type->id);

            return response()->json(['message' => 'Type mis à jour', 'type' => $type], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $hashid)
    {
        try {
            $ids = Hashids::decode($hashid);
            if (empty($ids)) {
                return response()->json(['message' => 'ID invalide'], 400);
            }
            $id = $ids[0];

            $type = TypeImmobilisation::find($id);
            if (!$type) {
                return response()->json(['message' => 'Type non trouvé'], 404);
            }
            $type->delete();
            return response()->json(['message' => 'Type supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }
}
