<?php

namespace WebinaneCommerce\Fields;

use WebinaneCommerce\Fields\Field;

class MultiText extends Field
{

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'multi-text';

    /**
     * The field's suggestions callback.
     *
     * @var array|callable
     */
    public $suggestions;

    
    /**
     * Resolve the display suggestions for the field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function resolveSuggestions($request)
    {
        if (is_callable($this->suggestions)) {
            return call_user_func($this->suggestions, $request) ?? null;
        }

        return $this->suggestions;
    }

    /**
     * Display the field as raw HTML using Vue.
     *
     * @return $this
     */
    public function asHtml()
    {
        return $this->withMeta(['asHtml' => true]);
    }

    /**
     * Prepare the element for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'suggestions' => $this->resolveSuggestions(''),
            'value' => $this->value ?? ['']
        ]);
    }
}
