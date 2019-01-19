**Yii2 Tagify extension**

Provided by https://github.com/yairEO/tagify

**Documentation**
Example usage
```php
<?php
$form = ActiveForm::begin();

echo $form->field($model,'tags[]')->widget(\saintxak\tagify\widget\Tagify::class,[
    'tags'=>['fsdfsd','gdfgdfgdf','gdfgdfgdf'], //selected tags
    'whitelist'=>['test1','test2','test3','test4'] //aviable tags whitelist
]);

```