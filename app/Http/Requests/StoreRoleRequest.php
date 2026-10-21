<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role as RoleModel;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', RoleModel::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->slug((string) $this->input('name')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]{1,40}$/', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in(Permission::values())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'permissions' => 'permisos',
        ];
    }

    private function slug(string $name): string
    {
        return Str::of($name)->ascii()->lower()->replace(' ', '_')->replaceMatches('/[^a-z0-9_]/', '')->toString();
    }
}
