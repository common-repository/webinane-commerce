<?php

namespace WebinaneCommerce\Fields;

use WebinaneCommerce\Fields\Field;

class Country extends Field
{

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'country';

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
     * Get the list of countries.
     *
     * @return array
     */
    public function getCountries() {
        $countires = include WNCM_PATH . 'assets/data/_countries.php';

        return $countires;
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
     * Display the field as raw HTML using Vue.
     *
     * @return $this
     */
    public function hideStates()
    {
        $this->withMeta(['hide_states' => true]);
        return $this;
    }

    /**
     * Display the field as raw HTML using Vue.
     *
     * @return $this
     */
    public function multiple()
    {
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
        $data  = array_merge(parent::jsonSerialize(), [
            'suggestions' => $this->resolveSuggestions(''),
            'value' => $this->value ?? ['country' => '', 'state' => ''],
        ]);
        $data['props']['countries_list'] = $this->getCountries();

        return $data;
    }
}
