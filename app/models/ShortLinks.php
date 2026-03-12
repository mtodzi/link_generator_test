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
 *
 * @property ShortLinkVisits[] $shortLinkVisits
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
            [['original_url', 'short_code'], 'required'],
            [['created_at'], 'safe'],
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
        ];
    }

    /**
     * Gets query for [[ShortLinkVisits]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShortLinkVisits()
    {
        return $this->hasMany(ShortLinkVisits::class, ['short_link_id' => 'id']);
    }

    /**
     * Gets total visits count.
     *
     * @return int
     */
    public function getVisits()
    {
        return (int) $this->getShortLinkVisits()->sum('visits');
    }
}
