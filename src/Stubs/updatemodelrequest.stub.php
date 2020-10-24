<?php

namespace App\Http\Requests\Category;

use App\Models\Category;
use App\Http\Requests\BaseRequest;
use Bouncer;

class UpdateCategoryRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Bouncer::can('update-category');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string'
        ];
    }

    public function handle(){

        $data = $this->validated();

        return Category::where('id', $this->id)->update($this->all());
    }    
}
