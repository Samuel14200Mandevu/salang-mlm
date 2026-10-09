@php
    $selectedRole = $selectedRole ?? old('role', 'user');
@endphp

<label for="userRoleSelect">Rôle de l'utilisateur <span class="required">*</span></label>
<select name="role"
        id="userRoleSelect"
        class="form-control @error('role') form-control-error @enderror"
        required>
    <option value="user" @selected($selectedRole === 'user')>Membre</option>
    <option value="cashier" @selected($selectedRole === 'cashier')>Caissier</option>
    <option value="admin" @selected($selectedRole === 'admin')>Administrateur</option>
    <option value="it_manager" @selected($selectedRole === 'it_manager')>Responsable IT</option>
</select>
@error('role')
    <p class="text-xs text-[var(--ui-stat-danger)] mt-1">{{ $message }}</p>
@enderror
