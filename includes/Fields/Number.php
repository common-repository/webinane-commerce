<?php

namespace WebinaneCommerce\Fields;

use WebinaneCommerce\Fields\Field;

class Number extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'el-input-number';

    /**
     * Min
     *
     * @var integer
     */
    private $min = 0;

    /**
     * Max
     *
     * @var integer
     */
    private $max = 1000;

    /**
     * The field's suggestions callback.
     *
     * @var array|callable
     */
    public $suggestions;

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
     * Set Min
     *
     * @param int $min
     */
    public function setMin($min) {
        $this->min = $min;

        return $this;
    }

    /**
     * Set Max.
     *
     * @param int $max
     */
    public function setMax($max) {
        $this->max = $max;

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
            'props' => array_merge($this->meta(), ['min' => $this->min, 'max' => $this->max])
        ]);
    }
}
