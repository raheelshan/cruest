<?php

namespace App\Http\Requests\Category;

use App\Models\Category;
use App\Http\Requests\BaseRequest;
use Bouncer;

class GetCategoryRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Bouncer::can('view-category');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
          //'store_id' => ['required', 'integer'],
        ];
    }

    public function handle(){
        return Category::findOrNew($this->id);
    }
}
