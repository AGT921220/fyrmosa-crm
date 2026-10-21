<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Permission;
use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role as RoleModel;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->route('role');

        return $role instanceof RoleModel && $this->user()->can('update', $role);
    }

    protected function prepareForValidation(): void
    {
        $role = $this->route('role');

        if ($role instanceof RoleModel && Role::isBuiltIn($role->name)) {
            $this->merge(['name' => $role->name]);

            return;
        }

        $name = Str::of((string) $this->input('name'))->ascii()->lower()->replace(' ', '_')->replaceMatches('/[^a-z0-9_]/', '')->toString();
        $this->merge(['name' => $name]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $role = $this->route('role');
        $id = $role instanceof RoleModel ? $role->id : null;

        return [
            'name' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]{1,40}$/', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($id)],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(Permission::values())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return (new StoreRoleRequest)->attributes();
    }
}
