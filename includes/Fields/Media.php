<?php

namespace WebinaneCommerce\Fields;

use WebinaneCommerce\Fields\Field;

class Media extends Field
{
    /**
     * Add Text.
     *
     * @var string
     */
    public $add_text = '';
    
    /**
     * Update Text
     * 
     * @var string
     */
    public $update_text = '';
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'media';

    /**
     * The field's suggestions callback.
     *
     * @var array|callable
     */
    public $suggestions;

    /**
     * Set Add Text
     * 
     * @param string $text
     */
    public function setAddText($text) {
        $this->add_text = $text;

        return $this;
    }

    /**
     * Get Add Text
     * 
     * @return string
     */
    public function getAddText() {
        return ($this->add_text) ?? esc_html__('Add New', 'webinane-commerce');
    }

    /**
     * Set Update Text
     *
     * @param string $text
     */
    public function setUpdateText($text) {
        $this->update_text = $text;

        return $this;
    }

    /**
     * Get Update Text.
     *
     * @return string
     */
    public function getUpdateText() {
        return ($this->update_text) ?? esc_html__('Update', 'webinane-commerce');
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
     * Prepare the element for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'suggestions' => $this->resolveSuggestions(''),
            'props' => [
                'add_text'   => $this->getAddText(),
                'update_text'   => $this->getUpdateText(),
            ]
        ]);
    }
}
