<?php

namespace app\models;

use yii\base\Model;

/**
 * ShortLinkForm is the model behind the link shortening form.
 */
class ShortLinkForm extends Model
{
    public $original_url;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // original_url обязателен для заполнения
            [['original_url'], 'required'],
            // original_url должен быть валидным URL-адресом
            ['original_url', 'url', 'defaultScheme' => 'http'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'original_url' => 'Ваша ссылка для сокращения',
        ];
    }
}