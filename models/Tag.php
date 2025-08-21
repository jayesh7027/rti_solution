<?php
/*
 * Author: Jayesh Prajapati
 * Create At: 2023-08-21
 * Updated At: 2023-08-22
 * Description: Model for managing tags
 */

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tag".
 *
 * @property int $id
 * @property string $name
 */
class Tag extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tag';
    }
    /**
     * Gets tasks related to this tag (many-to-many).
     */
    public function getTasks()
    {
        return $this->hasMany(Task::class, ['id' => 'task_id'])
            ->viaTable('task_tag', ['tag_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }
}
