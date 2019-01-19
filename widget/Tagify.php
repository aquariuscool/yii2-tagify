<?php
namespace saintxak\tagify\widget;

use yii\bootstrap\Html;
use yii\bootstrap\InputWidget;
use saintxak\tagify\assets\TagifyAsset;
use yii\helpers\Json;
use yii\web\View;

/**
 * Class Tagify
 * @package saintxak\tagify\widget
 */
class Tagify extends InputWidget{

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
     * @var bool
     * Tries to autocomplete the input's value while typing (match from whitelist)
     */
    public $autocomplete = true;

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
    public $maxTags = 9999999; //there is no way pass the Infinity  object trought Json format?

    /**
     * @var string function
     * Takes a tag input as argument and returns a transformed value
     */
    public $transformTag = null;

    /**
     * @var string function
     * Takes a tag's value and data as arguments and returns an HTML string for a tag element
     */
    public $tagTemplate = null;

    /**
     * @var bool
     * If true, do not remove tags which did not pass validation
     */
    public $keepInvalidTags = false;

    /**
     * @var int
     * Minimum characters to input to show the suggestions list. "false" to disable
     */
    public $dropdown_enabled = 2;

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
     * @var string
     * Returns a custom string for each list item in the dropdown suggestions selectbox
     */
    public $dropdown_itemTemplate = "";

    /**
     * @var array Preloaded tags array
     *
     * For composed values use pattern: [
     *      ['value'=>{{value}}, 'prop'=>'prop1'],
     *      ...
     * ]
     */
    public $tags = [];


    protected function registerAssets(){
        TagifyAsset::register($this->getView());
    }

    public function run(){
        $this->registerAssets();

        if ($this->hasModel()){
            echo Html::activeTextInput($this->model,$this->attribute,$this->options);
        }else{
            echo Html::textInput($this->name,$this->value,$this->options);
        }

        $this->registerTagifyInit();
    }

    protected function registerTagifyInit(){
        $options = [
            'delimiters'=>$this->delimiters,
            'pattern'=>$this->pattern,
            'mode'=>$this->mode,
            'duplicates'=>$this->duplicates,
            'enforceWhitelist'=>$this->enforceWhitelist,
            'autocomplete'=>$this->autocomplete,
            'whitelist'=>$this->whitelist,
            'blacklist'=>$this->blacklist,
            'addTagOnBlur'=>$this->addTagOnBlur,
            'callbacks'=>$this->callbacks,
            'maxTags'=>$this->maxTags,
            'transformTag'=>$this->transformTag,
            'tagTemplate'=>$this->tagTemplate,
            'keepInvalidTags'=>$this->keepInvalidTags,
            'dropdown.enabled'=>$this->dropdown_enabled,
            'dropdown.maxItems'=>$this->dropdown_maxItems,
            'dropdown.classname'=>$this->dropdown_classname,
            'dropdown.itemTemplate'=>$this->dropdown_itemTemplate,
        ];

        $js = "$('#{$this->options['id']}').tagify(".Json::encode($options).");";

        if (!empty($this->tags)){
            $tags_js = '';
            if ($this->mode === 'mix'){
                $tags_js = $this->createMixTags();
            }else{
                $tags_js = Json::encode($this->tags);
            }

            $js .= " $('#{$this->options['id']}').data('tagify').addTags({$tags_js});";
        }

        $this->getView()->registerJs($js, View::POS_END);
    }

    protected function createMixTags(){
        $tags_js = '[';

        foreach ($this->tags as $tag){
            $tags_js .= Json::encode($tag).',';
        }

        $tags_js = substr($tags_js,0,-1);
        $tags_js .= ']';

        return $tags_js;
    }

}