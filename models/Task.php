<?php
/*
 * Author: Jayesh Prajapati
 * Create At: 2023-08-21
 * Updated At: 2023-08-22
 * Description: Model for managing tasks
 */

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "task".
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property string $priority
 * @property string|null $due_date
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $deleted_at
 */
class Task extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task';
    }
    /**
     * Gets tags related to this task (many-to-many).
     */
    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])
            ->viaTable('task_tag', ['task_id' => 'id']);
    }

    /**
     * Assign tags to the task.
     * @param array $tagIds
     */
    public function setTagIds($tagIds)
    {
        $this->unlinkAll('tags', true);
        if (is_array($tagIds)) {
            foreach ($tagIds as $tagId) {
                $tag = Tag::findOne($tagId);
                if ($tag) {
                    $this->link('tags', $tag);
                }
            }
        }
    }

    /**
     * Returns array of tag IDs for this task.
     */
    public function getTagIds()
    {
        return $this->getTags()->select('id')->column();
    }

    /**
     * Filter tasks by tag name or ID (static helper).
     * @param \yii\db\ActiveQuery $query
     * @param string|int $tag Tag name or ID
     */
    public static function filterByTag($query, $tag)
    {
        if (is_numeric($tag)) {
            $query->joinWith('tags')->andWhere(['tag.id' => $tag]);
        } else {
            $query->joinWith('tags')->andWhere(['tag.name' => $tag]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'min' => 5, 'max' => 255],
            [['description'], 'string'],
            [['status'], 'in', 'range' => ['pending', 'in_progress', 'completed']],
            [['priority'], 'in', 'range' => ['low', 'medium', 'high']],
            [['due_date'], 'date', 'format' => 'php:Y-m-d'],
            [['created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['status'], 'default', 'value' => 'pending'],
            [['priority'], 'default', 'value' => 'medium'],
        ];
    }
    /**
     * Override delete to implement soft delete.
     */
    public function delete()
    {
        $this->deleted_at = time();
        return $this->save(false, ['deleted_at']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'status' => 'Status',
            'priority' => 'Priority',
            'due_date' => 'Due Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getStatus()
    {
        $status = $this->getAttribute('status');
        return $status ? ucfirst($status) : $status;
    }

    public function getPriority()
    {
        $priority = $this->getAttribute('priority');
        return $priority ? ucfirst($priority) : $priority;
    }
}
