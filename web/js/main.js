// Set Authorization header globally for all Axios requests
axios.defaults.headers.common['Authorization'] = 'Bearer rtisolutiontoken7027';

const api = '/rti_solution/web/tasks';

document.getElementById("add-task-btn").addEventListener("click", function() {
    let div = document.getElementById("form-div");
    if (div.classList.contains("d-none")) {
        div.classList.remove("d-none");
        div.classList.add("d-block");
    } else {
        // Remove show and add hidden
        div.classList.remove("d-block");
        div.classList.add("d-none");
    }
});

function fetchTasks(filters = {}) {
    let url = api + '?';
    if (filters.status) url += `status=${filters.status}&`;
    if (filters.priority) url += `priority=${filters.priority}&`;
    axios.get(url)
        .then(res => {
            // API always returns { success, data }
            renderTasks(res.data.data || []);
        })
        .catch(() => alert('Something went wrong!'));
}

function renderTasks(tasks) {
    const tbody = document.getElementById('taskTableBody');
    tbody.innerHTML = '';
    if (!tasks.length) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="5" class="text-center">Task records not found.</td>`;
        tbody.appendChild(tr);
        return;
    }
    tasks.forEach(task => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-center">${tasks.indexOf(task) + 1}</td>
            <td>${task.title}</td>
            <td>${task.status}</td>
            <td>${task.priority}</td>
            <td>${task.due_date || ''}</td>
            <td class="text-center">
                <button class="btn btn-danger btn-sm" onclick="deleteTask(${task.id})">Delete</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function deleteTask(id) {
    if (!confirm('Are you sure you want to delete this record?')) return;
    axios.delete(`${api}/${id}`)
        .then(() => fetchTasks(getFilters()))
        .catch(() => alert('Task delete failed!'));
}

document.getElementById('taskForm').onsubmit = function(e) {
    e.preventDefault();
    const title = document.getElementById('title').value;
    const due_date = document.getElementById('due_date').value;
    const priority = document.getElementById('priority').value;
    axios.post(api, { title, due_date, priority })
        .then(() => {
            this.reset();
            fetchTasks(getFilters());
        })
        .catch(err => alert('Create task failed: ' + (err.response?.data?.message || 'Error')));
};

function getFilters() {
    return {
        status: document.getElementById('filterStatus').value,
        priority: document.getElementById('filterPriority').value
    };
}

document.getElementById('filterBtn').onclick = function() {
    fetchTasks(getFilters());
};

window.onload = () => fetchTasks();
