<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role; // <--- Import modelu Role
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;      // <--- Ważne dla hasła
use Illuminate\Support\Facades\Validator; // <--- Ważne dla walidacji
use Illuminate\Support\Facades\Log;       // <--- Do logowania błędów

class AuthController extends Controller
{
    // Rejestracja
    public function register(Request $request)
    {
        try {
            // 1. Walidacja
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // 2. Pobranie ID roli 'client' dynamicznie (bezpieczniej niż wpisywanie '1' na sztywno)
            // Jeśli nie znajdzie, domyślnie ustawi 1
            $clientRoleId = Role::where('name', 'client')->value('role_id') ?? 1;

            // 3. Tworzenie użytkownika
            $user = User::create([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $clientRoleId,
            ]);

            return response()->json(['message' => 'Rejestracja udana', 'user' => $user], 201);

        } catch (\Exception $e) {
            // Ten blok wyłapie błąd 500 i wyświetli go jako JSON
            Log::error('Błąd rejestracji: ' . $e->getMessage());
            return response()->json([
                'message' => 'Wystąpił błąd serwera',
                'error_detail' => $e->getMessage(), // To pokaże nam co jest nie tak
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    // Logowanie
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Nieprawidłowe dane logowania'], 401);
        }

        return response()->json([
            'message' => 'Zalogowano pomyślnie',
            'user' => $user
        ]);
    }
}
