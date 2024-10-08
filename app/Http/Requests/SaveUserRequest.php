<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SaveUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function response(array $errors)
    {
        return $this->redirector->back()->withInput()->withErrors($errors, $this->errorBag);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'department_id' => 'nullable|exists:departments,id',
            'manager_id' => 'nullable|exists:users,id',
            'company_id' => [
                // determines if the user is being moved between companies and checks to see if they have any items assigned
                function ($attribute, $value, $fail) {
                    if (($this->has('company_id')) && ($this->user?->allAssignedCount() > 0) && (Setting::getSettings()->full_multiple_companies_support)) {
                        $fail(trans('admin/users/message.error.multi_company_items_assigned'));
                    }
                }
            ]
        ];

        switch ($this->method()) {

            // Brand new user
            case 'POST':
                $rules['first_name'] = 'required|string|min:1';
                $rules['username'] = 'required_unless:ldap_import,1|string|min:1';
                if ($this->request->get('ldap_import') == false) {
                    $rules['password'] = Setting::passwordComplexityRulesSaving('store').'|confirmed';
                }
                break;

            // Save all fields
            case 'PUT':
                $rules['first_name'] = 'required|string|min:1';
                $rules['username'] = 'required_unless:ldap_import,1|string|min:1';
                $rules['password'] = Setting::passwordComplexityRulesSaving('update').'|confirmed';
                break;

            // Save only what's passed
            case 'PATCH':
                $rules['password'] = Setting::passwordComplexityRulesSaving('update');
                break;

            default:
                break;
        }

        return $rules;
    }
}
