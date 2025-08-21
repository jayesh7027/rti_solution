<?php

namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use yii\filters\Cors;
use yii\filters\auth\HttpBearerAuth;
use app\models\Task;

class TaskController extends ActiveController
{
    public $modelClass = 'app\\models\\Task';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
        ];
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => [],
        ];
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'view' => ['GET'],
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],
            ],
        ];
        return $behaviors;
    }

    /**
     * Override create action to handle validation errors and wrap response.
     */
    public function actionCreate()
    {
        $model = new Task();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            Yii::$app->response->setStatusCode(201);
            return [
                'success' => true,
                'data' => $model,
            ];
        }
        Yii::$app->response->setStatusCode(422);
        return [
            'success' => false,
            'data' => $model->getErrors(),
        ];
    }

    /**
     * Override update action to handle validation errors and wrap response.
     */
    public function actionUpdate($id)
    {
        $model = Task::findOne($id);
        if (!$model) {
            Yii::$app->response->setStatusCode(404);
            return [
                'success' => false,
                'data' => ['message' => 'Task not found.'],
            ];
        }
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            return [
                'success' => true,
                'data' => $model,
            ];
        }
        Yii::$app->response->setStatusCode(422);
        return [
            'success' => false,
            'data' => $model->getErrors(),
        ];
    }

    public function actionDelete($id)
    {
        $model = Task::findOne($id);
        if (!$model) {
            Yii::$app->response->setStatusCode(404);
            return [
                'success' => false,
                'data' => ['message' => 'Task not found.'],
            ];
        }

        if ($model->delete() !== false) {
            Yii::$app->response->setStatusCode(200);
            return [
                'success' => true,
                'data' => ['message' => 'Task deleted successfully.'],
            ];
        }

        Yii::$app->response->setStatusCode(500);
        return [
            'success' => false,
            'data' => ['message' => 'Failed to delete task.'],
        ];
    }

    /**
     * Restore a soft-deleted task.
     * Endpoint: PATCH /tasks/{id}/restore
     */
    public function actionRestore($id)
    {
        $model = Task::find()->where(['id' => $id])->one();
        if (!$model || $model->deleted_at === null) {
            Yii::$app->response->setStatusCode(404);
            return [
                'success' => false,
                'data' => ['message' => 'Task not found or not deleted.'],
            ];
        }
        $model->deleted_at = null;
        if ($model->save(false, ['deleted_at'])) {
            return [
                'success' => true,
                'data' => $model,
            ];
        }
        Yii::$app->response->setStatusCode(500);
        return [
            'success' => false,
            'data' => ['message' => 'Failed to restore task.'],
        ];
    }

    /**
     * Toggle the status of a task: pending > in_progress > completed > pending
     * Endpoint: PATCH /tasks/{id}/toggle-status
     */
    public function actionToggleStatus($id)
    {
        $model = Task::find()->where(['id' => $id, 'deleted_at' => null])->one();
        if (!$model) {
            Yii::$app->response->setStatusCode(404);
            return [
                'success' => false,
                'data' => ['message' => 'Task not found.'],
            ];
        }
        $statuses = ['pending', 'in_progress', 'completed'];
        $currentIndex = array_search($model->status, $statuses);
        $nextIndex = ($currentIndex === false || $currentIndex === count($statuses) - 1) ? 0 : $currentIndex + 1;
        $model->status = $statuses[$nextIndex];
        if ($model->save(false, ['status'])) {
            return [
                'success' => true,
                'data' => $model,
            ];
        }
        Yii::$app->response->setStatusCode(500);
        return [
            'success' => false,
            'data' => ['message' => 'Failed to toggle status.'],
        ];
    }

    /**
     *  Returns the list of actions for the controller.
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        // Override index to support filtering, sorting, pagination
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        // Remove create and update so custom actionCreate/actionUpdate are used
        unset($actions['create'], $actions['update'], $actions['delete']);

        $actions['view']['checkAccess'] = [$this, 'checkAccess'];
        return $actions;
    }
    /**
     * Override to set status code for create (201), update (200), delete (200).
     */
    public function afterAction($action, $result)
    {
        try {
            if ($action->id === 'create' && Yii::$app->response->statusCode === 200) {
                Yii::$app->response->setStatusCode(201);
            }
            if (in_array($action->id, ['update', 'delete']) && Yii::$app->response->statusCode === 200) {
                Yii::$app->response->setStatusCode(200);
            }

            // Prevent double wrapping if already in {success, data} format

            if (is_array($result) && array_key_exists('success', $result) && array_key_exists('data', $result)) {
                return $result;
                
            }
            if ($result instanceof \yii\data\ActiveDataProvider) {
                $result = $result->getModels(); // extract the actual tasks
            }

            $success = Yii::$app->response->statusCode < 400;
            // For validation errors, wrap errors in {success: false, data: ...}
            if (!$success && is_array($result)) {
                return [
                    'success' => false,
                    'data' => $result,
                ];
            }
            // For all other responses, wrap in {success: true, data: ...}
            return [
                'success' => $success,
                'data' => $result,
            ];
        } catch (\Throwable $e) {
            $status = ($e instanceof \yii\web\HttpException) ? $e->statusCode : 500;
            Yii::$app->response->setStatusCode($status);
            $data = [];
            if ($e instanceof \yii\web\NotFoundHttpException) {
                $data = ['message' => 'Task not found.'];
            } elseif ($e instanceof \yii\web\HttpException) {
                $data = ['message' => $e->getMessage()];
            } else {
                $data = [
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                ];
            }
            return [
                'success' => false,
                'data' => $data,
            ];
        }
    }

    public function beforeAction($action)
    {
        \Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, function () {
            \Yii::$app->errorHandler->errorAction = null;
            \Yii::$app->errorHandler->register();
        });
        \Yii::$app->on(\yii\base\Application::EVENT_BEFORE_ACTION, function () {
            \Yii::$app->errorHandler->errorAction = null;
        });
        set_exception_handler(function ($exception) {
            $status = ($exception instanceof \yii\web\HttpException) ? $exception->statusCode : 500;
            \Yii::$app->response->setStatusCode($status);
            $data = [];
            if ($exception instanceof \yii\web\NotFoundHttpException) {
                $data = ['message' => 'Task not found.'];
            } elseif ($exception instanceof \yii\web\HttpException) {
                $data = ['message' => $exception->getMessage()];
            } else {
                $data = [
                    'message' => $exception->getMessage(),
                    'type' => get_class($exception),
                ];
            }
            echo json_encode([
                'success' => false,
                'data' => $data,
            ]);
            exit;
        });
        return parent::beforeAction($action);
    }
    /**
     * Custom error handling for validation and not found.
     * This method is called before every action to check access permissions.
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if ($model === null && in_array($action, ['view', 'update', 'delete'])) {
            throw new \yii\web\NotFoundHttpException('Resource not found.');
        }
        if ($model && $model->hasErrors()) {
            Yii::$app->response->setStatusCode(422);
            Yii::$app->response->data = $model->getErrors();
            Yii::$app->end();
        }
    }

    /**
     * Prepares the data provider for the index action.
     *
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $request = Yii::$app->request;
        $query = Task::find()->where(['deleted_at' => null]);

        // Filtering
        if ($status = $request->get('status')) {
            $query->andWhere(['status' => $status]);
        }
        if ($priority = $request->get('priority')) {
            $query->andWhere(['priority' => $priority]);
        }
        if ($dueDateFrom = $request->get('due_date_from')) {
            $query->andWhere(['>=', 'due_date', $dueDateFrom]);
        }
        if ($dueDateTo = $request->get('due_date_to')) {
            $query->andWhere(['<=', 'due_date', $dueDateTo]);
        }
        if ($keyword = $request->get('keyword')) {
            $query->andWhere(['like', 'title', $keyword]);
        }

        // Sorting
        $sort = $request->get('sort', 'id');
        
        $sortFields = ['created_at', 'due_date', 'priority'];
        $order = [];
        foreach (explode(',', $sort) as $field) {
            $direction = SORT_ASC;
            if (in_array($field, $sortFields, true)) {
                $order[$field] = $direction;
            }
        }
        if ($order) {
            $query->orderBy($order);
        }

        // Pagination
        $pageSize = (int) $request->get('limit', 10);
        $page = (int) $request->get('page', 1) - 1;

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageSize,
                'page' => $page,
                'pageParam' => 'page',
                'pageSizeParam' => 'limit',
            ],
        ]);
    }
}
