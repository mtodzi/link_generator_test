<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "short_links".
 *
 * @property int $id
 * @property string $original_url
 * @property string $short_code
 * @property string $created_at
 * @property int $visits
 */
class ShortLinks extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'short_links';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['visits'], 'default', 'value' => 0],
            [['original_url', 'short_code'], 'required'],
            [['created_at'], 'safe'],
            [['visits'], 'integer'],
            [['original_url'], 'string', 'max' => 2048],
            [['short_code'], 'string', 'max' => 20],
            [['short_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'original_url' => 'Original Url',
            'short_code' => 'Short Code',
            'created_at' => 'Created At',
            'visits' => 'Visits',
        ];
    }

}
