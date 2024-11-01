<?php

namespace WebinaneCommerce\Fields;

use WebinaneCommerce\Fields\Field;

class Select extends Field
{
    /**
     * Set the options.
     * 
     * @var array
     */
    public $options = [];
    
    /**
     * Whether multiple or single.
     *
     * @var boolean
     */
    public $multiple = false;
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'el-select';

    /**
     * The field's suggestions callback.
     *
     * @var array|callable
     */
    public $suggestions;

    public function setOptions($options) {

        if(is_callable($options)) {
            $this->options = call_user_func($options);
        } else {
            $this->options = $options;
        }

        return $this;
    }

    public function getOptions() {
        return $this->options;
    }

    /**
     * Set the callback or array to be used to determine the field's suggestions list.
     *
     * @param  array|callable  $suggestions
     * @return $this
     */
    public function suggestions($suggestions)
    {
        $this->suggestions = $suggestions;

        return $this;
    }

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
     * Multiple
     *
     * @return [type] [description]
     */
    public function multiple() {
        $this->withMeta(['multiple' => true]);

        return $this;
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
            'options'   => $this->getOptions(),
            // 'props' => ['multiple' => $this->multiple]
        ]);
    }
}
