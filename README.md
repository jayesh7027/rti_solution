## Project Setup Instructions

### 1. Requirements
- PHP 7.4+
- Composer
- MySQL
- Node.js (optional, for frontend tooling)

### 2. Install Yii2 
```
You can then install this project template using the following command:

~~~
composer create-project --prefer-dist yiisoft/yii2-app-basic rti_solution
~~~
```

### 3. Database Configuration
- Edit `config/db.php` with your MySQL credentials and database name.
- Example:
  ```php
  'dsn' => 'mysql:host=localhost;dbname=rti_solution_llc',
  'username' => 'root',
  'password' => 'admin@123',
  ```

### 4. Run Migrations
```
php yii migrate
```
This will create the `task` table with all required columns, including `deleted_at` for soft delete.

### 5. Start the Yii2 Server (for local development)
```
php yii serve --port=8080
```
Or use your Apache/Nginx setup pointing to the `web/` directory.

Direct browser access URL: http://localhost/rti_solution/web/

## API Endpoints
All endpoints require the header:

```
Authorization: Bearer rtisolutiontoken7027
```


### Task CRUD
- **List Tasks:**
  - `GET /tasks`
  - Supports filtering, sorting, and pagination:
    - `?status=Pending&priority=High&sort=-due_date&page=1&limit=10`
- **View Task:**
  - `GET /tasks/{id}`
- **Create Task:**
  - `POST /tasks`
  - Body:
    ```json
    {
      "title": "Task title",
      "description": "...",
      "priority": "medium",
      "due_date": "2025-08-31",
      "tag_ids": [1, 2]
    }
    ```
- **Update Task:**
  - `PUT /tasks/{id}`
  - Body: `{ "title": "New title", ... }`
- **Delete Task (Soft Delete):**
  - `DELETE /tasks/{id}`
- **Restore Task:**
  - `PATCH /tasks/{id}/restore`
- **Toggle Status:**
  - `PATCH /tasks/{id}/toggle-status`
  - Cycles status: pending → in_progress → completed → pending

### Filtering, Sorting, Pagination Examples
- `GET /tasks?status=Pending&priority=High`
- `GET /tasks?sort=-due_date`
- `GET /tasks?page=2&limit=5`
- `GET /tasks?status=Completed&sort=-priority&limit=10&page=1`
- `GET /tasks?keyword=meeting`

---

## Frontend Usage

### 1. Open the Frontend
- Open `web/index.html` in your browser (if using PHP built-in server, go to `http://localhost/rti_solution/web or http://localhost/rti_solution/web/index.html`).
- All API calls are authenticated automatically with the correct Bearer token.

### 2. Features
- Add, edit, delete (soft), restore, and toggle status of tasks.
- Filter, sort, and paginate tasks.
- Responsive UI with Bootstrap.

---

## Assumptions & Known Issues
- Only one hardcoded API token (`rtisolutiontoken7027`) is used for authentication.
- No user management or registration (single-user API demo).
- Soft-deleted tasks are excluded from all queries unless restored.
- Status and priority are always returned with the first character capitalized for display.
- The frontend uses vanilla JS and Axios, no jQuery required.
- If you change the API token, update it in `web/js/main.js` as well.

---

## Task Audit Logs

All create, update, and delete actions on tasks are logged in the `task_log` table. Each log entry records:
- `task_id`: The affected task
- `action`: One of `create`, `update`, or `delete`
- `data`: JSON snapshot of the task at the time of the action
- `created_at`: Timestamp of the action

You can view the `task_log` table in your database to audit all changes.

---


## Tag System

### Overview
- Tasks can have multiple tags (many-to-many relationship).
- Tags help categorize, filter, and organize tasks.
- 20 dummy tags are auto-created during migration for testing/demo.

### Tag Table
- Table: `tag` (id, name)
- Table: `task_tag` (task_id, tag_id) — junction table

### Assigning Tags to Tasks
- When creating or updating a task, include a `tag_ids` array in the request body:
  ```json
  {
    "title": "Finish report",
    "tag_ids": [1, 2, 3]
  }
  ```
- Tags are linked/unlinked automatically on create, update, and delete.

### Filtering Tasks by Tag
- Use the `tag` query parameter to filter tasks by tag name or ID:
  - `GET /tasks?tag=Urgent` (by name)
  - `GET /tasks?tag=1` (by ID)git s

### Example Dummy Tags
`Urgent`, `Important`, `Low Priority`, `Bug`, `Feature`, `Enhancement`, `Research`, `Testing`, `Documentation`, `Meeting`, `Review`, `Blocked`, `In Progress`, `Completed`, `Backlog`, `Design`, `Frontend`, `Backend`, `DevOps`, `QA`

---


## Postman collection is attached in root directory 
- RTI Solution LLC Task API.postman_collection.json

## Contact
For questions or issues, contact me.

jayeshprajapati.7027@gmail.com
+91 8511719709
