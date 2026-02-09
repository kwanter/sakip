@extends('layouts.modern')

@section('title', 'Pengaturan Akun')

@section('page-title', 'Pengaturan Akun')

@section('content')
@php($user = $user ?? auth()->user())
<style>
    .settings-section {
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-lg);
        margin-bottom: var(--space-lg);
    }

    .settings-header {
        display: flex;
        align-items: center;
        gap: var(--space-md);
        padding: var(--space-lg);
        border-bottom: 1px solid var(--border-light);
    }

    .settings-header h3 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .settings-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .settings-body {
        padding: var(--space-lg);
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: var(--space-lg);
    }

    .form-group {
        margin-bottom: var(--space-md);
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: var(--space-sm);
    }

    .form-group input {
        width: 100%;
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        color: var(--text-primary);
        background: var(--bg-surface);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-md);
        transition: all var(--transition-fast);
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary-400);
        box-shadow: 0 0 0 3px var(--primary-100);
    }

    .form-group .error {
        color: var(--danger);
        font-size: 0.75rem;
        margin-top: var(--space-xs);
    }

    .form-text {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        margin-top: var(--space-xs);
    }

    .info-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--space-lg);
    }

    .info-list-item {
        padding: var(--space-md);
        background: var(--gray-50);
        border-radius: var(--radius-md);
    }

    .info-list-item-label {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        margin-bottom: var(--space-xs);
    }

    .info-list-item-value {
        font-weight: 500;
        color: var(--text-primary);
    }

    [data-theme="dark"] .info-list-item {
        background: var(--gray-800);
    }
</style>

<div style="max-width: 800px; margin: 0 auto;">
    <!-- User Summary -->
    <div class="modern-card" style="margin-bottom: var(--space-lg);">
        <div class="card-body">
            <div style="display: flex; align-items: center; gap: var(--space-lg);">
                <div style="width: 56px; height: 56px; border-radius: var(--radius-full); background: linear-gradient(135deg, var(--primary-400), var(--primary-600)); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                    <i class="fas fa-user"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: var(--text-primary); font-size: 1.125rem;">{{ $user->name }}</div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem;">{{ $user->email }}</div>
                </div>
                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span style="margin-left: var(--space-sm);">Kembali ke Profil</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Change Password Section -->
    <div class="settings-section">
        <div class="settings-header">
            <div class="settings-icon" style="background: var(--warning-light); color: var(--warning);">
                <i class="fas fa-lock"></i>
            </div>
            <h3>Ubah Kata Sandi</h3>
        </div>
        <div class="settings-body">
            <form method="POST" action="{{ route('settings.password.update') }}">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="current_password">Kata Sandi Saat Ini</label>
                        <input type="password"
                               id="current_password"
                               name="current_password"
                               required
                               autocomplete="current-password"
                               placeholder="Masukkan kata sandi saat ini">
                        @error('current_password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group"></div>

                    <div class="form-group">
                        <label for="password">Kata Sandi Baru</label>
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               autocomplete="new-password"
                               placeholder="Masukkan kata sandi baru">
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Minimal 8 karakter.</div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               required
                               autocomplete="new-password"
                               placeholder="Ulangi kata sandi baru">
                    </div>
                </div>

                <div style="margin-top: var(--space-lg);">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span style="margin-left: var(--space-sm);">Simpan Kata Sandi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Account Info Section -->
    <div class="settings-section">
        <div class="settings-header">
            <div class="settings-icon" style="background: var(--primary-50); color: var(--primary-600);">
                <i class="fas fa-info-circle"></i>
            </div>
            <h3>Informasi Akun</h3>
        </div>
        <div class="settings-body">
            <div class="info-list">
                <div class="info-list-item">
                    <div class="info-list-item-label">Nama Lengkap</div>
                    <div class="info-list-item-value">{{ $user->name }}</div>
                </div>
                <div class="info-list-item">
                    <div class="info-list-item-label">Email</div>
                    <div class="info-list-item-value">{{ $user->email }}</div>
                </div>
                <div class="info-list-item">
                    <div class="info-list-item-label">Terdaftar Sejak</div>
                    <div class="info-list-item-value">{{ $user->created_at->format('d M Y') }}</div>
                </div>
                <div class="info-list-item">
                    <div class="info-list-item-label">Terakhir Login</div>
                    <div class="info-list-item-value">{{ optional($user->last_login_at)->format('d M Y, H:i') ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Tips -->
    <div class="settings-section">
        <div class="settings-header">
            <div class="settings-icon" style="background: var(--success-light); color: var(--success);">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Tips Keamanan</h3>
        </div>
        <div class="settings-body">
            <ul style="margin: 0; padding-left: var(--space-lg); color: var(--text-secondary);">
                <li style="margin-bottom: var(--space-sm);">Gunakan kata sandi yang kuat dengan kombinasi huruf, angka, dan simbol.</li>
                <li style="margin-bottom: var(--space-sm);">Jangan gunakan kata sandi yang sama untuk akun lain.</li>
                <li style="margin-bottom: var(--space-sm);">Ganti kata sandi secara berkala untuk keamanan akun.</li>
                <li>Jangan bagikan kata sandi Anda kepada siapapun.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
