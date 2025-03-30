<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * The permission required for this request
     * 
     * @var string|null
     */
    protected $permission = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // First check if user is authenticated
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // If no specific permission is required, just allow authenticated users
        if (!$this->permission) {
            return true;
        }

        // Check if user has the required permission
        // To avoid relying on specific method names, check permissions through roles
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                if ($permission->slug === $this->permission) {
                    return true;
                }
            }
        }
        
        return false;
    }
}