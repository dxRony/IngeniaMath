@php
$viewerRoleId = (int) Auth::user()->role_id;
$layout = match ($viewerRoleId) {
	1 => 'admin.main',
	2 => 'moderator.main',
	3 => 'tutor.main',
	4 => 'student.main',
	default => 'layouts.app',
};

if ($user->profile_photo_url) {
	$profilePhoto = str_starts_with($user->profile_photo_url, 'img/')
		? asset($user->profile_photo_url)
		: Storage::url($user->profile_photo_url);
} else {
	$profilePhoto = asset('img/default_pp.png');
}
@endphp

@extends($layout)

@section('content')
<div class="row justify-content-center">
	<div class="col-12 col-xl-9">
		<div class="mb-3">
			<a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
				<i class="bi bi-arrow-left me-1"></i> Volver
			</a>
		</div>

		<div class="card shadow-sm border-0">
			<div class="card-header bg-white px-4 px-lg-5 pt-4 pb-3">
				<div class="d-flex align-items-center gap-3">
					<img src="{{ $profilePhoto }}" alt="Foto de perfil" class="rounded-circle border"
						style="width: 100px; height: 100px; object-fit: cover; flex-shrink: 0;">
					<div>
						<h1 class="h5 fw-bold mb-0">{{ $user->first_name }} {{ $user->last_name }}</h1>
						<p class="text-muted mb-0">{{ $user->role?->real_name ?? 'Usuario' }}</p>
					</div>
					<span class="ms-auto text-muted small">Perfil público</span>
				</div>
			</div>

			<div class="card-body px-4 px-lg-5 py-4">
				<h2 class="h6 fw-bold mb-3">Información del usuario</h2>

				<div class="row g-3 mb-3">
					<div class="col-12 col-md-6">
						<label class="form-label text-muted mb-1">Nombre</label>
						<p class="mb-0">{{ $user->first_name }}</p>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label text-muted mb-1">Apellido</label>
						<p class="mb-0">{{ $user->last_name }}</p>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label text-muted mb-1">Correo electrónico</label>
						<p class="mb-0">{{ $user->email }}</p>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label text-muted mb-1">Teléfono</label>
						<p class="mb-0">{{ $user->phone_number ?: 'No especificado' }}</p>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label text-muted mb-1">Género</label>
						<p class="mb-0">{{ $user->gender ?: 'No especificado' }}</p>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label text-muted mb-1">Fecha de nacimiento</label>
						<p class="mb-0">{{ $user->birth_date ? $user->birth_date->format('d/m/Y') : 'No especificada' }}</p>
					</div>

					<div class="col-12">
						<label class="form-label text-muted mb-1">Biografía</label>
						<p class="mb-0">{{ $user->bio ?: 'Este usuario no ha agregado biografía.' }}</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
