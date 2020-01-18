<?php

namespace AppArk\Admin\Actions\Traits;

use AppArk\Database\Eloquent\Deactivates;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

/**
 * 需要确认框的自定义action trait
 */
trait RetriveDeactivatedModel
{
    abstract protected function getModelClass();

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function retrieveModel(Request $request)
    {
        if (!$key = $request->get('_key')) {
            return false;
        }

        /**
         * @var \Illuminate\Database\Eloquent\Model|SoftDeletes|Deactivates $query
         */
        $query = app(str_replace('_', '\\', $request->get('_model')));

        if (in_array(SoftDeletes::class, class_uses_deep($query))) {
            $query = $query->withTrashed();
        }
        if (in_array(Deactivates::class, class_uses_deep($query))) {
            $query = $query->withDeactivated();
        }

        return $query->findOrFail($key);
    }
}
