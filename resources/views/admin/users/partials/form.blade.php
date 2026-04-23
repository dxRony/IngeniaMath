<div class="card-body px-4 px-lg-5 pt-2 pb-4 pb-lg-5">
    <div class="row g-4">
        @if(!isset($user))
        <div class="col-12">
            <label for="role_id" class="form-label">Rol*</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                    <option value="">Seleccionar rol...</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id')==$role->id ? 'selected' : '' }}>
                        {{ $role->real_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @error('role_id')
            <div class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
        @endif

        <div class="col-12 col-lg-6">
            <label for="first_name" class="form-label">Nombre*</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name"
                    name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" required
                    placeholder="Tu nombre">
            </div>
            @error('first_name')
            <div class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>

        <div class="col-12 col-lg-6">
            <label for="last_name" class="form-label">Apellido*</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name"
                    name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" required
                    placeholder="Tu apellido">
            </div>
            @error('last_name')
            <div class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>

        <div class="col-12">
            <label for="email" class="form-label">Correo Electrónico*</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                    value="{{ old('email', $user->email ?? '') }}" required placeholder="usuario@ejemplo.com">
            </div>
            @error('email')
            <div class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>

        <div class="col-12 col-lg-6">
            <label for="password" class="form-label">Contraseña{{ isset($user) ? ' Nueva' : '*' }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                    name="password" autocomplete="new-password" {{ isset($user) ? '' : 'required' }}
                    placeholder="Mínimo 8 caracteres">
            </div>
            @if(isset($user))
            <small class="text-muted d-block mt-1" style="font-size: 15px;">Dejar en blanco para mantener la contraseña
                actual</small>
            @endif
            @error('password')
            <div class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>

        <div class="col-12 col-lg-6">
            <label for="password_confirmation" class="form-label">Confirmar Contraseña{{ isset($user) ? ' Nueva' : '*'
                }}</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                    id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                    placeholder="Repite tu contraseña">
            </div>
            @error('password_confirmation')
            <div class="invalid-feedback d-block" role="alert">
                <strong>{{ $message }}</strong>
            </div>
            @enderror
        </div>
    </div>
</div>


<div class="card-footer bg-white border-top p-4 p-lg-5 d-flex flex-column flex-sm-row justify-content-end gap-2">
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary order-2 order-sm-1">Cancelar</a>
    <button type="submit" class="btn btn-primary order-1 order-sm-2">{{ $submitButton }}</button>
</div>
