<?php

namespace saintxak\tagify\widget;

use yii\bootstrap\Html;
use yii\bootstrap\InputWidget;
use saintxak\tagify\assets\TagifyAsset;
use yii\helpers\Json;
use yii\web\View;
use yii\web\JsExpression;

/**
 * Class Tagify
 * @package saintxak\tagify\widget
 */
class Tagify extends InputWidget
{

    /**
     * @var string
     * Placeholder text. If this attribute is set on an input/textarea element it will override this setting
     */
    public $placeholder = '';

    /**
     * @var string
     * [regex] split tags by any of these delimiters. Example: ",
     */
    public $delimiters = ",";

    /**
     * @var string
     * Validate input by REGEX pattern (can also be applied on the input itself as an attribute) Ex: /[1-9]/
     */
    public $pattern = "";

    /**
     * @var string
     * use 'mix' as value to allow mixed-content. The 'pattern' setting must be set to some character.
     */
    public $mode = "";

    /**
     * @var string
     * Interpolation for mix mode. Everything between these will become a tag
     */
    public $mixTagsInterpolator = ['[[', ']]'];

    /**
     * @var string
     * Define conditions in which typed mix-tags content is allowing a tag to be created after.
     */
    public $mixTagsAllowedAfter = '/,|\.|\:|\s/';

    /**
     * @var bool
     * (flag) Should duplicate tags be allowed or not
     */
    public $duplicates = false;

    /**
     * @var bool
     * Should ONLY use tags allowed in whitelist
     */
    public $enforceWhitelist = false;

    /**
     * @deprecated
     * @var bool
     * Tries to autocomplete the input's value while typing (match from whitelist).
     * Keep this for backward compatibility, use $autoComplete_enabled instead
     */
    public $autocomplete = true;

    /**
     * @var bool
     * autocomplete
     * enabled: Tries to suggest the input's value while typing (match from whitelist) by adding the rest of term as
     *     grayed-out text
     */
    public $autoComplete_enabled = true;

    /**
     * @var bool
     * autocomplete
     * rightKey: If true, when → is pressed, use the suggested value to create a tag, else just auto-completes the
     *     input. In mixed-mode this is ignored and treated as "true"
     */
    public $autoComplete_rightKey = false;

    /**
     * @var array
     * An array of tags which only they are allowed
     */
    public $whitelist = [];

    /**
     * @var array
     * An array of tags which aren't allowed
     */
    public $blacklist = [];

    /**
     * @var bool
     * Automatically adds the text which was inputed as a tag when blur event happens
     */
    public $addTagOnBlur = true;

    /**
     * @var array
     * Exposed callbacks object to be triggered on events: 'add' / 'remove' tags
     */
    public $callbacks = [];

    /**
     * @var int
     * Maximum number of allowed tags. when reached, adds a class "hasMaxTags" to <Tags>
     */
    public $maxTags = 9999999; //there is no way pass the Infinity  object through Json format?

    /**
     * @var int
     * Number of clicks on a tag to enter "edit" mode. Only 1 or 2 work. false or null will disallow editing
     */
    public $editTags = 2;

    /**
     * @var array
     * Object consisting of functions which return template strings
     */
    public $templates = null;

    /**
     * @var string function
     * Takes a tag input as argument and returns a transformed value
     */
    public $transformTag = null;

    /**
     * @var bool
     * If true, do not remove tags which did not pass validation
     */
    public $keepInvalidTags = false;

    /**
     * @var bool
     * If true, do not add invalid, temporary, tags before automatically removing them
     */
    public $skipInvalid = false;

    /**
     * @var bool / string
     * On pressing backspace key: true - remove last tag, edit - edit last tag
     */
    public $backspace = true;

    /**
     * @var string
     * If you wish your original input/textarea value property format to other than the default (which I recommend
     *     keeping) you may use this and make sure it returns a string.
     */
    public $originalInputValueFormat = null;

    /**
     * @var int
     * Minimum characters to input to show the suggestions list. "false" to disable
     */
    public $dropdown_enabled = 2;

    /**
     * @var bool
     * if true, match exact item when a suggestion is selected (from the dropdown) and also more strict matching for
     *     duplicate items. Ensure fuzzySearch is false for this to work.
     */
    public $dropdown_caseSensitive = false;

    /**
     * @var int
     * Maximum items to show in the suggestions list dropdown
     */
    public $dropdown_maxItems = 10;

    /**
     * @var string
     * Custom class name for the dropdown suggestions selectbox
     */
    public $dropdown_classname = "";

    /**
     * @var bool
     * Enables filtering dropdown items values' by string containing and not only beginning
     */
    public $dropdown_fuzzySearch = true;

    /**
     * @var bool
     * Enables filtering dropdown items values' by string containing and not only beginning
     */
    public $dropdown_accentedSearch = true;

    /**
     * @var string
     * manual - will not render the dropdown, and you would need to do it yourself. See demo
     * text - will place the dropdown next to the caret
     * input - will place the dropdown next to the input
     * all - normal, full-width design
     */
    public $dropdown_position = '';

