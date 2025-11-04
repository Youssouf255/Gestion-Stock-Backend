<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    /**
     * Get all admins
     */
    public function index(Request $request)
    {
        $query = Admin::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $admins = $query->paginate($request->get('per_page', 15));

        return response()->json($admins);
    }

    /**
     * Create new admin
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,super_admin',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $admin = Admin::create($validated);

        return response()->json([
            'message' => 'Administrateur créé avec succès',
            'admin' => $admin,
        ], 201);
    }

    /**
     * Update admin
     */
    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('admins')->ignore($id)],
            'role' => 'sometimes|in:admin,super_admin',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->has('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $admin->update($validated);

        return response()->json([
            'message' => 'Administrateur mis à jour avec succès',
            'admin' => $admin,
        ]);
    }

    /**
     * Delete admin
     */
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);

        // Prevent deleting yourself
        if ($admin->id === auth()->id()) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte',
            ], 403);
        }

        $admin->delete();

        return response()->json([
            'message' => 'Administrateur supprimé avec succès',
        ]);
    }

    /**
     * Toggle admin active status
     */
    public function toggleStatus($id)
    {
        $admin = Admin::findOrFail($id);

        $admin->update([
            'is_active' => !$admin->is_active,
        ]);

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
            'admin' => $admin,
        ]);
    }
}











