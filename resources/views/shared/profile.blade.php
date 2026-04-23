@php
$layout = match ((int) $user->role_id) {
1 => 'admin.main',
2 => 'moderator.main',
3 => 'tutor.main',
4 => 'student.main',
default => 'layouts.app',
};
@endphp

@extends($layout)

@section('content')
@php
if ($user->profile_photo_url) {
$profilePhoto = str_starts_with($user->profile_photo_url, 'img/')
? asset($user->profile_photo_url)
: Storage::url($user->profile_photo_url);
} else {
$profilePhoto = asset('img/default_pp.png');
}
@endphp

<div class="row justify-content-center">
    <div class="col-12 col-xl-9">
        <div class="card shadow-sm border-0">

            {{-- Encabezado con foto, nombre y rol --}}
            <div class="card-header bg-white px-4 px-lg-5 pt-4 pb-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $profilePhoto }}" alt="Foto de perfil" class="rounded-circle border"
                        style="width: 100px; height: 100px; object-fit: cover; flex-shrink: 0;">
                    <div>
                        <h1 class="h5 fw-bold mb-0">{{ $user->first_name }} {{ $user->last_name }}</h1>
                        <p class="text-muted mb-0">{{ $user->role->real_name }} &middot; {{ $user->email }}</p>
                    </div>
                    <span class="ms-auto text-muted small">Mi perfil</span>
                </div>
                <p class="text-muted mb-0 mt-2" style="font-size: 0.875rem;">
                    Actualiza tu información personal y datos de contacto.
                </p>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card-body px-4 px-lg-5 py-4">

                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger">
                        Revisa los campos del formulario para continuar.
                    </div>
                    @endif

                    {{-- Información básica --}}
                    <h2 class="h6 fw-bold mb-3">Información básica</h2>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="first_name" class="form-label">Nombre*</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}"
                                required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="last_name" class="form-label">Apellido*</label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                                required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">Correo electrónico*</label>
                            <input type="email" class="form-control" id="email" value="{{ $user->email }}" readonly>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Contacto y perfil --}}
                    <h2 class="h6 fw-bold mb-3">Contacto y perfil</h2>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label for="phone_number" class="form-label">Teléfono</label>
                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                id="phone_number" name="phone_number"
                                value="{{ old('phone_number', $user->phone_number) }}" placeholder="Ej: +502 7000-0000">
                            @error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="gender" class="form-label">Género</label>
                            <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">Seleccionar...</option>
                                <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : ''
                                    }}>Masculino</option>
                                <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : ''
                                    }}>Femenino</option>
                                <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : ''
                                    }}>Otro</option>
                                <option value="prefer_not_to_say" {{ old('gender', $user->gender) ===
                                    'prefer_not_to_say' ? 'selected' : '' }}>Prefiero no decirlo</option>
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="birth_date" class="form-label">Fecha de nacimiento</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                                id="birth_date" name="birth_date"
                                value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}">
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Foto de perfil + botón quitar en la misma fila --}}
                        <div class="col-12 col-md-8">
                            <label for="profile_photo" class="form-label">

                                @if($user->profile_photo_url)
                                Cambiar foto de perfil
                                @else
                                Subir foto de perfil
                                <small class="d-block text-body-secondary" style="font-size: 0.75rem;">
                                    Si no subes una foto, se mostrará la imagen por defecto.
                                </small>
                                @endif
                            </label>
                            <input type="file" class="form-control @error('profile_photo') is-invalid @enderror"
                                id="profile_photo" name="profile_photo" accept=".jpg,.jpeg,.png,.webp">
                            @error('profile_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-4 d-flex align-items-end">
                            @if($user->profile_photo_url)
                            <button type="submit" name="remove_profile_photo" value="1"
                                class="btn btn-outline-danger w-100"
                                onclick="return confirm('¿Estás seguro de que deseas quitar tu foto de perfil?');">
                                <i class="bi bi-trash"></i> Quitar foto actual
                            </button>
                            @endif
                        </div>

                        <div class="col-12 col-md-12">
                            <label for="bio" class="form-label">Biografía</label>
                            <textarea id="bio" name="bio" rows="3"
                                class="form-control @error('bio') is-invalid @enderror"
                                placeholder="Escribe algo breve sobre ti">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Cambiar contraseña --}}
                    <h2 class="h6 fw-bold mb-1">Cambiar contraseña</h2>
                    <p class="text-muted mb-3" style="font-size: 0.875rem;">
                        Completa estos campos solo si deseas actualizar tu contraseña.
                    </p>

                    <div class="rounded border p-3 p-lg-4 bg-light-subtle">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="current_password" class="form-label">Contraseña actual</label>
                                <input type="password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" autocomplete="new-password">
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="password" class="form-label">Nueva contraseña</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" autocomplete="new-password">
                                <div class="form-text">Mínimo 8 caracteres.</div>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="password_confirmation" class="form-label">Confirmar nueva</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-white d-flex justify-content-end px-4 px-lg-5 py-3">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
