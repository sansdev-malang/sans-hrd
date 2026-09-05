<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user() ?? Auth::user(),
        ]);
    }

    /**
     * Update the user's profile information (Name & Email).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Informasi profil akun berhasil diperbarui.');
    }

    /**
     * Update user photo via AJAX (Instant Auto-Save).
     */
    public function updatePhoto(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'photo.required' => 'Pilih file foto terlebih dahulu.',
            'photo.image' => 'File yang diunggah harus berupa gambar/foto.',
            'photo.mimes' => 'Format foto harus berupa JPG, JPEG, PNG, atau WebP.',
            'photo.max' => 'Ukuran file foto tidak boleh melebihi 2MB (2048 KB).',
        ]);

        $user = $request->user() ?? Auth::user();

        // Delete old photo if it exists
        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->decode($request->file('photo')->getRealPath());
        
        // Resize and crop to 400x400 sharp square avatar
        $image->cover(400, 400);
        
        // Encode as lightweight WebP with 80% quality
        $encoded = $image->encode(new WebpEncoder(80));

        $filename = 'avatars/' . uniqid('avatar_') . '.webp';
        Storage::disk('public')->put($filename, (string) $encoded);

        $user->photo = $filename;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diperbarui.',
            'photo_url' => asset('storage/' . $user->photo) . '?v=' . time(),
        ]);
    }

    /**
     * Delete user photo via AJAX (Instant Auto-Save).
     */
    public function destroyPhoto(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user() ?? Auth::user();

        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->photo = null;
        $user->save();

        $nameParts = explode(' ', $user->name);
        $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil dihapus.',
            'initials' => $initials,
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