    /**
     * @var bool
     * When a suggestions list is shown, highlight the first item, and also suggest it in the input (The suggestion can
     *     be accepted with → key)
     */
    public $dropdown_highlightFirst = false;

    /**
     * @var bool
     * close the dropdown after selecting an item, if enabled:0 is set (which means always show dropdown on focus)
     */
    public $dropdown_closeOnSelect = true;

    /**
     * @var string
     * if whitelist is an Array of Objects:
     * Ex. [{value:'foo', email:'foo@a.com'},...])
     * this setting controlls which data key will be printed in the dropdown.
     * Ex. mapValueTo: data => "To:" + data.email
     * Ex. mapValueTo: "email"
     */
    public $dropdown_mapValueTo = '';

    /**
     * @var array
     * When a user types something and trying to match the whitelist items for suggestions, this setting allows
     *     matching other keys of a whitelist objects
     */
    public $dropdown_searchKeys = ['value', 'searchBy'];

    /**
     * @var array Preloaded tags array
     *
     * For composed values use pattern: [
     *      ['value'=>{{value}}, 'prop'=>'prop1'],
     *      ...
     * ]
     */
    public $tags = [];


    protected function registerAssets()
    {
        TagifyAsset::register($this->getView());
    }

    public function run()
    {
        $this->registerAssets();

        if ($this->hasModel()) {
            echo Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textInput($this->name, $this->value, $this->options);
        }

        $this->registerTagifyInit();
    }

    protected function registerTagifyInit()
    {
        $options = [
            'placeholder' => $this->placeholder,
            'delimiters' => $this->delimiters,
            'mode' => $this->mode,
            'mixTagsInterpolator' => $this->mixTagsInterpolator,
            'mixTagsAllowedAfter' => $this->mixTagsAllowedAfter,
            'duplicates' => $this->duplicates,
            'enforceWhitelist' => $this->enforceWhitelist,
            'autoComplete' => [
                'enabled' => $this->autocomplete && $this->autoComplete_enabled,
                'rightKey' => $this->autoComplete_rightKey,
            ],
            'whitelist' => $this->whitelist,
            'blacklist' => $this->blacklist,
            'addTagOnBlur' => $this->addTagOnBlur,
            //'callbacks' => $this->callbacks,
            'maxTags' => $this->maxTags,
            'editTags' => $this->editTags,
            'keepInvalidTags' => $this->keepInvalidTags,
            'skipInvalid' => $this->skipInvalid,
            'backspace' => $this->backspace,
            'originalInputValueFormat' => $this->originalInputValueFormat,
            'dropdown' => [
                'enabled' => $this->dropdown_enabled,
                'caseSensitive' => $this->dropdown_caseSensitive,
                'maxItems' => $this->dropdown_maxItems,
                'classname' => $this->dropdown_classname,
                'fuzzySearch' => $this->dropdown_fuzzySearch,
                'accentedSearch' => $this->dropdown_accentedSearch,
                'position' => $this->dropdown_position,
                'highlightFirst' => $this->dropdown_highlightFirst,
                'closeOnSelect' => $this->dropdown_closeOnSelect,
                'mapValueTo' => $this->dropdown_mapValueTo,
                'searchKeys' => $this->dropdown_searchKeys,
            ],
        ];

        $optionsName = 'options_' . rand(0, 10000);
        $tagifyName = 'tagify_' . rand(0, 10000);
        $js = [];
        $js[] = "var $optionsName = " . Json::encode($options) . ';';

        if ($this->pattern) {
            $js[] = $optionsName . "['pattern'] = " . new JsExpression($this->pattern) . ';';
        }

        if ($this->templates && is_array($this->templates)) {
            $templates = [];
            foreach ($this->templates as $key => $templateFunction) {
                if (!in_array($key, ['wrapper', 'tag', 'dropdownItem'])) {
                    continue;
                }
                $templates[$key] = $templateFunction;
            }
            if ($templates) {
                $js[] = $optionsName . "['templates'] = {};";
                foreach ($templates as $key => $template) {
                    $js[] = $optionsName . "['templates']['$key'] = " . new JsExpression($template) . ";";
                }
            }
        }

        $js[] = ";var $tagifyName = jQuery('#{$this->options['id']}').tagify($optionsName);";

        if ($this->callbacks && is_array($this->callbacks)) {
            foreach ($this->callbacks as $key => $callback) {
                $js[] = "$tagifyName.on('$key', $callback)";
            }
        }

        if (!empty($this->tags)) {
            $tags_js = '';
            if ($this->mode === 'mix') {
                $tags_js = $this->createMixTags();
            } else {
                $tags_js = Json::encode($this->tags);
            }

            $js[] = "$tagifyName.addTags({$tags_js});";
        }

        $this->getView()->registerJs(implode("\n", $js), View::POS_END);
    }

    protected function createMixTags()
    {
        $tags_js = '[';

        foreach ($this->tags as $tag) {
            $tags_js .= Json::encode($tag) . ',';
        }

        $tags_js = substr($tags_js, 0, -1);
        $tags_js .= ']';

        return $tags_js;
    }

}