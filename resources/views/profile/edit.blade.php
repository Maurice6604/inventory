@extends('layouts.app')

@section('title', 'My Profile')
@section('header', 'My Profile')

@section('breadcrumbs')
    <span class="text-gray-300">/</span>
    <span class="text-[hsl(var(--text-main))] font-medium">Profile</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- ── Profile Updated Banner ── --}}
    @if (session('status') === 'profile-updated')
        <div class="premium-card bg-green-50/80 border-green-200 text-[hsl(var(--success))] p-4 flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="font-medium">Profile information updated successfully.</span>
        </div>
    @endif

    {{-- ── Account Info Card ── --}}
    <div class="premium-card overflow-hidden">
        <div class="section-header">
            <span class="section-title">Account Information</span>
            <span class="badge badge-{{ $user->role === 'admin' ? 'primary' : 'secondary' }} capitalize">{{ $user->role }}</span>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="p-6 space-y-5">
            @csrf
            @method('patch')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="premium-label" for="name">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                           class="premium-input" required autofocus autocomplete="name">
                    @error('name')
                        <p class="mt-1.5 text-xs text-[hsl(var(--danger))]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="premium-label" for="email">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                           class="premium-input" required autocomplete="username">
                    @error('email')
                        <p class="mt-1.5 text-xs text-[hsl(var(--danger))]">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-2 flex items-center gap-4">
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

    {{-- ── Password Card — Admin Only ── --}}
    @if(Auth::user()->isAdmin())
    <div class="premium-card overflow-hidden">
        <div class="section-header">
            <span class="section-title">Change Password</span>
            <span class="badge badge-warning">Admin Only</span>
        </div>

        @if (session('status') === 'password-updated')
            <div class="mx-6 mt-4 p-3 bg-green-50 border border-green-200 text-[hsl(var(--success))] rounded-xl text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Password updated successfully.
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="p-6 space-y-5">
            @csrf
            @method('put')

            <div>
                <label class="premium-label" for="current_password">Current Password</label>
                <input id="current_password" name="current_password" type="password"
                       class="premium-input" autocomplete="current-password">
                @error('current_password', 'updatePassword')
                    <p class="mt-1.5 text-xs text-[hsl(var(--danger))]">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="premium-label" for="password">New Password</label>
                    <input id="password" name="password" type="password"
                           class="premium-input" autocomplete="new-password">
                    @error('password', 'updatePassword')
                        <p class="mt-1.5 text-xs text-[hsl(var(--danger))]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="premium-label" for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                           class="premium-input" autocomplete="new-password">
                    @error('password_confirmation', 'updatePassword')
                        <p class="mt-1.5 text-xs text-[hsl(var(--danger))]">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary">Update Password</button>
            </div>
        </form>
    </div>
    @else
    {{-- ── Staff: show read-only password notice ── --}}
    <div class="premium-card p-6 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        </div>
        <div>
            <p class="font-semibold text-[hsl(var(--text-main))]">Password changes are restricted</p>
            <p class="text-sm text-[hsl(var(--text-muted))] mt-0.5">Only administrators can change account passwords. Contact your system administrator if you need a reset.</p>
        </div>
    </div>
    @endif

    {{-- ── Danger Zone — Admin Only ── --}}
    @if(Auth::user()->isAdmin())
    <div class="premium-card overflow-hidden border border-[hsl(var(--danger))]/20">
        <div class="section-header bg-red-50/50">
            <span class="section-title text-[hsl(var(--danger))]">Danger Zone</span>
            <span class="badge badge-danger">Destructive</span>
        </div>

        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-[hsl(var(--danger))]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-[hsl(var(--text-main))]">Delete Account</p>
                    <p class="text-sm text-[hsl(var(--text-muted))] mt-0.5 mb-4">Once deleted, all data will be permanently removed. This action cannot be undone.</p>

                    <button onclick="document.getElementById('modal-delete-account').classList.remove('hidden')"
                            class="btn-danger !py-2 !px-4 !text-xs">
                        Delete My Account
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ── Delete Account Modal ── --}}
<div id="modal-delete-account" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="premium-card w-full max-w-md p-6 animate-fade-in border-t-4 border-[hsl(var(--danger))]">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-[hsl(var(--danger))]">Confirm Account Deletion</h3>
            <button onclick="document.getElementById('modal-delete-account').classList.add('hidden')" class="btn-icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <p class="text-sm text-[hsl(var(--text-muted))] mb-5">Please enter your password to confirm you want to permanently delete your account.</p>

        <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4">
            @csrf
            @method('delete')
            <div>
                <label class="premium-label" for="del_password">Your Password</label>
                <input id="del_password" name="password" type="password"
                       class="premium-input" placeholder="Enter your current password" required>
                @error('password', 'userDeletion')
                    <p class="mt-1.5 text-xs text-[hsl(var(--danger))]">{{ $message }}</p>
                @enderror
            </div>
            <div class="pt-2">
                <button type="submit" class="btn-danger w-full justify-center">Yes, Delete My Account</button>
            </div>
        </form>
    </div>
</div>
@endsection
